<?php

namespace App\Http\Controllers;

use App\Http\Requests\Request;
use App\Models\AskUser;
use App\Models\DeptUser;
use App\Models\GroupUser;
use App\Models\Message;
use App\Models\Org;
use App\Models\Task;
use App\Models\TaskUser;
use App\Models\User;
use App\Models\Dept;
use App\Models\OrgUser;
use App\Http\Requests\OrgRequest;
use Illuminate\Support\Facades\Hash;
use Toplan\Sms\Facades\SmsManager;
use Validator;
use Illuminate\Support\Facades\DB;
use App\Models\Group;
use App\Models\MergeDeptOrg;
use App\Models\AskType;

class OrgController extends Controller
{
    // 获取所有机构信息
    public function index(OrgRequest $request)
    {
        $data = Org::all()->where('status', 1);

        return ResponseJson($data);
    }

    // 创建机构 申请
    public function store(OrgRequest $request)
    {
        $user_id = session()->get('user.id');
        $user = User::findOrFail($user_id);

        $same_name = Org::where('name', $request->name)->where('region', $request->region)->get();
        if (!$same_name->isEmpty()) {
            return ResponseJson([], "同地区已有该机构名称");
        }

        $org = Org::create([
            'name' => $request->name,
            'password' => '666',
            'region' => $request->region,
            'status' => 2
        ]);


        $org->code = date("Ymd");
        $pattern = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        for ($i = 0; $i < 6; $i++) {
            $org->code .= $pattern[mt_rand(0, 25)];
        }

        $org->save();

        // 如果用户还没有机构,is_default为1，否则为0
        $is_default = $user->orgs->count() == 0 ? 1 : 0;

        $user->orgs()
            ->syncWithoutDetaching([
                $org->id => [
                    'role_id' => ROLE_SYS_SUPER,
                    'is_default' => $is_default
                ]
            ]);

        $result = $user->orgs()->find($org->id);

        return ResponseJson($result);
    }


    //  注册机构直通按钮
    public function storeagree(OrgRequest $request)
    {
        $user_id = session()->get('user.id');

        $user = User::findOrFail($user_id);

        $same_name = Org::where('name', $request->name)->where('region', $request->region)->get();
        if (!$same_name->isEmpty()) {
            return ResponseJson([], "同地区已有该机构名称");
        }

        $hash = Hash::make('123456');

        // 创建机构信息
        $org = Org::create([
            'name' => $request->name,
            'password' => $hash,
            'region' => $request->region,
            'status' => 1
        ]);


        // 新建机构默认添加机构本部
        $depts = Dept::create([
            'org_id' => $org->id,
            'name' => $org->name,
            'level' => 0,
            'status' => 1
        ]);
        // 将机构超管添加进机构本部
        DeptUser::create([
            'dept_id' => $depts->id,
            'user_id' => $user_id
        ]);

        // 新建机构默认添加“所有人”工作群组
        $group = Group::create([
            'org_id' => $org->id,
            'name' => "所有人",
            'type' => 0,
            'status' => 1,
        ]);
        // 将机构超管添加进该工作群组
        GroupUser::create([
            'group_id' => $group->id,
            'user_id' => $user_id,
            'role_id' => 5
        ]);

        $org->code = date("Ymd");
        $pattern = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        for ($i = 0; $i < 6; $i++) {
            $org->code .= $pattern[mt_rand(0, 25)];
        }

        $depts->save();
        $org->save();

        // 往表中插入默认类型
        AskType::insert([
            ['org_id' => $org->id, 'name' => '工作'],
            ['org_id' => $org->id, 'name' => '请假'],
            ['org_id' => $org->id, 'name' => '用车']
        ]);

        // 如果用户还没有机构,is_default为1，否则为0
        $is_default = $user->orgs->count() == 0 ? 1 : 0;

        $user->orgs()
            ->syncWithoutDetaching([
                $org->id => [
                    'role_id' => ROLE_SYS_SUPER,
                    'is_default' => $is_default
                ]
            ]);


        $result = $user->orgs()->find($org->id);
        $resilt_all = $result->toArray();


        // 创建推送消息
        $message = (object)[];
        $message->title = '您已成功注册【' . $resilt_all['name'] . '】';
        $message->content = '请参阅使用帮助进行必要的设置';
        $message->type = 5;
        $message->subtype = 1;
        $message->params = json_encode([
            'id' => $user_id,
            'org_id' => $resilt_all['id']
        ]);
        $message->user_id = $user_id;

        MessageController::create($message, false);

        return ResponseJson($result);
    }

    // 获取一个机构的信息
    public function show(OrgRequest $request, Org $org)
    {
        $org->load(['depts', 'groups', 'depts.users', 'groups.users']);
        $data = $org;

        return ResponseJson($data);
    }


    // 对接机构:获取机构信息
    public function getByCodeMergeOrg(OrgRequest $request)
    {
        $data = Org::where('code', $request->code)->first();
        $user_id = session()->get('user.id');
        // 判断用户是否已经加入该机构
        if ($data == null) {
            return ResponseJson($data);
        }
        if ($data != null) {
            $data->load(['depts', 'users']);
        }
        $role_id = null;
        if ($data->users()->find($user_id)) {
            $role_id = $data->users()->find($user_id)->pivot->role_id;
        }
        $data = $data->toArray();
        $data['role_id'] = $role_id;

        return ResponseJson($data);
    }

    // 根据编码获取机构
    public function getByCode(OrgRequest $request)
    {
        $group_id = $request->input('group', -1);
        if ($group_id == -1) {
            $data = Org::where('code', $request->code)->orWhere('name', 'like', '%' . $request->code . '%')->get();

            if (count($data) == 0) { // 计算获取到的数组长度
                $user = User::where('tel', 'like', '%' . $request->code . '%')->first(); // 根据条件关联获取后台数据
                if ($user == null) {
                    return ResponseJson($user);
                } else {
                    $data = $user->orgs()->get(); // 根据获取到的数据进行二次拆分得到需要的数组
                }
            }
        } else {
            $data = Org::where('code', $request->code)->get();
        }
        $user_id = session()->get('user.id');



        $data->depts_id =  $data[0]->depts[0]->id;

        // 判断用户是否已经加入该机构
        if (count($data) == 0) {
            return ResponseJson($data);
        } else {
            foreach ($data as $key => $value) {
                $value->load(['depts', 'users']);

                $role_id = null;

                if ($value->users()->find($user_id)) {
                    $role_id = $value->users()->find($user_id)->pivot->role_id;
                }
                $value = $value->toArray();

                $data[$key]['role_id'] = $role_id;
            }
        }


        return ResponseJson($data);
    }

    // 获取登录用户机构信息一览
    public function indexWithLogin(OrgRequest $request, Org $org = null)
    {
        $user_id = session()->get('user.id');
        $user = User::findOrFail($user_id);

        if ($org == null) {
            $data = $user->orgs;

            foreach ($data as &$org) {
                $org_ids = [];
                $temp = [$org->id];
                while ($temp != []) {
                    $temp_org = Org::find($temp[0]);
                    $org_ids[] = $temp[0];
                    array_splice($temp, 0, 1);
                    $parent = $temp_org->parent()->get()->pluck('id')->toArray();
                    array_splice($temp, count($temp) - 1, 0, $parent);
                }
                $temp = [$org->id];
                while ($temp != []) {
                    $temp_org = Org::find($temp[0]);
                    $org_ids[] = $temp[0];
                    array_splice($temp, 0, 1);
                    $children = $temp_org->children()->get()->pluck('id')->toArray();
                    array_splice($temp, count($temp) - 1, 0, $children);
                }
                $org->depts = $user->depts($org->id)->get();
                $org->depts_count = $org->depts->count();
                $org->groups_count = $user->groups()->whereIn('groups.org_id', $org_ids)->count();
                $org->ask_type = $org->ask_type;
                $org->other_contact_num = 0;
                $org->star_contact_num = 0;
            }
        } else {
            $dept_ids = $user->depts;

            $org_ids = [];
            $temp = [$org->id];
            while ($temp != []) {
                $temp_org = Org::find($temp[0]);
                $org_ids[] = $temp[0];
                array_splice($temp, 0, 1);
                $parent = $temp_org->parent()->get()->pluck('id')->toArray();
                array_splice($temp, count($temp) - 1, 0, $parent);
            }
            $temp = [$org->id];
            while ($temp != []) {
                $temp_org = Org::find($temp[0]);
                $org_ids[] = $temp[0];
                array_splice($temp, 0, 1);
                $children = $temp_org->children()->get()->pluck('id')->toArray();
                array_splice($temp, count($temp) - 1, 0, $children);
            }

            $data = User::with([
                'depts' => function ($q) use ($org) {
                    $q->where('org_id', $org->id);
                },
                'groups' => function ($q) use ($org_ids) {
                    $q->whereIn('org_id', $org_ids);
                },
                'orgs' => function ($q) use ($org) {
                    $q->where('org_id', $org->id);
                },
                'depts.users',
                'groups.users'
            ])->find($user_id);
            $data['org_count'] = $user->orgs()->count();
        }

        return ResponseJson($data);
    }

    // 用户退出机构
    public function leaveOrg(OrgRequest $request, Org $org)
    {
        $user_id = session()->get('user.id');
        $user = User::findOrFail($user_id);

        $user->orgs()->detach($org->id);

        if (count($user->orgs) != 0) {
            $user->orgs()->updateExistingPivot($user->orgs()->first()->id, ['is_default' => 1]);
        };

        return ResponseJson();
    }

    // 编辑机构信息
    public function updateOrg(OrgRequest $request, Org $org)
    {
        $org->name = $request->name;
        $org->code = $request->code;
        $org->save();

        return ResponseJson();
    }


    // 用来获取用户下的部门,加了一个 ask 传值判断是否需要检查是否是第一次发送请示 todo 机构本部
    public function getUsersByDepts(Request $request, Org $org)
    {
        $user_id = session()->get('user.id');
        $org_depts_users = $org->with([
            'users',
            'users.depts' => function ($q) use ($org) {
                $q->where('org_id', $org->id);
            },
        ])->find($org->id);
        $base_arr = [$org->name => []];
        $dept_arr = [];
        $unit_arr = [];

//        $is_one_sendask = [];
//        $ask_send_user = [];
//
//        if ($request->ask != "false"){
//
//            $check_sum =  AskUser::where([
//                ['ask_id', $request->ask_id],
//                ['item_type',0]
//            ])->count();
//
//            $ask_send_user = AskUser::where([
//                ['ask_id', $request->ask_id],
//                ['item_type',1]
//            ])->first();
//
//            if ($check_sum === 0){
//                $is_one_sendask['is_oneask'] = true;
//            }
//        }

        $user_depts_count_arr = [];
        $temp_user_id_arr = [];
        foreach ($org_depts_users->users as $user) {
            // 用户部门数计算
            $user_depts_count_arr[$user->id] = count($user->depts);
            // 防止重复统计用户
            if (in_array($user->id, $temp_user_id_arr)) continue;
            // 如果是用户自己就跳过
            if ($user->id == $user_id) continue;

//            // 如果是发送人,跳过
//            if($request->ask != "false"){
//                if($ask_send_user['work_send_id'] ===$user->id){
//                    continue;
//                }
//            }

            foreach ($user->depts as $dept) {
                // 机构本部
                if ($dept->name === $org->name) {
                    array_push($base_arr[$org->name], $user->toArray());
                } else {
                    // 防止重复统计用户
                    if (in_array($user->id, $temp_user_id_arr)) continue;
                    switch ($dept->level) {
                        case 0:
                            if (!isset($dept_arr[$dept->name])) $dept_arr[$dept->name] = [];
                            array_push($dept_arr[$dept->name], $user->toArray());
                            $temp_user_id_arr[] = $user->id;
                            break;
                        case 1:
                            if (!isset($unit_arr[$dept->name])) $unit_arr[$dept->name] = [];
                            array_push($unit_arr[$dept->name], $user->toArray());
                            $temp_user_id_arr[] = $user->id;
                            break;
                    }
                }
            }
        }
//        dd($temp_user_id_arr);
//        dd($unit_arr);

        // 如果机构只有机构本部则
        // 如果机构名称为纯数字， array_merge 后，索引会转为数字.. todo
        if(count($dept_arr) === 0 && count($unit_arr) === 0) {
            $new_base_arr[$org->name] = count($base_arr) !== 0 ? $base_arr[$org->name] : $base_arr;
            $data = array_merge($new_base_arr);
        }else{
            $new_base_arr = [$org->name => []];
            // 防止空数组报错
            if(count($base_arr) === 0) $base_arr[$org->name] = [];
            foreach ($base_arr[$org->name] as $u){
                if($user_depts_count_arr[$u['id']] === 1){
                    $new_base_arr[$org->name][] = $u;
                }
            }
            $data = array_merge($new_base_arr, $dept_arr, $unit_arr);
        }

        return ResponseJson($data);
    }

    // 切换默认机构
    public function changeDefault(OrgRequest $request, Org $org)
    {

        $user_id = session()->get('user.id');
        $user = User::findOrFail($user_id);

        $change_org = Org::find($request->change_org);

        $user->orgs()
            ->syncWithoutDetaching([
                $org->id => [
                    'is_default' => 0
                ],
                $change_org->id => [
                    'is_default' => 1
                ]
            ]);

        return ResponseJson();
    }

    // 修改超管密码
    public function changePassword(OrgRequest $request)
    {
        $user_id = session()->get('user.id');
        $user = User::findOrFail($user_id);
        $org = $user->orgs()->where('orgs.id', $request->org_id)->first();

        // 判断是否为超管
        hasRole($org->pivot->role_id, [ROLE_SYS], "权限不足，无法操作");
        $phone = $user->tel;
        request()->offsetSet('mobile', $phone);
        request()->offsetSet('access_token', $phone . $org->code);

        //验证数据
        $validator = Validator::make(request()->all(), [
            'mobile' => 'required|confirm_mobile_not_change|confirm_rule:mobile_required',
            'sms_code' => 'required|verify_code',
        ]);
        if ($validator->fails()) {
            //验证失败后建议清空存储的发送状态，防止用户重复试错
            // SmsManager::forgetState();
            return ResponseJson([], "验证码错误");
        }
        if (!$request->pwd === $request->pwd_comfirm) {
            return ResponseJson([], "密码不一致");
        }

        $hash = Hash::make($request->pwd);
        $org->password = $hash;
        $org->save();
        return ResponseJson();
    }

    // 检查验证码
    public function checkSmsCode(OrgRequest $request)
    {

        $user_id = session()->get('user.id');

        $user = User::findOrFail($user_id);

        $org = $user->orgs()->where('orgs.id', $request->org_id)->first();

        $name = $request->name;
        $phone = $request->phone;

        request()->offsetSet('mobile', $phone);

        if ($request->org_id == null) {
            request()->offsetSet('access_token', $phone . $request->openid);
            $user->identity = $request->identity;
        } else {
            request()->offsetSet('access_token', $phone . $org->code);
        }

        //验证数据
        $validator = Validator::make(request()->all(), [
            'mobile' => 'required|confirm_mobile_not_change|confirm_rule:mobile_required',
            'sms_code' => 'required|verify_code',
        ]);

        if ($validator->fails()) {
            //验证失败后建议清空存储的发送状态，防止用户重复试错
            //  SmsManager::forgetState();
            return ResponseJson([], "验证码错误");
        }

        $user->name = $name;
        $user->tel = $phone;
        $user->save();
        return ResponseJson();
    }

    // 发送验证码
    // preg_match('/^(\+?0?86\-?)?(((13[0-9])|(14[5,7])|(15[0-3,5-9])|(17[0,3,5-8])|(18[0-9])|166|198|199|(147))\\d{8})$/', $value);  短信验证要修改，不然199开头等新号无法使用.
    public function sendSmsCode(Request $request)
    {

        $user_id = session()->get('user.id');
        $user = User::findOrFail($user_id);


        $org = $user->orgs()->where('orgs.id', request()->offsetGet('org_id'))->first();

        // 存在传入的手机号,即使用传入的手机号进行验证
        if ($request->phone != null) {
            $in_phone = DB::table('users')->where('tel', '=', $request->phone)->first();
            if ($in_phone != null) {
                return ResponseJson([], '手机号码已存在，请切换手机号码重试！');
            }
            $phone = $request->phone;
            request()->offsetSet('mobile', $phone);
        } else {
            // 判断是否为超管
            hasRole($org->pivot->role_id, [ROLE_SYS], "权限不足，无法操作");
            $phone = $user->tel;
            request()->offsetSet('mobile', $phone);
        }

        if ($request->org_id == null) {
            request()->offsetSet('access_token', $phone . $request->openid);

        } else {
            request()->offsetSet('access_token', $phone . $org->code);
        }

        $result = SmsManager::validateSendable();
        if (!$result['success']) {
            return ResponseJson([], $result['message']);
        }

        $result = SmsManager::validateFields();
        if (!$result['success']) {
            return ResponseJson([], $result['message']);
        }

        $result = SmsManager::requestVerifySms();
        if (!$result['success']) {
            return ResponseJson([], $result['message']);
        }
        return ResponseJson([
            'msg' => $result['message']
        ]);
    }


    // 查找用户
    public function search_bk(OrgRequest $request)
    {
        if (trim($request->name) == "") {
            return ResponseJson();
        }
        $search_name = $request->name;
        $search_name = strtr($search_name, array('%' => '\%', '_' => '\_', '\\' => '\\\\'));
        $data = [];
        $user_id = session()->get('user.id');
        $org_ids = DB::table('org_user')->where('user_id', $user_id)->pluck('org_id')->toArray();
        $dept_ids = DB::table('dept_user')->where('user_id', $user_id)->pluck('dept_id')->toArray();
        $group_ids = DB::table('group_user')->where('user_id', $user_id)->pluck('group_id');
        // 循环查询子机构
        $temp_orgs = $org_ids;
        while ($temp_orgs != []) {
            $child_orgs = Org::find($temp_orgs[0])->children()->get()->pluck('id')->toArray();
            array_splice($temp_orgs, 0, 1);
            array_splice($temp_orgs, count($temp_orgs) - 1, 0, $child_orgs);
            array_splice($org_ids, count($org_ids) - 1, 0, $child_orgs);
        }
        $temp_depts = $dept_ids;
        while ($temp_depts != []) {
            $sub_orgs = Dept::find($temp_depts[0])->sub_org()->get();
            array_splice($temp_depts, 0, 1);
            foreach ($sub_orgs as $org) {
                $child_depts = $org->depts()->get()->pluck('id')->toArray();
                array_splice($temp_depts, count($temp_depts) - 1, 0, $child_depts);
                array_splice($dept_ids, count($org_ids) - 1, 0, $child_depts);
            }
        }
        // 去重
        $org_ids = array_flip(array_flip($org_ids));
        $dept_ids = array_flip(array_flip($dept_ids));

        $users = User::where('name', 'like', '%' . $search_name . '%')->get();
        foreach ($users as $user) {
            foreach ($org_ids as $org_id) {
                $inOrg = DB::table('org_user')->where('org_id', $org_id)->where('user_id', $user->id)->get();
                if (!$inOrg->isEmpty()) {
                    $org_name = Org::where('id', $org_id)->first()->name;
                    $dept_names = $user->depts($org_id)->get()->pluck('name');
                    foreach ($dept_names as $dept_name) {
                        $data[] = [
                            'user_id' => $user->id,
                            'name' => $user->name,
                            'avatar' => $user->avatar,
                            'user_type' => 1,
                            'dept_name' => $dept_name,
                            'org_name' => $org_name
                        ];
                    }
                }
            }
            foreach ($dept_ids as $dept_id) {
                $inDept = DB::table('dept_user')->where('dept_id', $dept_id)->where('user_id', $user->id)->get();
                if (!$inDept->isEmpty()) {
                    $dept = Dept::where('id', $dept_id)->with('org')->first();
                    $data[] = [
                        'user_id' => $user->id,
                        'name' => $user->name,
                        'avatar' => $user->avatar,
                        'user_type' => 2,
                        'org_name' => $dept->org->name,
                        'dept_name' => $dept->name
                    ];
                }
            }
            foreach ($group_ids as $group_id) {
                $inGroup = DB::table('group_user')->where('group_id', $group_id)->where('user_id', $user->id)->get();
                if (!$inGroup->isEmpty()) {
                    $group = Group::where('id', $group_id)->with('org')->first();
                    $data[] = [
                        'user_id' => $user->id,
                        'name' => $user->name,
                        'avatar' => $user->avatar,
                        'user_type' => 3,
                        'org_name' => $group->org->name,
                        // 为前端方便，与其他保持一致 dept_name
                        'dept_name' => $group->name
                    ];
                }
            }
        }
        return ResponseJson($data);
    }

    public function search(OrgRequest $request)
    {
        if (!isset($request->key_word)) {
            return ResponseJson([], "当前机构中找不到符合条件的用户");
        }

        $users = User::where('name', 'like', "%{$request->key_word}%")
            ->orwhere('tel', '=', "{$request->key_word}")->get();

        if (count($users) === 0) {
            return ResponseJson([], "当前机构中找不到符合条件的用户");
        }

        return ResponseJson($users);
    }


    public function getOrgUsers(Org $org)
    {
        dd($org->users->toArray());
    }

    // 请求合并机构到部门
    public function MergeOrg(OrgRequest $request)
    {
        $user_id = session()->get('user.id');
        $user = User::findOrFail($user_id);
        $org = $user->orgs()->where('orgs.id', $request->org_id)->first()->makeVisible('password');
        hasRole($org->pivot->role_id, [ROLE_SYS], "权限不足，无法操作");
        $merged = MergeDeptOrg::where('is_active', 1)->whereNull('deleted_at')->where('org_id', $org->id)->first();
        if ($merged != null) {
            return ResponseJson([], "已经对接到部门，不可重复对接");
        }
        if ($org->id == $request->merge_org_id) {
            return ResponseJson([], "不可对接到本机构所属部门");
        }
        // 判断是否对接到子孙机构
        $sup = $request->merge_org_id;
        while ($sup != 0) {
            $sup = Org::find($sup)->parent_id;
            if ($sup == $request->org_id) {
                return ResponseJson([], "不可对接到下属机构");
            }
        }

        if (!Hash::check($request->password, $org->password)) {
            return ResponseJson([], "密码错误");
        }

        $mergeDeptOrg = new MergeDeptOrg;
        $mergeDeptOrg->dept_id = $request->dept_id;
        $mergeDeptOrg->org_id = $org->id;
        $mergeDeptOrg->dept_org_id = $request->merge_org_id;
        $mergeDeptOrg->save();

        // 获取对应超管id
        $send_id = OrgUser::where('org_id', $request->merge_org_id)->where('role_id', ROLE_SYS)
            ->first()->user_id;
        $open_id = User::find($send_id)->openid;

        // 创建推送消息 Message todo
        $message = (object)[];
        $message->title = '机构对接申请';
        $message->content = '您有新的机构对接申请，请及时处理。';
        $message->type = 5;
        $message->subtype = 2;
        $message->params = json_encode([
            'id' => $mergeDeptOrg->id,
            'org_id' => $mergeDeptOrg->dept_org_id   //  需要对接的部门的ID
        ]);
        $message->user_id = $send_id;
        // 微信模板消息所需参数
        $message->openid = $open_id;
        $message->wx_title = "机构对接申请";
        $message->wx_msg_time = date("Y-m-d H:i:s");
        MessageController::create($message, false);

        return ResponseJson();
    }

    // 请求对接机构详情页
    public function MergeOrgMsg(OrgRequest $request)
    {
        $mergeDeptOrg = MergeDeptOrg::withTrashed()->find($request->apply_id);
        if ($mergeDeptOrg == null) {
            return ResponseJson([], "错误");
        }
        $user_id = session()->get('user.id');
        $user = User::findOrFail($user_id);
        $user_1 = Org::find($mergeDeptOrg->dept_org_id)->users()->where('role_id', ROLE_SYS)->first()->id;
        $user_2 = Org::find($mergeDeptOrg->org_id)->users()->where('role_id', ROLE_SYS)->first()->id;
        hasRole($user_id, [$user_1, $user_2], "权限不足，无法查看");

        $data = [
            'org_id' => $mergeDeptOrg->org_id,
            'org_name' => Org::find($mergeDeptOrg->org_id)->name,
            'dept_id' => $mergeDeptOrg->dept_id,
            'dept_name' => Dept::find($mergeDeptOrg->dept_id)->name,
            'dept_org_id' => $mergeDeptOrg->dept_org_id,
            'apply_user' => Org::find($mergeDeptOrg->org_id)->users()->first()->name,
            'result' => 2
        ];
        if ($mergeDeptOrg->trashed()) {
            $data['result'] = 0;
        } else {
            if ($mergeDeptOrg->is_active == 1) {
                $data['result'] = 1;
            }
        }
        return ResponseJson($data);
    }

    // 机构对接批复
    public function MergeOrgReply(OrgRequest $request)
    {
        $user_id = session()->get('user.id');
        $user = User::findOrFail($user_id);
        $org = $user->orgs()->where('orgs.id', $request->org_id)->first()->makeVisible('password');
        hasRole($org->pivot->role_id, [ROLE_SYS_SUPER], "权限不足，无法操作");
        if (!Hash::check($request->password, $org->password)) {
            return ResponseJson([], "密码错误");
        }

        $mergeDeptOrg = MergeDeptOrg::withTrashed()->find($request->apply_id);
        if ($mergeDeptOrg->trashed() || $mergeDeptOrg->is_active == 1) {
            return ResponseJson([], "已批复,不可重复批复");
        }
        $sub_org_id = $mergeDeptOrg->org_id;
        $sub_org = Org::find($sub_org_id);
        $merge_id = $mergeDeptOrg->id;

        if ($request->aggrement == 1) {
            $mergeDeptOrg->is_active = 1;
            $mergeDeptOrg->save();
            $sub_org->parent_id = $org->id;
            $sub_org->save();
        } else {
            $mergeDeptOrg->delete();
            $sub_org->parent_id = 0;
            $sub_org->save();
        }

        $send_id = OrgUser::where('org_id', $sub_org_id)->where('role_id', ROLE_SYS)
            ->first()->user_id;
        $open_id = User::find($send_id)->openid;

        // 创建推送消息 Message todo
        $message = (object)[];
        $message->title = '机构对接申请处理结果';
        $message->content = '您的对接申请已处理，请及时查看结果。';
        $message->type = 5;
        $message->subtype = 3;
        $message->params = json_encode([
            'id' => $merge_id,
            'org_id' => $sub_org_id
        ]);
        $message->user_id = $send_id;
        // 微信模板消息所需参数
        $message->openid = $open_id;
        $message->wx_title = "机构对接申请结果";
        $message->wx_msg_time = date("Y-m-d H:i:s");
        MessageController::create($message, false);

        return ResponseJson();
    }

    // 超级管理员添加请示类型
    public function addAskType(OrgRequest $request)
    {
        $user = User::find(session()->get('user.id'));
        $org = $user->orgs()->where('orgs.id', $request->org_id)->first();
        hasRole($org->pivot->role_id, [ROLE_SYS_SUPER], "权限不足，无法操作");

        $ask_type = new AskType;
        $ask_type->org_id = $request->org_id;
        $ask_type->name = $request->name;
        $ask_type->save();
        return ResponseJson();
    }

    public function exitOrg(OrgRequest $request)
    {
        /** 获取 “执行用户实例” 和 “退出机构用户实例” **/
        // 获取当前执行用户实例
        $user = User::with([
            'orgs' => function ($q) use ($request) {
                $q->where('org_id', $request->org_id);
            }
        ])->find(session()->get('user.id'));


        // 获取要退出机构用户实例
        $exit_user = User::with([
            'orgs' => function ($q) use ($request) {         // 获取关联机构数据
                $q->where('org_id', $request->org_id);
            },
            'depts' => function ($q) use ($request) {        // 获取关联部门数据
                $q->where('org_id', $request->org_id);
            },
            'groups' => function ($q) use ($request) {       // 获取关联群组数据
                $q->where('org_id', $request->org_id);
            },
            'groups.users',                                  // 获取群组中用户数据
            'tasks' => function ($q) use ($request) {        // 获取关联任务数据
                $q->where(function ($_q) {                   // 获取我接收任务中未完成项
                    $_q->where('item_type', 0)// todo 状态为 “未发送” 的任务项，目前也当做未完成任务项处理
                    ->whereNotIn('status', [4]);
                })->orWhere(function ($_q) {                 // 获取我发送任务中所有通过的审批的项
                    $_q->where('item_type', 1)// todo 状态为 “待流转审核” 的任务项，目前也当做通过审批的任务项处理
                    ->whereNotIn('status', [3]);
                });
            },
            'tasks.task_items',                              // 获取符合上述任务条件的任务子项 todo 优化后可不加载此项
        ])->find($request->exit_user_id);


        // 是否自己退出: true 是, false 不是
        $self_exit = (int)$user->id === (int)$request->exit_user_id ? true : false;


        /** 判断用户身份情况 **/
        // 自退和飞人差异逻辑
        // 若是飞人情况，必须超管或系统管理员才可飞人
        if (!$self_exit) {
            if (!in_array($user->orgs[0]->pivot->role_id, [1, 2])) {
                return ResponseJson([], "非机构超管或机构系统管理员，无权进行此操作");
            }
        }
        // 差异逻辑结束, 后续业务逻辑大体可公用，变量差异通过 $self_exit 变量三目赋值

        // 判断退出用户是否属于机构
        if ($exit_user->orgs->count() === 0) {
            return ResponseJson([], "非机构用户,非法操作");
        }

        // 判断退出用户是否是超管
        if ($exit_user->orgs[0]->pivot->role_id === 1) {
            $err_msg = $self_exit ? "请先转让超管权限再退出" : "不可踢超管出机构！";
            return ResponseJson([], $err_msg);
        }


        /** 判断用户的工作情况 */
        $self_send_work_arr = [];
//        foreach ($exit_user->tasks as $k => $t) {
//            // 判断是否为接收任务
//            if ($t->pivot->item_type === 0) {
//                return ResponseJson([], "您接收的任务中有未办结项，请办结后再尝试退出");
//            }
//            // 判断我发出的任务是否全部办结 todo 可优化逻辑,避免双重循环
//            foreach ($t->task_items as $i => $ti) {
//                if ($ti->item_type === 0 && $ti->status != 4) {
//                    return ResponseJson([], "您发出的任务中有未办结项，请办结后再尝试退出");
//                }
//            }
//        }
        foreach ($exit_user->tasks as $k => $t) {
            // 判断是否为接收任务
            if ($t->pivot->item_type === 0) {
                return ResponseJson([], "您接收的任务中有未办结项，请办结后再尝试退出");
            } else {
                $self_send_work_arr[] = $t->id;
            }
        }

        // 查找所有我发出的任务的未完成子项
        $self_send_work_count = TaskUser::where([
            ['item_type', 0],
            ['status', '<>', 4],
        ])->whereIn('task_id', $self_send_work_arr)->count();
        if ($self_send_work_count !== 0) {
            return ResponseJson([], "您发出的任务中有未办结项，请办结后再尝试退出");
        }


        /** 判断用户群组逻辑 */
        $owner_group_arr = [];
        $smart_owner_group_arr = [];


        foreach ($exit_user->groups as $k => $g) {

            // 判断是不是群主
            if (in_array($g->pivot->role_id, [5, 9])) {
                $owner_group_arr[] = $g->id;
                $smart_owner_group_arr[$g->id] = $g;
                // 如果是群主要先转让或解散群
                // 判断群内是否有其他任务管理员
//                $u_count = $g->users->count();
//                foreach ($g->users as $i => $u) {
//                    // 跳过自己
//                    if($exit_user->id === $u->id) continue;
//                    // todo 不考虑性能的写法，为了方便理解，留着做对比
//                    $org_role_id = $u->orgRole($request->org_id)->pivot->role_id;
//                    // 组员中遇到超管、系统管理员、任务管理员的，直接转让群组权限
//                    if(in_array($org_role_id, [1, 2, 3])){
//                        $owner_role_id = $g->type === 0 ? 5 : 9; //区分工作群和日程群
//                        // 群组角色转让
//                        GroupUser::where([
//                            ['group_id', $g->id],
//                            ['user_id', $u->id],
//                        ])->update(['role_id' => $owner_role_id]);
//                        break;
//                    }else if ($i === $u_count-1){
//                        // 群中无人可转让直接解散该群
//                        // 移除群中所有组员
//                        $g->users()->detach();
//                        // 删除群 todo 看是否要改为软删除
//                        Group::where('id',$g->id)->delete();
//                    }
//                }
            }
            // 退出群组
            $g->users()->detach($exit_user->id);
        }


        // 获取群组中的用户
        $group_users = GroupUser::with([
            'group',
            'user',
            'user.orgs' => function ($q) use ($request) {  // 关联用户的机构数据
                $q->where([
                    ['org_id', $request->org_id],
                ]);
            },
        ])->whereIn('group_id', $owner_group_arr)->get();

        // 循环转让群主或解散群
        $transfer_group_arr = [];

        $smart_count = [];

        foreach ($group_users as $k => $gu) {

            // 已经转交过权限的,略过
            if (in_array($gu->group_id, $transfer_group_arr)) {
                continue;
            }
            // 获取当前用户的机构角色
            $user_org_role = $gu->user->orgs[0]->pivot->role_id;
            if (in_array($user_org_role, [1, 2, 3])) {
                // 判断群组类型
                $owner_role_id = $gu->group->type === 0 ? 5 : 9;
                GroupUser::where([
                    ['group_id', $gu->group_id],
                    ['user_id', $gu->user_id],
                ])->update(['role_id' => $owner_role_id]);
                // 将群id加入已转让群组数组
                $transfer_group_arr[] = $gu->group_id;
            } else {
                // 统计不能被转让群主的成员人数
                if (!isset($smart_count[$gu->group_id])) {
                    $smart_count[$gu->group_id] = 0;
                }
                $smart_count[$gu->group_id]++;
            }

            // 群中无人可转让直接解散该群
            if (count($smart_owner_group_arr) !== 0 && count($smart_count) !== 0) {
                if ($smart_owner_group_arr[$gu->group_id]->users->count() - 1 === $smart_count[$gu->group_id]) {
                    // 移除群中所有组员, 删除群 todo 看是否要改为软删除
                    $smart_owner_group_arr[$gu->group_id]->users()->detach();
                    Group::where('id', $gu->group_id)->delete();
                }
            }
        }


        /** 退出用户关联的所有部门 */
        $exit_depts_id_arr = [];
        foreach ($exit_user->depts as $k => $d) {
            $exit_depts_id_arr[] = $d->id;
        }
        // 批量退出部门
        $exit_user->depts()->detach($exit_depts_id_arr);


        /** 退出用户关联的机构 */
        $exit_user->orgs()->detach($request->org_id);
        /** 若用户加入了多个机构，要设置默认机构 */
        $exit_user_left_org = OrgUser::where('user_id', 2)->first();
        if($exit_user_left_org){
            // 将剩余某个机构设为默认机构
            $exit_user_left_org->update(['is_default' => 1]);
        }

        /** 发送消息 */
        // todo
        // 是否自己退出: true 是, false 不是
        $message = (object)[];

        //  拿到用户退出机构下的所有管理员
        $admin_list = OrgUser::with(['user'])->where('org_id', $request->org_id)->whereIn('role_id', [1, 2])->get();

        if ($self_exit) {
            // 自己退出
            // 创建推送消息
            $message->title = "用户退出机构提醒";
            $message->content = $exit_user->name . '退出了' . $user->orgs[0]->name;
            $message->type = 5;
            $message->subtype = MSG_SP_ORG_DELETE_RES;

            // 微信模板消息所需参数
            $message->tpl = TPL_MESSAGES_RESULT;
            $message->keyword1 = $exit_user->name . '退出了' . $user->orgs[0]->name;
            $message->keyword2 = "已退出机构";
            $message->keyword3 = date("Y-m-d H:i:s");

            // 循环给所有管理员推送消息
            foreach ($admin_list as $k => $v) {
                $message->params = json_encode([
                    'id' => $v->user_id,                                    // 接收人ID
                    'sennd_id' => $request->exit_user_id                   // 发送人ID
                ]);
                $message->user_id = $v->user_id;                           // 管理员ID
                $message->openid = $v->user->openid;                       // 管理员的openid

                MessageController::create($message, true);
            }

        } else {
            // 被踢
            // 创建推送消息
            $message->title = "被移出机构提醒";
            $message->content = "您已被移出{$user->orgs[0]->name}";
            $message->type = 5;
            $message->subtype = MSG_SP_ORG_DELETE_RES;
            $message->params = json_encode([
                'id' => $request->exit_user_id,
                'sennd_id' => $user->id
            ]);
            $message->user_id = $request->exit_user_id;
            // 微信模板消息所需参数
            $message->openid = $exit_user['openid'];
            $message->tpl = TPL_MESSAGES_RESULT;
            $message->keyword1 = "您已被移出{$user->orgs[0]->name}";
            $message->keyword2 = "已退出机构";
            $message->keyword3 = date("Y-m-d H:i:s");

            MessageController::create($message, true);

        }


        return ResponseJson("退出机构成功");
    }


    // 剔除用户 or 用户主动退出，主动退出带 user_exit 参数
    public function delteluser(OrgRequest $request)
    {
        // 获取用户模型实例
        $then_user = User::find(session()->get('user.id'));

        // todo org_id 和 user_id 比较？
        if ($request->user_exit) {
            $check_user = $then_user->orgs()->where('org_id', $request->user_id)->count();
            if ($check_user == 0) {
                return ResponseJson([], '您不是此机构的用户！');
            }
        }

        // 管理员飞人
        if (!$request->user_exit) {

            $user = User::find($request->user_id);
            $org_roles = $user->orgs()->get();
            if ($then_user['id'] == $request->user_id) {
                return ResponseJson([], '您不能踢出自己！');
            }
            if ($org_roles[0]['pivot']['role_id'] == 1) {
                return ResponseJson([], '超管不能被删除！');
            }

            $roles = $user->groups()->where('org_id', $org_roles[0]['id'])->get();


            $task = TaskUser::where([['user_id', $then_user['id']], ['item_type', 0], ['status', '!=', 4]])
                ->orwhere([['user_id', $then_user['id']], ['item_type', 1], ['status', '!=', 4]])
                ->first();


        } else {

            //用户自己退出
            $org_roles = $then_user->orgs()->where([
                ['org_id', '=', $request->user_id],
                ['user_id', '=', $then_user['id']]
            ])->get();

            // 判断是否是超级管理员
            if ($org_roles[0]['pivot']['role_id'] == 1) {
                return ResponseJson([], '您是此机构的超级管理员，如想退出机构，请先转移超级管理员权限');
            }

            $task = TaskUser::where([['user_id', $then_user['id']], ['item_type', 0], ['status', '!=', 4]])
                ->orwhere([['user_id', $then_user['id']], ['item_type', 1], ['status', '!=', 4]])
                ->first();

            // todo 变量命名要有含义
            $roles = $then_user->groups()->where('org_id', $request->user_id)->where('user_id', '=',
                $then_user['id'])->get();

        }


        // 只有当用户没有任务的时候
        if ($task == null) {
            $need_delte_user = "";
            if (!$request->user_exit) {
                $need_delte_user = $user;
            } else {
                $need_delte_user = $then_user;

            }

            // todo 要明白业务逻辑孰轻孰重
            if (!$request->user_exit) {
                // 创建推送消息
                $message = (object)[];
                $message->title = $org_roles[0]['name'] . '管理员已将您移出机构';
                $message->content = $org_roles[0]['name'] . '已将您移出机构！';
                $message->type = 5;
                $message->subtype = MSG_SP_ORG_DELETE_RES;
                $message->params = json_encode([
                    'id' => $request->user_id,
                ]);
                $message->user_id = $request->user_id;
                // 微信模板消息所需参数
                $message->openid = $user['openid'];
                $message->tpl = TPL_MESSAGES_RESULT;
                $message->keyword1 = $org_roles[0]['name'] . '管理员已将您移出机构';
                $message->keyword2 = '已被移出' . $org_roles[0]['name'];
                $message->keyword3 = date("Y-m-d H:i:s");
            } else {
                // 当用户自己退出群组的时候
                $sys_list = [];
                $messages = [];

                // 创建推送消息
                $message = [];
                $message['title'] = $then_user['name'] . '退出' . $org_roles[0]['name'];
                $message['content'] = $then_user['name'] . '退出' . $org_roles[0]['name'];
                $message['type'] = 5;
                $message['subtype'] = MSG_SP_ORG_DELETE_RES;
                $message['params'] = null;
                $message['user_id'] = null;
                // 微信模板消息所需参数
                $message['openid'] = '';
                $message['tpl'] = TPL_MESSAGES_RESULT;
                $message['keyword1'] = $then_user['name'] . '退出' . $org_roles[0]['name'];
                $message['keyword2'] = $then_user['name'] . '退出' . $org_roles[0]['name'];
                $message['keyword3'] = date("Y-m-d H:i:s");

                //消息在超管跟管理员可以接受到
                $ords_sys = OrgUser::where([
                    ['org_id', '=', $request->user_id],
                    ['role_id', '=', 1],
                ])->orwhere([
                    ['org_id', '=', $request->user_id],
                    ['role_id', '=', 2]
                ])->get();

                //获取到全部管理
                foreach ($ords_sys as $k => $v) {
                    $sys_list[$k] = User::find($v['user_id'])->toArray();
                }

                //组装传输的数组
                foreach ($sys_list as $k => $v) {
                    $message['openid'] = $v['openid'];
                    $message['user_id'] = $v['id'];
                    $message['params'] = json_encode([
                        'id' => $v['id'],
                    ]);
                    $messages[$k] = $message;
                }

                foreach ($messages as $k => $v) {
                    if (gettype($v) == 'array' || getType($v) == 'object') {
                        $messages[$k] = (object)$v;
                    }
                }

            }


            // 没有加入群组的情况下
            if (count($roles) == 0) {

                // 机构下删除
                OrgUser::where([['org_id', $org_roles[0]['id']], ['user_id', $need_delte_user['id']]])->delete();

                // 设置查找到的下一个为默认机构,在有的情况下!
                $is_default = OrgUser::where('user_id', $need_delte_user['id'])->first();

                if ($is_default != null) {
                    $is_default->is_default = 1;
                    $is_default->save();
                }

                // 拿到机构下有这个用户存在的部门
                $depts_list = $need_delte_user->depts()->where([
                    [
                        'org_id',
                        '=',
                        $org_roles[0]['id']
                    ]
                ])->get()->pluck('id')->toArray();
                // 部门或下单单位下删除
                foreach ($depts_list as $kd => $vd) {
                    DeptUser::where([['dept_id', '=', $vd], ['user_id', '=', $need_delte_user['id']]])->delete();
                }
            }


            // todo 逻辑归类,避免重复代码
            foreach ($roles as $k => $v) {

                //当用户不是群主时候
                if ($v['pivot']['role_id'] != 5) {

                    // 机构下删除
                    OrgUser::where([['org_id', $org_roles[0]['id']], ['user_id', $need_delte_user['id']]])->delete();

                    // 设置查找到的下一个为默认机构,在有的情况下!
                    $is_default = OrgUser::where('user_id', $need_delte_user['id'])->first();
                    if ($is_default != null) {
                        $is_default->is_default = 1;
                        $is_default->save();
                    }


                    // 拿到机构下有这个用户存在的部门
                    $depts_list = $need_delte_user->depts()->where([
                        [
                            'org_id',
                            '=',
                            $org_roles[0]['id']
                        ]
                    ])->get()->pluck('id')->toArray();
                    // 部门或下单单位下删除
                    foreach ($depts_list as $kd => $vd) {
                        DeptUser::where([['dept_id', '=', $vd], ['user_id', '=', $need_delte_user['id']]])->delete();
                    }

                    //拿到属于这个机构下面的所有群组;
                    $orgs_list = $need_delte_user->groups()->where('org_id',
                        $org_roles[0]['id'])->get()->pluck('id')->toArray();

                    foreach ($orgs_list as $kg => $vg) {
                        // 删除用户表中的记录
                        GroupUser::where([['group_id', $vg], ['user_id', $need_delte_user['id']]])->delete();
                    }
                }

                //当删除用户是群主的时候
                if ($v['pivot']['role_id'] == 5) {

                    $groups_user = GroupUser::where('group_id', $v['id'])->count();

                    //当人数为0 或者 等于 1的时候 或 无人
                    if ($groups_user < 2 || $groups_user == null) {


                        // 机构下删除
                        OrgUser::where([
                            ['org_id', $org_roles[0]['id']],
                            ['user_id', $need_delte_user['id']]
                        ])->delete();
                        // 设置查找到的下一个为默认机构,在有的情况下!
                        $is_default = OrgUser::where('user_id', $need_delte_user['id'])->first();
                        if ($is_default != null) {
                            $is_default->is_default = 1;
                            $is_default->save();
                        }

                        //  拿到机构下有这个用户存在的部门
                        $depts_list = $need_delte_user->depts()->where([
                            [
                                'org_id',
                                '=',
                                $org_roles[0]['id']
                            ]
                        ])->get()->pluck('id')->toArray();
                        // 部门或下单单位下删除
                        foreach ($depts_list as $kd => $vd) {
                            DeptUser::where([
                                ['dept_id', '=', $vd],
                                ['user_id', '=', $need_delte_user['id']]
                            ])->delete();
                        }

                        //拿到属于机构下面存在这个用户的所有群组;
                        $orgs_list = $need_delte_user->groups()->where('org_id',
                            $org_roles[0]['id'])->get()->pluck('id')->toArray();
                        foreach ($orgs_list as $kg => $vg) {
                            // 删除用户
                            GroupUser::where([['group_id', $vg], ['user_id', $need_delte_user['id']]])->delete();
                            // 删除群组
                            Group::where([['id', '=', $vg], ['org_id', '=', $org_roles[0]['id']]])->delete();
                        }
                    }

                    //当用户人数大于1
                    if ($groups_user > 1) {


                        // 机构下删除
                        OrgUser::where([
                            ['org_id', $org_roles[0]['id']],
                            ['user_id', $need_delte_user['id']]
                        ])->delete();
                        // 设置查找到的下一个为默认机构,在有的情况下!
                        $is_default = OrgUser::where('user_id', $need_delte_user['id'])->first();
                        if ($is_default != null) {
                            $is_default->is_default = 1;
                            $is_default->save();
                        }

                        //拿到机构下有这个用户存在的部门
                        $depts_list = $need_delte_user->depts()->where([
                            [
                                'org_id',
                                '=',
                                $org_roles[0]['id']
                            ]
                        ])->get()->pluck('id')->toArray();

                        // 部门或下单单位下删除
                        foreach ($depts_list as $kd => $vd) {
                            DeptUser::where([
                                ['dept_id', '=', $vd],
                                ['user_id', '=', $need_delte_user['id']]
                            ])->delete();
                        }

                        //拿到属于这个机构下面的所有群组;
                        $orgs_list = $need_delte_user->groups()->where(
                            [
                                ['org_id', $org_roles[0]['id']],
                                ['role_id', '5']

                            ]
                        )->get()->pluck('id')->toArray();

                        // 将属于他的群组全部删除
                        foreach ($orgs_list as $kg => $vg) {
                            // 删除用户表中的记录
                            GroupUser::where([['group_id', $vg], ['user_id', $need_delte_user['id']]])->delete();

                            // 删除掉在群组用户中的记录
                            if (!$request->user_exit) {

                                // 当是被管理员删除的时候。 将是最早进入那个人设置为群主
                                $roles_update = GroupUser::where('group_id', '=', $vg)->first();
                                $roles_update->role_id = 5;
                                $roles_update->save();

                            } else {

                                $all = GroupUser::where('group_id', $vg)->get()->toArray();
                                foreach ($all as $k => $v) {
                                    $role_three = OrgUser::where([
                                        ['org_id', $org_roles[0]['id']],
                                        ['user_id', $v['user_id']],
                                        ['role_id', 3]
                                    ])->first();
                                }
                                if ($role_three == null) {
                                    GroupUser::where('group_id', '=', $vg)->delete();
                                    Group::where('id', '=', $vg)->delete();
                                } else {
                                    $uodate_role = GroupUser::where([
                                        ['group_id', $vg],
                                        ['user_id', $role_three['user_id']]
                                    ])->first();

                                    $uodate_role->role_id = 5;
                                    $uodate_role->save();

                                }
                            }
                        }


                    }
                }
            }

            // 发送微信消息 Message todo
            if ($request->user_exit) {
                foreach ($messages as $key => $value) {
                    MessageController::create($value, true);
                }
            } else {
                MessageController::create($message, true);
            }

            return ResponseJson();

        }

        if (!$request->user_exit) {
            $tips_test = '您选中的用户有尚未完成的任务，暂时不能删除！';
        } else {
            $tips_test = '您有尚未完成的任务，无法退出机构！';

        }
        return ResponseJson([], $tips_test);

    }

    // 获取某用户下所有机构 或 取 某机构下有无此用户
    public function getallorgs(OrgRequest $request)
    {
        if (empty($request->check_user)) {
            $user = User::find(session()->get('user.id'));
            $org = $user->orgs()->get();
            return ResponseJson($org);

        } else {

            $org = OrgUser::where(
                [
                    ['org_id', $request->org_id],
                    ['user_id', $request->user_id]
                ])->get();

            return ResponseJson($org);
        }
    }

    // 拒绝用户加入机构
    public function refused(OrgRequest $request)
    {
        $user = User::find(session()->get('user.id'));


        $msg = Message::where('id', $request->msg_id)->first();
        $receive_user = User::where('id', $msg->send_id)->first();                 // 接收人

        $msg->content = '已拒绝' . substr($msg->content, 0, strpos($msg->content, '，'));
        $msg->save();

        //更改其他管理员的消息回复
        $all_admingmsg = Message::where([
            ['type', 6],
            ['subtype', 1],
            ['send_id', $msg->send_id],
            ['params', $msg->params],
            ['user_id', '<>', $user->id]
        ])->get();


        foreach ($all_admingmsg as $k => $v) {
            $v->title = '管理员' . $user->name . '已拒绝' . $receive_user->name . '加入' . $request->org_name . '的申请';
            $v->content = '管理员' . $user->name . '已拒绝' . $receive_user->name . '加入' . $request->org_name . '的申请';
            $v->save();
        }

        // 构造消息
        $message = (object)[];
        // 创建推送消息
        $message->title = "加入机构结果返回";
        $message->content = "管理员" . $user->name . "拒绝您申请加入" . $request->org_name . "！";
        $message->type = 6;
        $message->subtype = MSG_SP_USR_JOIN_ORG_REJCET_RES;
        $message->send_id = $user->id;
        $message->params = json_encode([
            'id' => $msg->send_id,
            'send_id' => $user->id
        ]);
        $message->user_id = $msg->send_id;

        // 微信模板消息所需参数
        $message->openid = $receive_user['openid'];
        $message->tpl = TPL_APPLY_AUDIT_RESULT;
        $message->keyword1 = "申请加入机构";
        $message->keyword2 = "审核不通过";

        MessageController::create($message, true);

        return ResponseJson("已拒绝用户加入机构");

    }
}
