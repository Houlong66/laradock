<?php

namespace App\Http\Controllers;

use App\Models\OrgUser;
use Illuminate\Support\Facades\Cache;
use App\Models\User;
use App\Models\Dept;
use App\Models\Group;
use App\Models\Org;
use App\Models\Message;
use App\Models\Role;
use App\Http\Requests\UserRequest;
use App\Libraries\ApplicationHelper;
use Illuminate\Support\Facades\Log;
use App\Models\DeptUser;

class UserController extends Controller
{
    // 获取一个用户的信息
    public function show(UserRequest $request, User $user)
    {
        $user->load('orgs');
        $data = $user;

        foreach ($data->orgs->where('status', 1) as &$org) {
            $org->depts = $user->depts($org->id)->get();
            $org->groups = $user->groups($org->id)->get();
            $org->role = Role::find($org->pivot->role_id); // TODO
        }

        return ResponseJson($data);
    }

    // 获取登录用户的信息
    public function showWithLogin(UserRequest $request)
    {
        $user_id = session()->get('user.id');
        $user = User::findOrFail($user_id);


        $user->load([
            'orgs.users',
            'orgs.depts.users',
            'orgs.groups.users',
            'depts.users',
            'groups.users',
        ]);
        $data = $user;

//        $del_dept_arr = [];
        foreach ($data->orgs->where('status', 1) as &$org) {
//            $org->depts = $user->depts($org->id)->get();
//            $org->groups = $user->groups($org->id)->get();
            // 机构本部处理
            // 如果用户的机构只有一个部门，则保留机构本部，否则剔除机构本部
//            if(count($org->depts) !== 1){
//                $del_dept_arr[] = $org->depts[0]->id;
//                $org->depts->shift();
//                $org->depts->all();
//            }
            $org->role = Role::find($org->pivot->role_id); // TODO
        }
//        $new_depts = $data->depts->filter( function ($v, $k) use ($del_dept_arr) {
//            return !in_array($v->id, $del_dept_arr);
//        });
//        $data->depts = $new_depts->all();

        return ResponseJson($data);
    }


    // 获取部门内的用户
    public function getByDept(UserRequest $request, Dept $dept)
    {
        $data = collect();

        // 本部门用户
        $users = $dept->users()->get()->makeHidden('pivot');

        foreach ($users as $v ){
             $v->agreed  = OrgUser::with('agreed_user')->where([
                 ['user_id',$v->id],
                 ['org_id',$request->org_id],
                 ['role_id','<>',1]
             ])->first();
        }


        $data->put('users', $users);

        // 子机构
        $son_orgs = $dept->sub_org()->with([
            'users' => function ($query) {
                $query->select('users.id', 'avatar', 'name');
            }
        ])->get()->makeHidden('pivot');
        $data->put('son_orgs', $son_orgs);

        // 孙子机构
        $grandson_orgs = [];
        foreach ($son_orgs as $son_org) {
            $grandson_orgs[] = $son_org->id;
        }
        $grandson = collect();

        while ($grandson_orgs != []) {
            $orgs = Org::find($grandson_orgs[0])->children()
                ->with([
                    'users' => function ($query) {
                        $query->select('users.id', 'avatar', 'name');
                    }
                ])->get()->makeHidden('pivot');
            $grandson = $grandson->merge($orgs);
            $child_orgs = $orgs->pluck('id')->toArray();
            array_splice($grandson_orgs, 0, 1);
            array_splice($grandson_orgs, count($grandson_orgs) - 1, 0, $child_orgs);
        }

        $data->put('grandson_orgs', $grandson);

        return ResponseJson($data);
    }

    // 获取机构内的用户
    public function getByOrg(UserRequest $request, Org $org)
    {
        $users = $org->users;

        $data = User::with([
            'depts' => function ($q) use ($org) {
                $q->where('org_id', $org->id);
            },
            'orgs' => function ($q) use ($org) {
                $q->where('org_id', $org->id);
            }
        ])->get()->intersect($users);

        return ResponseJson($data);
    }

    // 获取群组内的用户
    public function getByGroup(UserRequest $request, Group $group)
    {
        $data = $group->load(['users']);

        foreach ($data->users as $key => $value) {
            $value->pivot->role['types'] = $data['type'];
            $data->users[$key]->pivot['type'] = $data['type'];
//            if($data->users[$key]->pivot['role_id'] != 5 && $data->users[$key]->pivot['type'] != 0){
//                $data->users[$key]->pivot['role']['name'] = '组员';
//            }
        }
        return ResponseJson($data);
    }


    // 获取群组内的用户并通过部门分组
    public function getByGroupGroupByDept(UserRequest $request, Group $group, Dept $dept)
    {
        $data = collect();

        foreach ($group->users as &$user) {
            $depts = $user->depts;
            foreach ($depts as $dept) {
                if (!$data->has($dept->name)) {
                    $data->put($dept->name, collect());
                }
                $data[$dept->name]->push($user);
            }
        }

        return ResponseJson($data);
    }

    // 通过名字搜索登陆用户相关的用户
    public function searchByNameWithLogin(UserRequest $request)
    {
        $user_id = session()->get('user.id');
        $user = User::findOrFail($user_id);
        $name = $request->input('name');
        $data = collect([
            'all' => collect(),
            'org' => collect(),
            'dept' => collect(),
            'group' => collect(),
            'other_contact_user' => collect(),
        ]);

        // 将名字匹配的且与当前登录用户不同的用户插入集合中
        // 搜索同机构的用户
        foreach ($user->orgs->where('status', 1) as $org) {
            foreach ($org->users as $user_item) {
                if (stristr($user_item->name, $name) && $user_item->id != $user->id) {
                    $data['org']->push($user_item);
                }
            }
        }
        $data['org'] = $data['org']->unique('id');

        // 搜索同部门的用户
        foreach ($user->depts as $dept) {
            foreach ($dept->users as $user_item) {
                if (stristr($user_item->name, $name) && $user_item->id != $user->id) {
                    $data['dept']->push($user_item);
                }
            }
        }
        $data['dept'] = $data['dept']->unique('id');

        // 搜索同群组的用户
        foreach ($user->groups as $group) {
            foreach ($group->users as $user_item) {
                if (stristr($user_item->name, $name) && $user_item->id != $user->id) {
                    $data['group']->push($user_item);
                }
            }
        }
        $data['group'] = $data['group']->unique('id');

        // 搜索联系人内的用户
        // $data['other_contact_user'] = $data['other_contact_user']->unique('id');

        $data['all'] = $data['org']
            ->merge($data['dept'])
            ->merge($data['group'])
            ->merge($data['other_contact_user'])
            ->unique('id');

        return ResponseJson($data);
    }

    // 登录用户更改个人信息
    public function updateWithLogin(UserRequest $request)
    {
        $user_id = session()->get('user.id');
        $user = User::findOrFail($user_id);

        $user->update(array_except(
            $request->all(),
            ['id', 'openid', 'tel', 'uniqueid']
        ));

        return ResponseJson($user);
    }

    // 登录用户获取手机验证码
    public function getTelCodeWithLogin(UserRequest $request)
    {
        $user_id = session()->get('user.id');
        $user = User::findOrFail($user_id);

        // 判断数据库中是否有此号码
        $tel = $request->input('tel');
        if (User::where('tel', $tel)->count() != 0) {
            return ResponseJson([], '手机号已被绑定');
        }

        $tel_code = rand(1000, 9999);
        $openid = $user->openid;
        Cache::put("{$openid}-tel-code", $tel_code, 5);
        $key = '';
        switch (env('APP_ENV')) {
            case 'local_deep':
                $key = '96c83c563d6664ebcdafd4db8f043f70';
                break;
            default:
                $key = '0d6531eda53b848be185a3daf79da6ab';
                break;
        }
        $url = 'http://v.juhe.cn/sms/send?mobile=' . $tel . '&tpl_id=66710&tpl_value=%23code%23%3D' . $tel_code . '&key=' . $key;
        $res = ApplicationHelper::curlPost($url, [], 'GET');

        return ResponseJson($res);
    }

    // 登录用户绑定手机号
    public function updateTelWithLogin(UserRequest $request)
    {
        $user_id = session()->get('user.id');
        $user = User::findOrFail($user_id);

        // 手机验证码
        $openid = $user->openid;
        $client_code = $request->input('code', '');
        $real_code = Cache::get("{$openid}-tel-code", '');
        if ($real_code == '' || $client_code != $real_code) {
            return ResponseJson([], '验证码不正确');
        }

        $user->update(['tel' => $request->input('tel', '')]);

        return ResponseJson($user);
    }

    // 获取未加入该分组的用户信息 todo 机构本部
    public function getByGroupWithoutJoin(UserRequest $request, Group $group)
    {
        $base_clt = collect();
        $dept_clt = collect();
        $unit_clt = collect();

        $org = Org::find($request->org_id);
        $users = Org::find($request->org_id)->users;

        $users_group = $group->users;

        $users = $users->diff($users_group);

        // 用于记录每个用户，此机构加入的部门数
        $user_depts_count_arr = [];
        foreach ($users as $user) {
            $depts = $user->depts($request->org_id)->with('org')->orderBy('level','asc')->get();
            $user_depts_count_arr[$user->id] = count($depts);

            foreach ($depts as $dept) {
                if ($dept->name === $org->name) {
                    if (!$base_clt->has($dept->name)) {
                        if ($dept) {
                            $base_clt->put($dept->name, collect());
                        }
                    }
                    $base_clt[$dept->name]->push($user);
                    continue;
                }

                // 部门
                if ($dept->level === 0) {
                    if (!$dept_clt->has($dept->name)) {
                        if ($dept) {
                            $dept_clt->put($dept->name, collect());
                        }
                    }
                    $dept_clt[$dept->name]->push($user);
                    continue;
                }

                // 单位
                if ($dept->level === 1) {
                    if (!$unit_clt->has($dept->name)) {
                        if ($dept) {
                            $unit_clt->put($dept->name, collect());
                        }
                    }
                    $unit_clt[$dept->name]->push($user);
                    continue;
                }

            }
        }

        // 子机构
        $depts = Org::find($request->org_id)->depts()->orderBy('level','asc')->get();

        foreach ($depts as $dept) {
            $sub_org = $dept->sub_org()->first();
            if ($sub_org === null) {
                continue;
            }
            $sub_users = $sub_org->users;
            $sub_org_ids = $sub_org->children()->get()->pluck('id')->toArray();
            while ($sub_org_ids != []) {
                $child_orgs = Org::find($sub_org_ids[0])->children()->get()->pluck('id')->toArray();
                $sub_users = $sub_users->merge(Org::find($sub_org_ids[0])->users);
                array_splice($sub_org_ids, 0, 1);
                array_splice($sub_org_ids, count($sub_org_ids) - 1, 0, $child_orgs);
            }
            $sub_users = $sub_users->diff($users_group);


            foreach ($sub_users as $sub_user) {

                if ($dept->name === $org->name) {
                    if (!$base_clt->has($dept->name)) {
                        $base_clt->put($dept->name, collect());
                    }
                    $sub_user->name = $sub_user->name . '-' .
                        Org::find($sub_user->pivot->org_id)->name;
                    $base_clt[$dept->name]->push($sub_user);
                    continue;
                }

                if ($dept->level === 0) {
                    if (!$dept_clt->has($dept->name)) {
                        $dept_clt->put($dept->name, collect());
                    }
                    $sub_user->name = $sub_user->name . '-' .
                        Org::find($sub_user->pivot->org_id)->name;
                    $dept_clt[$dept->name]->push($sub_user);
                    continue;
                }

                if ($dept->level === 1) {
                    if (!$unit_clt->has($dept->name)) {
                        $unit_clt->put($dept->name, collect());
                    }
                    $sub_user->name = $sub_user->name . '-' .
                        Org::find($sub_user->pivot->org_id)->name;
                    $unit_clt[$dept->name]->push($sub_user);
                    continue;
                }
            }
        }

        // 如果机构只有机构本部则
        if(count($dept_clt) === 0 && count($unit_clt) === 0) {
            $new_base_clt[$org->name] = count($base_clt) !== 0 ? $base_clt[$org->name] : $base_clt;
            $data_pre = collect([$new_base_clt]);
        }else{
            // 防止空集合报错
            if(count($base_clt) === 0) $base_clt[$org->name] = collect([]);
            // 清除机构本部中多余的用户
            $left_users = $base_clt[$org->name]->filter(function ($v, $k) use ($user_depts_count_arr){
                return $user_depts_count_arr[$v->id] === 1;
            });
            $left_users->all();
            $left_users = array_values($left_users->toArray());
            $new_base_clt[$org->name] = $left_users;
            $data_pre = collect([$new_base_clt, $dept_clt, $unit_clt]);
        }

        $data = $data_pre->collapse();
        $data->all();

        return ResponseJson($data);
    }

    // 获取机构用户并按部门划分 // todo 机构本部
    public function getUserByOrg(UserRequest $request, Org $org, $flag)
    {
        $base_clt = collect();
        $dept_clt = collect();
        $unit_clt = collect();

        $users = $org->users;

        $user_depts_count_arr = [];

        foreach ($users as $user) {


            $depts = $user->depts($request->org_id)->with('org')->orderBy('level','asc')->get();

            $user_depts_count_arr[$user->id] = count($depts);

            foreach ($depts as $dept) {
                if ($dept->name === $org->name) {
                    if (!$base_clt->has($dept->name)) {
                        if ($dept) {
                            $base_clt->put($dept->name, collect());
                        }
                    }
                    $base_clt[$dept->name]->push($user);
                    continue;
                }

                // 部门
                if ($dept->level === 0) {
                    if (!$dept_clt->has($dept->name)) {
                        if ($dept) {
                            $dept_clt->put($dept->name, collect());
                        }
                    }
                    $dept_clt[$dept->name]->push($user);
                    continue;
                }
                // 单位
                if ($dept->level === 1) {
                    if (!$unit_clt->has($dept->name)) {
                        if ($dept) {
                            $unit_clt->put($dept->name, collect());
                        }
                    }
                    $unit_clt[$dept->name]->push($user);
                    continue;
                }
            }
        }


        if ($flag == 'true') {
            // 子机构
            $depts = $org->depts()->get();
            foreach ($depts as $dept) {
                $sub_org = $dept->sub_org()->first();
                if ($sub_org === null) {
                    continue;
                }
                $sub_users = $sub_org->users;
                $sub_org_ids = $sub_org->children()->get()->pluck('id')->toArray();
                while ($sub_org_ids != []) {
                    $child_orgs = Org::find($sub_org_ids[0])->children()->get()->pluck('id')->toArray();
                    $sub_users = $sub_users->merge(Org::find($sub_org_ids[0])->users);
                    array_splice($sub_org_ids, 0, 1);
                    array_splice($sub_org_ids, count($sub_org_ids) - 1, 0, $child_orgs);
                }
                foreach ($sub_users as $sub_user) {

                    if ($dept->name === $org->name) {
                        if (!$base_clt->has($dept->name)) {
                            $base_clt->put($dept->name, collect());
                        }
                        $sub_user->name = $sub_user->name . '-' .
                            Org::find($sub_user->pivot->org_id)->name;
                        $base_clt[$dept->name]->push($sub_user);
                        continue;
                    }

                    if ($dept->level === 0) {
                        if (!$dept_clt->has($dept->name)) {
                            $dept_clt->put($dept->name, collect());
                        }
                        $sub_user->name = $sub_user->name . '-' .
                            Org::find($sub_user->pivot->org_id)->name;
                        $dept_clt[$dept->name]->push($sub_user);
                        continue;
                    }

                    if ($dept->level === 1) {
                        if (!$unit_clt->has($dept->name)) {
                            $unit_clt->put($dept->name, collect());
                        }
                        $sub_user->name = $sub_user->name . '-' .
                            Org::find($sub_user->pivot->org_id)->name;
                        $unit_clt[$dept->name]->push($sub_user);
                        continue;
                    }
                }
            }
        }

        // 如果机构只有机构本部则
        if(count($dept_clt) === 0 && count($unit_clt) === 0) {
            $new_base_clt[$org->name] = count($base_clt) !== 0 ? $base_clt[$org->name] : $base_clt;
            $data_pre = collect([$new_base_clt]);
        }else{
            // 防止空集合报错
            if(count($base_clt) === 0) $base_clt[$org->name] = collect([]);
            // 清除机构本部中多余的用户
            $left_users = $base_clt[$org->name]->filter(function ($v, $k) use ($user_depts_count_arr){
                return $user_depts_count_arr[$v->id] === 1;
            });
            $left_users->all();
            $left_users = array_values($left_users->toArray());
            $new_base_clt[$org->name] = $left_users;
            $data_pre = collect([$new_base_clt, $dept_clt, $unit_clt]);
        }

        $data = $data_pre->collapse();
        $data->all();

        return ResponseJson($data);
    }

    // 检查用户是否关注了公众号
    public function checkIsLike(UserRequest $request)
    {
        $user_id = session()->get('user.id');
        $app_conf = 'wechat.official_account.dudu';
        $app = app($app_conf);
        $data = [];
        $dept = Dept::find($request->dept_id);
        $user = User::find($user_id);

        $is_like = $user->is_followed == 1;

        $data['is_like'] = $is_like;
        if (!$is_like) {
            $result = $result = $app->qrcode->temporary("{$request->org_id}@{$request->dept_id}@{$request->role}",
                6 * 24 * 3600);
            $data['qrcode'] = $app->qrcode->url($result['ticket']);
        }
        return ResponseJson($data);
    }

    // 更新用户信息
    public function updateInfo(UserRequest $request)
    {
        $user_id = session()->get('user.id');
        $user = User::findOrFail($user_id);

        $user[$request->key] = $request->value;
        $user->save();

        return ResponseJson([]);
    }
}
