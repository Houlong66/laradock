<?php

namespace App\Http\Controllers;

use App\Models\DeptUser;
use App\Models\Org;
use App\Models\User;
use App\Models\Role;
use App\Models\Dept;
use App\Models\Group;
use App\Models\Message;
use App\Http\Requests\ApplyRequest;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\MessageController;

class ApplyController extends Controller
{
    // 登录用户申请加入新机构/机构内的某个部门
    public function joinOrgWithLogin(ApplyRequest $request, Org $org, Role $role)
    {
        $user_id = session()->get('user.id');
        $user = User::findOrFail($user_id);

        $dept = $request->has('dept_id') ?
            Dept::findOrFail($request->input('dept_id')) : null;
        $group = $request->has('group_id') ?
            Group::findOrFail($request->input('group_id')) : null;

        // 找到所有消息
        $msg =  Message::where([ ['send_id',$user_id],['type',MSG_TP_USR],['subtype',MSG_SP_USR_JOIN_REQ],['status',0] ])->get();

        $msg_arr = [];

        // 循环判断所有消息中存在是申请加入机构的消息时，再让其不通过
        foreach ($msg as $k => $v){
             $reject =  strpos($v->content,"拒绝") ? true : strpos($v->content,"同意") ? true : false  ;
            if (!$reject){
                $msg_arr[] = $k;
            }
        }

        if (count($msg_arr) !== 0) return ResponseJson([],'您已申请加入机构,请耐心等待机构管理员审核!');

        // 业务逻辑验证参数
        $dept_name = $dept != null ? '-' . $dept->name : '';

        if ($role->type != ROLE_SYS) {
            return ResponseJson([], '选择的角色不为系统角色');
        }

        if ($dept != null && $dept->org->id != $org->id) {
            return ResponseJson([], '选择的部门不属于选择的机构');
        }

        if ($user->depts->find($dept->id)) {
            return ResponseJson([], '您已加入该部门！');
        }

        if ($request->has('name')) {
            $user->name = $request->name;
            if(!$request->new_rules){
                $user->sex = $request->sex;
                $user->identity = $request->identity;
                $user->tel = $request->tel;
            }
            $user->save();
        }

        // 在加入机构的同时加入其的机构本部
//        $dept_id =  Dept::where([
//            ['org_id',$org->id],
//            ['name','机构本部']
//        ])->first();


//        if ($dept_id->id !== $request->dept_id){
//            DeptUser::insert(
//                ['dept_id' => $dept_id->id, 'user_id'=>$user_id]
//            );
//        }

        $content = "用户「{$user->name}」申请以「{$role->name}」角色加入「{$org->name}{$dept_name}」";
        if ($group != null) {
            $content = $content . "和「{$group->name}」，请及时处理";
        } else {
            $content = $content . "，请及时处理";
        }

        // 创建消息
        $data = collect();
        $role_ids = [ROLE_SYS_SUPER, ROLE_SYS_SYSTEM];
        $system_users = $org->users($role_ids)->get();

        foreach ($system_users as $system_user) {
            $message = (object)[
                'title'   => '加入机构申请',
                'content' => $content,
                'status'  => MSG_ST_UNREAD,
                'type'    => MSG_TP_USR,
                'subtype' => MSG_SP_USR_JOIN_REQ,
                'send_id'  => $user->id,
                'params'  => json_encode([
                    'user_id' => $user->id,
                    'org_id'  => $org->id,
                    'role_id' => $role->id,
                    'dept_id' => $dept != null ? $dept->id : null,
                    'group_id' => $group != null ? $group->id : null,
                ]),
                'user_id' =>  $system_user->id
            ];
            // 微信模板消息所需参数 Message done
            $message->openid = $system_user->openid;
            $message->tpl = TPL_APPLY_WAIT_AUDIT;
            $message->keyword1 = $user->name;
            $message->keyword2 = "申请加入「{$org->name}」";
            $message->keyword3 = date('Y-m-d H:i:s');
            $message->keyword4 = '待处理';

            MessageController::create($message, true);
            $data->push($message);
        }

        return ResponseJson($data);
    }

    // 登录用户申请注册新机构
    public function signOrgWithLogin(ApplyRequest $request)
    {
        $user_id = session()->get('user.id');
        $user = User::findOrFail($user_id);

        // 创建机构并设置code值（字符串code + 机构 id 拼接）
        $org = Org::create([
            'code' => 'code',
            'password' => 'default',
            'name'   => $request->input('name'),
            'region'   => $request->input('region'),
            'status'  => ORG_ST_PENDING
        ]);

        $org->code = 'code' . $org->id;
        $org->save();

        return ResponseJson();
    }

    // 邀请别人加入群组
    public function inviteJoinGroup(ApplyRequest $request)
    {
        $send_to_objs = explode(',',$request->send_to_objs);
        $group = Group::find($request->group_id);
        $user_id = session()->get('user.id');
        $user = User::findOrFail($user_id);

        // 判断用户是否已加入群组
        if($group->users->whereIn('id', $send_to_objs)->count() != 0){
            return ResponseJson(null, '用户已加入该群组！');
        }

        // 创建消息
        $data = collect();
        $send_to_users = User::whereIn('id', $send_to_objs)->get();
        foreach ($send_to_users as $send_to_user) {
            $message = (object)[
                'title'   => '邀请加入群组',
                'content' =>  $group->type  == 0 ? "用户「{$user->orgs->first()->name}-{$user->name}」邀请你加入群组「{$group->name}」，请及时处理"
                                :"用户「{$user->orgs->first()->name}-{$user->name}」邀请你加入群组「{$group->name}」，请及时处理。（加入日程群组后，您的日程信息将公开给此群组的其他成员）"  ,
                'status'  => MSG_ST_UNREAD,
                'type'    => MSG_TP_USR,
                'subtype' => MSG_SP_USR_GRP_REQ,
                'params'  => json_encode([
                    'group_id' => $group->id,
                    'user_id' => $user->id
                ]),
                'user_id' => $send_to_user->id,
            ];

            // 微信模板消息所需参数 Message done
            $message->openid = $send_to_user->openid;
            $message->tpl = TPL_MESSAGES_RESULT;
            $message->keyword1 = "邀请你加入群组「{$group->name}」";
            $message->keyword2 = "待处理";
            $message->keyword3 = date('Y-m-d H:i:s');

            MessageController::create($message, true);
            $data->push($message);
        }

        return ResponseJson($data);
    }

    /** 邀请别人加入机构，获取邀请信息接口 */
    public function getInviteInfo(ApplyRequest $request, Dept $dept, $inviter_id, $role_id)
    {
        if((int)$role_id===1){
            return ResponseJson([],'非法请求');
        }
        $dept->load('org');
        $dept->role_text = DB::table('roles')->where('id',$role_id)->value('name');
        $inviter = $dept->users()->where('user_id',$inviter_id)->first();

        if(!$inviter){
            return ResponseJson([],'邀请人信息有误');
        }else{
            $dept->inviter = $inviter;
            return ResponseJson($dept);
        }
    }
}
