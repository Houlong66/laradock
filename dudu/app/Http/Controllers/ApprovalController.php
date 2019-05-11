<?php

namespace App\Http\Controllers;

use App\Models\AskUser;
use App\Models\NotificationUser;
use App\Models\TaskUser;
use Log;
use App\Models\Org;
use App\Models\User;
use App\Models\Role;
use App\Models\Dept;
use App\Models\Group;
use App\Models\Message;
use App\Http\Controllers\MessageController;
// use App\Http\Requests\ApprovalRequest;
use Illuminate\Http\Request;

class ApprovalController extends Controller
{

    // 同意加入机构申请
    public function grantJoinOrgWithLogin(Request $request, Message $message) {
        $user_id = session()->get('user.id');

        $user = User::findOrFail($user_id);

        if (!($message->type == MSG_TP_USR && $message->subtype == MSG_SP_USR_JOIN_REQ)) {
            return ResponseJson([], '该消息类型不为「申请加入新机构」');
        }


        $params = json_decode($message->params, true);

        $org = Org::findOrFail($params['org_id']);



        $apply_user = User::findOrFail($params['user_id']);
        $dept = Dept::find($params['dept_id']);
        $dept_name = $dept != null ? '-' . $dept->name : '';
        $group = $params['group_id'] != null ? Group::find($params['group_id']) : null;


        if ($dept == null && $apply_user->isInOrg($params['org_id'])) {
            return ResponseJson([], '用户已在该机构内');
        }

        if ($dept != null) {
            if($dept->org->id != $org->id) {
                return ResponseJson([], '选择的部门不属于选择的机构');
            }

            if ($apply_user->isInOrg($params['org_id'])) {
                return ResponseJson([], '用户已在该机构内');
            }

            if ($apply_user->isInOrg($params['org_id']) && $apply_user->isInDept($params['dept_id'])) {
                return ResponseJson([], '用户已在该部门内');
            }
        }


        $msg = Message::find($message->id);

        $all_msg = Message::where([
            ['send_id',$msg['send_id']]
            ,['type',$msg['type']]
            ,['subtype',$msg['subtype']]
        ])->get();


        foreach ($all_msg as $k => $v){
            $all_msg[$k]->status = MSG_ST_READ;
            $str = explode('，', $all_msg[$k]->content)[0];
            $all_msg[$k]->content = '管理员'.$user['name'].'同意了' .$str;
            $all_msg[$k]->url = preg_replace('/(?<=[=]).*(?=[&])/', $str, $all_msg[$k]->url);
            $all_msg[$k]->save();
        }

        $msg->status = MSG_ST_READ;

        $msg->save();


        // 如果用户还没有机构,is_default为1，否则为0
        $is_default = $apply_user->orgs->count() == 0 ? 1 : 0;

        $apply_user->orgs()
            ->syncWithoutDetaching([
                $org->id => [
                    'role_id' => $params['role_id'],
                    'is_default' => $is_default,
                    'agreed_user' => $user->id,
                    'agreed_time' =>  date('Y-m-d H:i:s')
                ]
            ]);

        if ($dept != null) {
            $apply_user->depts()->syncWithoutDetaching($dept->id);
        }

        $content = "申请已通过审核，您成功加入「{$org->name}{$dept_name}」";

        if ($group != null) {
            $apply_user->groups()
            ->syncWithoutDetaching([
                $group->id => [
                    'role_id' => ROLE_GRP_SIGN
                ]
            ]);
            $content = $content . "「{$group->name}」，点击查看详情";
        } else {
            $content = $content . "，点击查看详情";
        }
        // 默认加入“所有人”工作群... todo
        $default_group = Group::where([
            ['org_id', $org->id],
            ['name', "所有人"],
        ])->first();

        // 如果有要加入的工作群组且不是“所有人群组”
        if($default_group && $group !== $default_group) {
            $apply_user->groups()
            ->syncWithoutDetaching([
                $default_group->id => [
                    'role_id' => ROLE_GRP_SIGN
                ]
            ]);
        }



        $message = (object)[
            'title'   => '申请加入机构成功',
            'content' => $content,
            'status'  => MSG_ST_UNREAD,
            'type'    => MSG_TP_USR,
            'subtype' => MSG_SP_USR_JOIN_RES,
            'params'  => json_encode([
                'id' => $user_id,
                'org_id' => $params['org_id']
            ]),
            'user_id' => $apply_user->id,
        ];

        // 微信模板消息所需参数
        $message->openid = $apply_user->openid;
        $message->tpl = TPL_APPLY_AUDIT_RESULT;
        $message->keyword1 = "申请加入机构";
        $message->keyword2 = "审核通过";
        MessageController::create($message, true);


        //

        return ResponseJson($message);
    }

    // 拒绝加入机构申请
    public function rejectJoinOrgWithLogin(Request $request, Message $message) {
        $user_id = session()->get('user.id');
        $user = User::findOrFail($user_id);

        if (!($user->id == ROLE_SYS_SUPER || $user->id == ROLE_SYS_SYSTEM)) {
            return ResponseJson([], '没有操作权限！');
        }

        if (!($message->type == MSG_TP_USR && $message->subtype == MSG_SP_USR_JOIN_REQ)) {
            return ResponseJson([], '该消息类型不为「申请加入新机构」');
        }

        $msg = Message::find($message->id);
        $msg->status = MSG_ST_READ;
        $msg->save();

        $params = json_decode($message->params, true);
        $org = Org::findOrFail($params['org_id']);
        $apply_user = User::findOrFail($params['user_id']);
        $dept = Dept::find($params['dept_id']);
        $dept_name = $dept != null ? '-' . $dept->name : '';

        if ($dept != null && $dept->org->id != $org->id) {
            return ResponseJson([], '选择的部门不属于选择的机构');
        }

        $message = (object)[
            'title'   => '申请加入机构失败',
            'content' => "您加入「{$org->name}{$dept_name}」的申请未通过审核，点击查看详情",
            'status'  => MSG_ST_UNREAD,
            'type'    => MSG_TP_USR,
            'subtype' => MSG_SP_USR_JOIN_RES,
            'params'  => json_encode([]),
            'user_id' => $apply_user->id,
        ];

        // Message todo
        MessageController::create($message);
        return ResponseJson($message);
    }

    // 同意注册机构申请
    public function grantSignOrgWithLogin(Request $request, $org_id) {
        $user_id = session()->get('user.id');
        $user = User::findOrFail($user_id);

        if (!($user->id == ROLE_SYS_SUPER || $user->id == ROLE_SYS_SYSTEM)) {
            return ResponseJson([], '没有操作权限！');
        }

        $org = Org::find($org_id);

        if ($org->status != ORG_ST_PENDING) {
            return ResponseJson([], '该机构已被其它管理员审核');
        }

        $org->status = ORG_ST_ENABLED;
        $org->save();

        // $message = Message::create([
        //     'title'   => '申请创建机构成功',
        //     'content' => "申请已通过审核，您成功创建机构「{$org->name}」，点击查看详情",
        //     'status'  => MSG_ST_UNREAD,
        //     'type'    => MSG_TP_ORG,
        //     'subtype' => MSG_SP_ORG_RES,
        //     'params'  => json_encode([]),
        //     'user_id' => $id,
        // ]);

        return ResponseJson();
    }

    // 拒绝注册机构申请
    public function rejectSignOrgWithLogin(Request $request, $org_id) {
        $user_id = session()->get('user.id');
        $user = User::findOrFail($user_id);

        if (!($user->id == ROLE_SYS_SUPER || $user->id == ROLE_SYS_SYSTEM)) {
            return ResponseJson([], '没有操作权限！');
        }

        $org = Org::find($org_id);

        if ($org->status != ORG_ST_PENDING) {
            return ResponseJson([], '该机构已被其它管理员审核');
        }

        $org->status = ORG_ST_DISABLED;
        $org->save();

        // $message = Message::create([
        //     'title'   => '申请创建机构失败',
        //     'content' => "您创建机构「{$org->name}」的申请未通过审核，点击查看详情",
        //     'status'  => MSG_ST_UNREAD,
        //     'type'    => MSG_TP_ORG,
        //     'subtype' => MSG_SP_ORG_RES,
        //     'params'  => json_encode([]),
        //     'user_id' => $id,
        // ]);

        return ResponseJson();
    }

    // 同意邀请加入群组
    public function grantJoinGroupWithLogin(Request $request)
    {
        $user_id = session()->get('user.id');
        $user = User::findOrFail($user_id);



        // $params = json_decode($message->params, true);
        // $invite_user = User::findOrFail($params['user_id']);
        $invite_user = User::findOrFail($request->user_id);


        // if (!($message->type == MSG_TP_USR && $message->subtype == MSG_SP_USR_GRP_REQ)) {
            // return ResponseJson([], '该消息类型不为「邀请加入群组」');
        // }

        // $msg = Message::find($message->id);
        // $msg->status = MSG_ST_READ;
        // $msg->save();

        // $group = Group::findOrFail($params['group_id']);

        $group = Group::findOrFail($request->group_id);

        if ($user->isInGroup($request->group_id)) {
            return ResponseJson([], '你已经在该群组了');
        }

        $group['type'] == 1 ? $default =  ROLE_GRP_SHARED : $default = ROLE_GRP_SIGN;

        $user->groups()
             ->syncWithoutDetaching([
                $group->id => [
                    'role_id' =>  $request->org_id == "" ? $default : $request->org_id
                ]
             ]);

        // 给邀请人发送新消息
        $message = (object)[
            'title'   => '邀请加入群组成功',
            'content' => "「{$user->name}」已接受您的邀请，加入群组「{$group->name}」",
            'status'  => MSG_ST_UNREAD,
            'type'    => MSG_TP_USR,
            'subtype' => MSG_SP_USR_GRP_RES,
            'params'  => json_encode([
                'group_id' => $group->id
            ]),
            'user_id' => $invite_user->id,
        ];
        // Message done
        // 微信模板消息所需参数 Message done
        $message->openid = $invite_user->openid;
        $message->tpl = TPL_MESSAGES_RESULT;
        $message->first = "「{$user->name}」已接受您的邀请，加入群组「{$group->name}」";
        $message->keyword1 = "加入群组通知";
        $message->keyword2 = "加入成功";
        $message->keyword3 = date('Y-m-d H:i:s');
        MessageController::create($message, true);
        return ResponseJson($message);
    }


    // 审核详情
    public function approvaldetails(Request $request){

        $user = User::find(session()->get('user.id'));

        // 拿到ID
        $id = $request->sid;

        $model = null;                      // 模块
        $where_arr = null;                  // where条件数组
        $where_in = null;                   // whereIn条件数组
        $is = null;                         // 识别是哪种类型的参数

        /*
         * 0 ： 通知流转审批
         * 1 ： 任务流转审批
         * 2 ： 任务上报
         * 3 :  请示流转
         * 拿到的结果是已处理过的
         * */

        switch ($request->type){

            case 0:
                $model = NotificationUser::Class;
                $where_arr = [['item_type',2], ["notification_id",$id]];
                $where_in  = [2, 3];
                break;

            case 1:
                $model = TaskUser::Class;
                $where_arr = [['item_type',2], ["task_id",$id]];
                $where_in  = [2, 3];
                break;

            case 2:
                $model = TaskUser::Class;

                if( $request->user_id){

                    if ( $request->dept_id){
                        $where_arr = [['item_type',0], ["task_id",$id],["user_id",$request->user_id],["dept_id",$request->dept_id]];
                    }else{
                        $where_arr = [['item_type',0], ["task_id",$id],["user_id",$request->user_id]];
                    }

                }else{

                    if($request->dept_id){
                        $where_arr = [['item_type',0], ["task_id",$id],["user_id",$user->id],["dept_id",$request->dept_id]];
                    }else{
                        $where_arr = [['item_type',0], ["task_id",$id],["user_id",$user->id]];
                    }
                }

                $where_in  = [4, 3];
                $is = "report" ;
                break;
            case 3:
                // 拿到最终同意的那一条
                $ask =  AskUser::with(['work_send','user'])->where([
                    ['ask_id',$request->sid],
                    ['item_type',0],
                    ['status',1]
                ])->first();




                if (!$ask) return ResponseJson();

                $allmessage[] =$ask;

                $while = 1;

                $tranfres = null;

                while ($while){

                    if (!$tranfres){

                        $ask_list =  AskUser::with(['work_send','user'])->where([
                            ['ask_id',$request->sid],
                            ['item_type',0],
                            ['user_id',$ask->work_send_id]
                        ])->first();

                        if (!$ask_list) {
                            $while = null;
                        }else{
                            $tranfres = $ask_list;
                            $allmessage[] = $tranfres;
                        }
                    }

                    if ($tranfres){

                        $new_tranfres =  AskUser::with(['work_send','user'])->where([
                            ['ask_id',$request->sid],
                            ['item_type',0],
                            ['user_id',$tranfres->work_send_id]
                        ])->first();


                       if ($new_tranfres){
                           $tranfres = $new_tranfres;
                           $allmessage[] = $tranfres;
                       }else{
                           $while = null;
                       }
                    }
                }

                break;

        }

        // 拿到当前任务项所有流转审批记录
        if (isset($request->alltranfres)){
            $where_in = [1,2,3];
        }


        if ($request->type != 3){
            // 拿到相关的信息
            $allmessage = $model::with(['user','send_user'])
                ->where($where_arr)
                ->whereIn('status',$where_in)
                ->orderBy('id','desc')
                ->withTrashed()
                ->get();
        }


        // 识别拼接
        if ($is){

            // 查找到发送人 即审批人
            $send_user =  TaskUser::with(['user'])->where([
                ['item_type',1],
                ['task_id',$allmessage[0]->task_id]
            ])->first();

            foreach ($allmessage as $k => $v){
                $v->is = $is;
                $v->audit_user  = $send_user->user;
            }
        }

        return ResponseJson($allmessage);

    }
}
