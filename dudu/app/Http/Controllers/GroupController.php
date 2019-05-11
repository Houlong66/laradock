<?php

namespace App\Http\Controllers;

use App\Models\MergeDeptOrg;
use App\Models\Message;
use App\Models\OrgUser;
use App\Models\Role;
use App\Models\User;
use App\Models\Org;
use App\Models\Group;
use App\Models\GroupUser;
use App\Http\Requests\GroupRequest;
use App\Models\TaskUser;
use http\Env\Request;

class GroupController extends Controller
{
    // 创建机构的群组
    public function store(GroupRequest $request)
    {
        $user_id = session()->get('user.id');
        $user = User::findOrFail($user_id);

        // 判断权限
        $role_id = $user->orgs()->where('orgs.id', $request->org_id)->first()->pivot->role_id;
        hasRole($role_id, [ROLE_SYS, ROLE_GRP, ROLE_SYS_TASK], "权限不足，无法操作");

        $group = Group::create([
            'org_id' => $request->org_id,
            'name' => $request->name,
            'type' => $request->type,
            'status' => 1
        ]);


        $user->groups()
            ->syncWithoutDetaching([
                $group->id => [
                    'role_id' => ROLE_GRP_CREATE
                ]
            ]);

        return ResponseJson($group);
    }

    // 获取某机构的群组信息一览
    public function getByOrg(GroupRequest $request, Org $org)
    {
        $data = $org->groups->load(['users']);

        return ResponseJson($data);
    }

    // 获取登陆用户某机构的群组信息一览
    public function getByOrgWithLogin(GroupRequest $request, Org $org)
    {
        $user_id = session()->get('user.id');
        $user = User::findOrFail($user_id);
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
        $data = $user->groups()->whereIn('groups.org_id', $org_ids)->get()->load(["users"]);

        return ResponseJson($data);
    }

    // 登录用户创建群组
    public function createWithLogin(GroupRequest $request, $org)
    {
        $user_id = session()->get('user.id');
        $user = User::findOrFail($user_id);

        $group = Group::create([
            'org_id' => $org,
            'name' => $request->input('name'),
            'type' => $request->input('type'),
            'status' => GRP_ST_ENABLED
        ]);

        $user->groups()->attach($group, ['role_id' => ROLE_GRP_CREATE]);

        return ResponseJson();
    }

    // 根据id获取群组
    public function getInfo(GroupRequest $request, Group $group)
    {

        $user_id = session()->get('user.id');

        $data = $group->load(['users']);

        return ResponseJson($data);
    }


    // 主动退群
    public function leaveGroup(GroupRequest $request)
    {
        $user_id = session()->get('user.id');

        $user = User::find($user_id);

        $groups = $user->groups()->where('groups.id', $request->group_id)->first();

        if ($user->groups()->where('groups.id', $request->group_id)->get()->isEmpty()) {
            return ResponseJson([], "你不在该群组中!");
        }

        $creator_id = GroupUser::where('group_id', $request->group_id)
            ->where('role_id', ROLE_GRP_CREATE)->first()->user_id;
        if ($user_id == $creator_id) {
            return ResponseJson([], "创建人不可退出群组");
        }

        $task = TaskUser::where(function ($query) use ($user_id) {
            $query->where('item_type', 0)->where('status', '!=', 4)->where('user_id', $user_id);
        })->orWhere(function ($query) use ($user_id) {
            $query->where('item_type', 1)->where('status', 1)->where('user_id', $user_id);
        })->orWhere(function ($query) use ($user_id) {
            $query->where('item_type', 2)->where('status', 1)->where('user_id', $user_id);
        })->orWhere(function ($query) use ($user_id) {
            $query->where('item_type', 3)->where('status', '!=', 4)->where('user_id', $user_id);
        })->get();

        // 判断当前用户要退出的群组类别是什么,若是工作群走下面,若不是,则绕开
        $group_list = $user->groups()->where('groups.id', $request->group_id)->get()->toArray();
        if ($group_list[0]['type'] == 0) {
            if (!$task->isEmpty()) {
                return ResponseJson([], "尚有任务未完成/未审核,请完成后再退群");
            }
        }

        $user->groups()->detach($request->group_id);

        // 创建推送消息
        $message = (object)[];
        $message->title = '用户'.$user->name.'退出了'.$groups->name;
        $message->content = '用户'.$user->name.'退出了'.$groups->name;
        $message->type = 6;
        $message->subtype = 8;
        $message->params = json_encode([
            'group_id' => $request->group_id,
            'user_id' => $user_id
        ]);
        $message->user_id = $creator_id;
        // 微信模板消息所需参数
        $message->openid = User::find($creator_id)->openid;
        $message->wx_title = "用户推出群组通知";
        $message->wx_msg_time = date("Y-m-d H:i:s");
        MessageController::create($message, false);

        return ResponseJson();
    }

    // 退出群聊详情页
    public function leaveGroupDetail(GroupRequest $request)
    {
        $user_id = session()->get('user.id');
        $creator = GroupUser::where('group_id', $request->group_id)->where('user_id', $user_id)->first();
        if ($creator->role_id !== ROLE_GRP_CREATE) {
            return ResponseJson([], "没有权限");
        }
        $user = User::select(['id', 'avatar', 'name'])->find($request->user_id);
        $group = Group::find($request->group_id);
        $data = collect();
        $data->push($user);
        $data->push($group);
        return ResponseJson($data->toArray());
    }

    // 用户申请加入群组
    public function addgroup(GroupRequest $request)
    {

        $user_msg = User::find(session()->get('user.id'));

        $user_id = session()->get('user.id');

        // 检查是否存在此机构中
        $exit_user = OrgUser::where([['user_id', $user_id], ['org_id', $request->org_id]])->first();

        // 获取选择的群组是否存在
        $exit_group = Group::where('id', $request->groupid)->first();

        // 判断此用户是否已经存在这个群组中
        $in_user = GroupUser::where([['group_id', $request->groupid], ['user_id', $user_msg['id']]])->first();

        // 检查是否为下级机构
        $next_org = MergeDeptOrg::where([['org_id', $request->org_id], ['dept_org_id', $exit_group['org_id']]])
            ->Orwhere([['org_id', $exit_group['org_id']], ['dept_org_id', $request->org_id]])
            ->first();


        if ($in_user != null) {
            return ResponseJson([], '您已在此群组中,无需重复添加');
        }
        if ($exit_group == null) {
            return ResponseJson([], '群组不存在,请刷新页面重试!');
        }

        if ($exit_user == null) {
            if ($next_org == null) {
                return ResponseJson([], '此用户不存在此机构下!');
            }
        }

        $create_user = GroupUser::where([
            ['group_id', $request->groupid],
            ['role_id', 5]
        ])->first();          // 找到对应群组中的群主
        $create_user_msg = User::find($create_user['user_id']);                   // 群主的信息
        $role_name = Role::where('id',
            $request->role_id)->first();                                         // 找到选选择的角色的信息

        // 创建推送消息
        $message = (object)[];
        $message->title = "申请加入群组";
        $message->content = "用户「{$user_msg['name']}」申请以「{$role_name['name']}」身份加入群组「{$exit_group['name']}」,请及时处理!";
        $message->type = 6;
        $message->subtype = MSG_SP_USR_APPLY_GRP_REQ;
        $message->send_id = $user_id;                           //发送人ID
        $message->user_id = $create_user_msg['id'];                           //接收人ID

        $message->params = json_encode([
            'id' => $create_user_msg['id'],              // 群主ID
            'group_id' => $request->groupid,                   // 群组ID
            'user_id' => $user_id,                            // 申请用户ID
            'role_id' => $request->role_id,                   // 申请用户选择的角色ID
            'group_type' => $exit_group['type']                  // 群组的类型

        ]);

        $message->user_id = $create_user_msg['id'];

        // 微信模板消息所需参数
        $message->openid = $create_user_msg['openid'];
        $message->tpl = TPL_APPLY_WAIT_AUDIT;
        $message->keyword1 = $user_msg['name'];
        $message->keyword2 = '申请加入群组「' . $exit_group['name'] . '」';
        $message->keyword3 = date("Y-m-d H:i:s");
        $message->keyword4 = '待审核';

        MessageController::create($message, true);


        return ResponseJson();
    }

    // 获取机构下所有群组
    public function getallgroups(GroupRequest $request)
    {
        $group = Group::where('org_id', $request->orgsid)->get()->toArray();

        $allgroup = array("工作群组" => [], "日程群组" => []);

        foreach ($group as $k => $v) {
            if ($v['type'] == 0) {
                array_push($allgroup['工作群组'], $v);
            } else {
                array_push($allgroup['日程群组'], $v);
            }
        }

        return ResponseJson($allgroup);
    }

    // 同意用户加入群组
    public function agreedjoin(GroupRequest $request)
    {
//        var_dump($request->id);         // 群主ID 2
//        var_dump($request->group_id);   // 群组ID 6
//        var_dump($request->user_id);    // 申请的用户的ID 3
//        var_dump($request->msg_id);     // 申请消息的ID 2


        $old_msg = Message::where('id', $request->msg_id)->first();

        // 检查用户是否存在群组中
        $check_user = GroupUser::where([['user_id', $request->user_id], ['group_id', $request->group_id]])->first();
        if ($check_user != null) {
            $old_msg->status = 1;
            $old_msg->save();
            return ResponseJson([], '此用户已经存在群组中!');
        }

        // 检查当前操作用户是否是这个群组的创建人
        $check_user_create = GroupUser::where([
            ['user_id', session()->get('user.id')],
            ['role_id', 5],
            ['group_id', $request->group_id],
        ])->first();
        if ($check_user_create == null) {
            return ResponseJson([], '您无权进行操作!');
        }


        $group = Group::where('id', $request->group_id)->first();

        if (isset($request->role_id)) {
            $role_id = $request->role_id;
        } else {
            $role_id = $group['type'] == 0 ? ROLE_GRP_SIGN : ROLE_GRP_SHARED;
        }

        // 往数据表中插入内容
        $group_user = GroupUser::create([
            'group_id' => $request->group_id,
            'user_id' => $request->user_id,
            'role_id' => $role_id
        ]);


        // 修改原来的消息的参数
//        $old_msg->title = '已同意' . $old_msg->title;
        $old_msg->content = '已同意' . substr($old_msg->content, 0, strpos($old_msg->content, ','));
//        $old_msg->url = "/group_user_list?group_id={$request->group_id}";
        $old_msg->status = 1;
        $old_msg->save();


        // 发送消息给申请用户
        // 创建推送消息
        $message = (object)[];
        $message->title = '加入「' . $group['name'] . '」结果返回';
        $message->content = '您加入「' . $group['name'] . '」群组的申请已通过!';
        $message->type = MSG_TP_USR;
        $message->subtype = MSG_SP_USR_APPLY_GRP_AGREE_RES;

        $message->params = json_encode([
            'group_id' => $request->group_id,
            'user_id' => $request->user_id,
        ]);
        $message->user_id = $request->user_id;

        $receiver_user = User::find($request->user_id);
        // 微信模板消息所需参数
        $message->openid = $receiver_user['openid'];
        $message->tpl = TPL_APPLY_AUDIT_RESULT;
        $message->keyword1 = '申请加入群组「' . $group['name'] . '」';
        $message->keyword2 = "审核通过";

        MessageController::create($message, true);


        return ResponseJson();
    }

    //  拒绝用户加入群组申请
    public function refusedjoin(GroupRequest $request)
    {
//        var_dump($request->id);         // 群主ID 2
//        var_dump($request->group_id);   // 群组ID 6
//        var_dump($request->user_id);    // 申请的用户的ID 3
//        var_dump($request->msg_id);     // 申请消息的ID 2

        // 检查当前操作用户是否是这个群组的创建人
        $check_user_create = GroupUser::where([
            ['user_id', session()->get('user.id')],
            ['role_id', 5],
            ['group_id', $request->group_id],
        ])->first();
        if ($check_user_create == null) {
            return ResponseJson([], '您无权进行此操作!');
        }

        $group = Group::where('id', $request->group_id)->first();

        // 修改原来信息状态
        $old_msg = Message::where('id', $request->msg_id)->first();
//        $old_msg->title = '已拒绝' . $old_msg->title;
        $old_msg->content = '已拒绝' . substr($old_msg->content, 0, strpos($old_msg->content, ','));
//        $old_msg->url = "/organization";
        $old_msg->status = 1;
        $old_msg->save();


        // 发送消息给申请用户
        // 创建推送消息
        $message = (object)[];
        $message->title = '申请加入' . $group['name'] . '结果返回';
        $message->content = $group['name'] . '群主拒绝了您的申请';
        $message->type = MSG_TP_USR;
        $message->subtype = MSG_SP_USR_APPLY_GRP_REJECT_RES;

        $message->params = json_encode([
            'group_id' => $request->group_id,
            'user_id' => $request->user_id
        ]);
        $message->user_id = $request->user_id;

        // 微信模板消息所需参数
        $receiver_user = User::find($request->user_id);
        $message->openid = $receiver_user['openid'];
        $message->tpl = TPL_APPLY_AUDIT_RESULT;
        $message->keyword1 = '申请加入群组「' . $group['name'] . '」';
        $message->keyword2 = "审核不通过";

        MessageController::create($message, true);
    }

    //  群主更改群名
    public function editGroupName(GroupRequest $request){
        Group::where('id', $request->group_id)->update(['name' => $request->name]);
        return ResponseJson();
    }

}
