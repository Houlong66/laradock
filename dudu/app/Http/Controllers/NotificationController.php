<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use App\Models\TaskUser;
use App\Models\Url;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Notification;
use App\Models\NotificationUser;
use App\Models\User;
use App\Models\Group;
use App\Models\Message;
use App\Models\WorkTransfer;
use Mockery\Matcher\Not;
use App\Http\Controllers\MessageController;
use App\Models\Schedule;
use App\Models\Remind;

class NotificationController extends Controller
{
    public static $workType = 'App\Models\Notification';

    /**
     * 通知列表接口
     * @return \json
     */
    public function show()
    {
        // 返回数据数组
        $data = [];

        // 用户id
        $user_id = session()->get('user.id');
        // 用户的通知集合
        $user = User::with([
            'notifications' => function ($q) {
                $q->orderBy('send_time', 'desc');
            }
        ])->find($user_id);

        // 构造每个通知项集合的数据项
        foreach ($user->notifications as $i => $notification) {
            // 过滤审核项
            if ($notification->pivot->item_type === 2 || ($notification->pivot->item_type === 0 && $notification->pivot->status === 0)) {
                continue;
            }

            // 通知收到人数
            $n_receive_num = Notification::find($notification->id)->notification_items()
                ->where([
                    ['item_type', 0],
                ])->count();
            // 通知查看人数
            $n_read_num = Notification::find($notification->id)->notification_items()
                ->where([
                    ['item_type', 0],
                    ['status', '=', 2],
                ])->count();
            // 我发送的通知，通知状态值
            $self_send_status = $n_read_num === $n_receive_num ? 2 : 1; // 通知未读状态值为1，已读状态值为2

            // 构建返回数据
            $obj = array(
                // 通知id
                'notification_id' => $notification->id,
                // 通知标题
                'title' => $notification->title,
                'send_time' => $notification->send_time,
                'n_receive_num' => $n_receive_num,
                'n_read_num' => $n_read_num,
                // 通知的审核状态
                'audit_status' => $notification->pivot->item_type === 1 ? $notification->pivot->status : null,
                // 通知状态
                'status' => $notification->pivot->item_type === 1 ? $self_send_status : $notification->pivot->status,
                // 通知重要级
                'important' => $notification->significance,
                // 是否为"我发送的"
                'self_send' => $notification->pivot->item_type,
            );

            // 通知项插入返回数据数组
            $data[] = $obj;
        }

        return ResponseJson($data);
    }

    /**
     * 创建通知
     * @param Request $request
     * @return \json
     */
    public function store(Request $request)
    {
        // 用户创建者id
        $creator_id = session()->get('user.id');
        $user = User::findOrFail($creator_id);
        // 判断是否有权限创建任务
        $group = $user->groups($request->org_id)->where('group_id', $request->group_id)->first();
        if ($group == null || ($group->pivot->role_id !== ROLE_GRP_CREATE && $group->pivot->role_id !== ROLE_GRP_ASSIGN)) {
            return ResponseJson([], "没有权限创建通知");
        }
        // 是否需要流转审批, 0不需要,1需要
        $audit = (int)$request->if_audit;

        $notification = Notification::updateOrCreate(
            ['id' => $request->notification_id],
            [
                'org_id' => $request->org_id,
                'group_id' => $request->group_id,
                'title' => $request->title,
                'desc' => $request->desc,
                'significance' => $request->significance,
                'send_time' => $audit === 0 ? date('Y-m-d H:i:s') : null
            ]
        );

        // 软删除该任务下的所有关联（包括发送者项和流转审批者项）
        if ((int)$request->notification_id !== 0) {
            NotificationUser::where('notification_id', $request->notification_id)->delete();
        }

        // 关联附件
        $common = new CommonController();
        $common->attachAttachment($notification->id, $request->notification_id, $request->attachment, 0,
            'notification');

        // 通知创建者添加中间关联数据
        $notification->users()->attach($creator_id, [
            'item_type' => 1,
            'status' => $audit === 0 ? 0 : 1,
        ]);

        // 获取要接收通知的用户列表
        $send_users_list = [];
        // 获取需要发送的用户id
        $users_id_list = explode(",", $request->send_to_objs);
        $users = User::whereIn('id', $users_id_list)->get();

        // 构建统一的数据结构
        foreach ($users as $user) {
            $temp = [
                'user_id' => $user->id,
                'openid' => $user->openid,
            ];
            array_push($send_users_list, $temp);
        }

        $significance = '重要级别（普通）';
        switch ((int)$request->significance) {
            case 1:
                $significance = '重要级别（重要）';
                break;
            case 2:
                $significance = '重要级别（非常重要）';
                break;
        }
        // 通知接收者添加中间关联数据
        foreach ($send_users_list as $user) {
            $notification->users()->attach($user['user_id'], [
                'item_type' => 0,
                'status' => $audit === 0 ? 1 : 0, // 根据是否需要流转审批而定
            ]);

            // 添加消息提醒
            if ($audit === 0) {  // 无需流转审核的才发送
                $message = (object)[];
                $message->title = '新通知提醒';
                $message->content = '您有新的通知，请及时查看';
                $message->type = MSG_TP_NTC;
                $message->subtype = MSG_SP_NTC_CA_NEW;
                // $message->params = "{'id':".$notification->id."}";
                $message->params = json_encode([
                    'id' => $notification->id
                ]);
                $message->user_id = $user['user_id'];

                // 微信模板消息所需参数
                $message->openid = $user['openid'];
                $message->tpl = TPL_WORK_SEND;
                $message->keyword1 = "{$significance}";
                $message->keyword2 = '通知';
                $message->keyword3 = "{$request->title}";
                MessageController::create($message, true);
            }
        }

        // 审批后重新创建日程
        if ((int)$request->notification_id !== 0) {
            Schedule::where('notification_id', $request->notification_id)->delete();
        }


        // 为接受者创建日程
        if ($request->if_schedule == 1) {
            // 判断是否给通知创建人自己创建日程
            if ($request->self_schedule == true) {
                $send_users_list[] = array(
                    'user_id' => $creator_id,
                );
            }

            foreach ($send_users_list as $user) {
                $schedule = new Schedule;
                $schedule->creater_id = $creator_id;
                $schedule->user_id = $user['user_id'];
                $schedule->name = $request->name;
                $schedule->comment = $request->comment;
                $schedule->type = $request->type;
//                $schedule->remind_at = $request->remind_at;
                $schedule->public = 0;
                $schedule->notification_id = $notification->id;
                // 日程起止时间
                if ($request->type == 1) {
                    $schedule->start_at = $request->start_at;
                    $schedule->end_at = $request->end_at;
                }
                if ($request->if_audit == 1) {
                    $schedule->is_active = 0;
                }
                $schedule->save();
                // 创建日程提醒
                $reminds = explode(',', $request->remind_at);
                foreach ($reminds as $remind_at) {
                    $remind = new Remind;
                    $remind->remind_at = $remind_at;
                    $remind->schedule_id = $schedule->id;
                    $remind->save();
                }
            }
        }


        $res = [
            'text' => '通知创建成功',
            'id' => $notification->id,
        ];
        return ResponseJson($res);
    }


    // 办结,废止
    public  function setnoticestatus(Request $request){

        // 办结
        if ($request->type == 0){

            $res =  NotificationUser::where([
                ["notification_id",$request->notice_id],
                ["item_type",0],
                ["status","<>",2]
            ])->get();

            if (count($res) < 1){
                return ResponseJson([],"任务已完成,请不要重复点击");
            }

            foreach ($res as $v){
                $v->status = 2;
                $v->check_time = date('Y-m-d H:i:s');
                $v->save();
            }

            return ResponseJson();
        }


        // 废止
        NotificationUser::where([
            ["notification_id",$request->notice_id],
        ])->delete();
        // 删除通知项
        Notification::where("id",$request->notice_id)->delete();

        Attachment::where([
            ["works_id",$request->notice_id],
            ["works_type","App\Models\Notification"]
        ])->delete();
        // 删除创建的日程
        Schedule::where("notification_id",$request->notice_id)->delete();
        // 删除url
        Url::where([
            ["works_id",$request->notice_id],
            ["works_type","App\Models\Notification"]
        ])->delete();




        return ResponseJson();
    }


    // 通知详情
    public function detail($notification_id)
    {

        // 检查任务是否已被废除
        $check_notification  = Notification::find($notification_id);

        if (!$check_notification){
            return ResponseJson(["该通知不存在,或已被废除"]);
        }


        // 找到该id的通知
        $org_id = Notification::find($notification_id)->org_id;

        $notification = Notification::with([
            'org', // 获取机构信息
            'group', // 获取群组信息
            'schedules', // 获取关联日程
            'schedules.reminds', // 获取关联日程及提醒
            'notification_items',
            'notification_items.user',
            'notification_items.user.orgs' => function ($q) use ($org_id) {
                $q->where('org_id', $org_id);
            },
            'notification_items.user.depts' => function ($q) use ($org_id) {
                $q->where('org_id', $org_id);
            },
            'notification_items.send_user',
            'msg_boards' => function ($query) {
                $query->where('type', 2);
                $query->orderBy('created_at', 'desc');
            },
            'msg_boards.user' => function ($query) {
                $query->select(['id', 'name', 'avatar']);
            },
            'url'
        ])->find($notification_id);

        // 获取关联附件 todo 待优化，改为只获取用户相关的附件信息
        foreach ($notification->attachments as $attachment) {
            $attachment->total_path = asset($attachment->total_path);
        }

        $notification = $notification->toArray();
        // 若有人部门为空，补全
        foreach ($notification['notification_items'] as $key => $value) {
            if (count($value['user']['depts']) == 0) {
                $notification['notification_items'][$key]['user']['depts'] = [
                    [
                        'name' => '无'
                    ]
                ];
            }
        }

        $remind_at = array();
        if (count($notification['schedules']) !== 0) {
            foreach ($notification['schedules'][0]['reminds'] as $k => $v) {
                $remind_at[] = $v['remind_at'];
            }
            $notification['schedules'][0]['remind_at'] = $remind_at;
        }

        // 判断通知流转审批是否已经被同意
        $iscomplete =  NotificationUser::where([
            ['notification_id',$notification_id],
            ['item_type',1],
            ['status',2]
        ])->first();

        if($iscomplete){
            $notification['complete'] = $iscomplete;
        }

        return ResponseJson($notification);
    }

    /**
     * 通知签收
     * @param Request $request
     * @return \json
     */
    public function sign(Request $request)
    {
        $user_id = session()->get('user.id');

        $update_data = [
            'check_time' => date('Y-m-d H:i:s'),
            'status' => 2, // 未签收->未上报
        ];

        // 更新签收人的item --关联方法
        NotificationUser::where(
            [
                ['notification_id', $request->notification_id],
                ['user_id', $user_id],
                ['item_type', 0],
            ])
            ->update($update_data);


        return ResponseJson('通知签收成功');
    }


    // 通知流转审批
    public function addFlowAuditors(Request $request)
    {
        $user = User::find(session()->get("user.id"));

        $send_to_objs = explode(',', $request->send_to_objs);

        $notification = Notification::find($request->id);

        if (!$notification){
            return ResponseJson(["该任务已被废止"]);
        }

        // 通知创建者
        $notification_user_creator = $notification->users()->where([
            ['item_type', 1]
        ])->first();


        // 判断通知状态是否为待审核
        if ($notification_user_creator->pivot->status !== 1) {
            return ResponseJson([], '通知状态有误，无法添加流转审批人');
        }

        // 是否需要查询消息内容
        $msg = null;
        $msg_params = null;
        if($request->msg_id){
            $msg =  Message::where('id',$request->msg_id)->first();
            $msg_params =  json_decode( $msg->params,true);
        }

        // 微信审批申请的消息
        if ($request->wx_note_text && $request->wx_work_send_id){
            $msg_params['work_send_id'] = $request->wx_work_send_id;
            $msg_params['note_text']  = $request->wx_note_text;
        }


        // 判断流转审批对象数组中的用户，是否已经添加过流转审批记录
        $notification_existing_auditors = $notification->users()
            ->where('item_type', 2)
            ->whereIn('user_id', $send_to_objs)
            ->get();

        // 过滤已存在流转审批记录的用户
        foreach ($notification_existing_auditors as $key => $a) {
            $index = array_search($a->pivot->user_id, $send_to_objs);
            if ($index === 0 || $index !== false) {
                array_splice($send_to_objs, $index, 1);
            }
        }

        // 获取要添加的审批者
        $notification_new_auditors = User::whereIn('id', $send_to_objs)->get();
        // 是否发送微信消息标记
        $wx_msg_flag = $request->if_send_wx_message === 1 ? true : false;
        foreach ($notification_new_auditors as $auditor) {
            // 过滤任务创建者自己
            if ((int)$auditor->id === $notification_user_creator->pivot->user_id) {
                continue;
            }

            // 添加流转审批记录
            $notification->users()->attach($request->id, [
                'item_type' => 2,
                'user_id' => $auditor->id,
                'work_send_id' => $msg_params ? $msg_params['work_send_id'] :  $user->id,
                'note_text' => $request->notetext ? $request->notetext : $msg_params['note_text'] ,
                'status' => 1,
            ]);

            // 添加消息记录
            $message = (object)[];
            $message->title = '新的流转审批提醒';
            $message->content = '您有新的待审批事项，请及时处理';
            $message->type = 2;
            $message->subtype = 2;
            // $message->params = "{'id':".$notification->id."}";
            $message->params = json_encode([
                'id' => $notification->id,
                'work_send_id' => $msg_params ? $msg_params['work_send_id'] :  $user->id,
                'note_text' => $request->notetext ? $request->notetext : $msg_params['note_text'] ,
            ]);
            $message->user_id = $auditor->id;

            // 微信模板消息所需参数
            $message->openid = $auditor->openid;
            $message->tpl = TPL_APPLY_WAIT_AUDIT;
            $message->first = "通知标题: {$notification->title}";
            $message->keyword1 = $notification_user_creator->name;
            $message->keyword2 = '发放通知';
            $message->keyword3 = $notification->created_at->format('Y-m-d H:i:s');
            $message->keyword4 = '待审核';
            MessageController::create($message, $wx_msg_flag);
        }

        return ResponseJson('成功给指定用户发送流转审批项');
    }

    public function flowAudit(Request $request)
    {
        $user_id = session()->get('user.id');

        // 通知项
        $notification = Notification::find($request->notification_id);

        // 发送人的通知项
        $notification_creator = Notification::find($request->notification_id)->users()->where([
            ['item_type', 1],
        ]);
        $notification_creator_user = $notification_creator->first();
        if ($notification_creator_user->pivot->user_id === $user_id) {
            return ResponseJson([], '不可审批自己创建的通知');
        }

        // 流转审批人通知项
        $notification_auditor = Notification::find($request->notification_id)->users()->where([
            ['item_type', 2],
            ['user_id', $user_id],
        ]);
        $notification_auditor_user = $notification_auditor->first();

        if ($notification_creator_user->pivot->status === 1 && $notification_auditor_user->pivot->status === 1) {
            $time = date('Y-m-d H:i:s');

            $update_data = [
                'audit_text' => $request->audit_text,
                'audit_time' => $time,
                'status' => $request->audit_result,
            ];
            // 更新中间表相关项的值 todo updateExistingPivot方法，如何增加条件限制
            NotificationUser::where([
                ['notification_id', $request->notification_id],
                ['item_type', 2],
                ['user_id', $user_id],
            ])->orWhere([
                ['notification_id', $request->notification_id],
                ['item_type', 1],
                ['user_id', $notification_creator_user->pivot->user_id],
            ])->update($update_data);

            // 发送消息通知任务创建者
            $message = (object)[];
            $message->title = '流转审批结果';
            $message->content = '您的通知已被审批，请查看审批结果';
            $message->type = MSG_TP_NTC;
            $message->subtype = MSG_SP_NTC_CA_RESULT;
            // $message->params = "{'id':$request->notification_id,'status':$request->audit_result}";
            $message->params = json_encode([
                'id' => $request->notification_id,
                'status' => $request->audit_result
            ]);
            $message->user_id = $notification_creator_user->pivot->user_id;

            // 微信模板消息所需参数
            $message->openid = $notification_creator_user->openid;
            $message->tpl = TPL_APPLY_AUDIT_RESULT;
            $message->first = "通知标题: {$notification->title}";
            $message->keyword1 = "通知发放申请";
            $message->keyword2 = (int)$request->audit_result === 3 ? '不通过' : '通过';
            MessageController::create($message, true);

            if ((int)$request->audit_result === 2) {
                // 更新通知发送时间
                DB::table('notifications')->where('id', $request->notification_id)->update(['send_time' => $time]);
                // 更新通知接收项的状态为未签收
                NotificationUser::where([
                    ['notification_id', $request->notification_id],
                    ['item_type', 0],
                ])->update(['status' => 1]);

                $notification_user_items = Notification::find($request->notification_id)->users()->where([
                    ['item_type', 0]
                ])->get();

                $significance = '重要级别（普通）';
                switch ((int)$notification->significance) {
                    case 1:
                        $significance = '重要级别（重要）';
                        break;
                    case 2:
                        $significance = '重要级别（非常重要）';
                        break;
                }

                foreach ($notification_user_items as $item) {
                    $message = (object)[];
                    $message->title = '新通知提醒';
                    $message->content = '您有新的通知，请及时处理';
                    $message->type = MSG_TP_NTC;
                    $message->subtype = MSG_SP_NTC_CA_NEW;
                    $message->params = json_encode([
                        'id' => $request->notification_id
                    ]);
                    $message->user_id = $item->pivot->user_id;

                     // 微信模板消息所需参数
                    $message->openid = $item->openid;
                    $message->tpl = TPL_WORK_SEND;
                    $message->keyword1 = "{$significance}";
                    $message->keyword2 = '通知';
                    $message->keyword3 = "{$notification->title}";
                    MessageController::create($message, true);
                }

                Schedule::where('notification_id', $request->notification_id)
                    ->update(['is_active' => 1]);
            }
        } else {
            // 任务流转审批项状态值有误，无法进行审批
            return ResponseJson([], '通知状态有误,无法进行流转审批');
        }

        return ResponseJson('流转审批已完成');
    }

    // 确认转交
    public function transfer(Request $request)
    {
        $from_user_id = session()->get('user.id');
        $user = User::find($from_user_id);

        // 需要传入的三个参数，分别为通知ID、转交目标用户ID、转交文字意见
        $notification_id = $request->work_id;
        $work_item_id = $request->work_item_id;
        $to_user_id = $request->to_user_id;
        $remark = $request->remark;

        if ($from_user_id == $to_user_id) {
            return ResponseJson([], '通知转交的对象不能是自己');
        }

        if (!$user->canTransferNotification($notification_id)) {
            return ResponseJson([], '不属于用户的通知无法进行转交');
        }

        if (!$user->isInTheSameOrgWith($to_user_id)) {
            return ResponseJson([], '通知转交仅限同机构用户内进行');
        }

        if (User::find($to_user_id)->hasNotification($notification_id)) {
            return ResponseJson([], '对方用户已有该通知项');
        }

        // 添加转交记录
        $transfer = new WorkTransfer;
        $transfer->work_type = self::$workType;
        $transfer->work_id = $notification_id;
        $transfer->work_item_id = $work_item_id;
        $transfer->from_user_id = $from_user_id;
        $transfer->to_user_id = $to_user_id;
        $transfer->remark = $remark;
        $transfer->save();

        // 修改原有通知关联的用户到新用户
        NotificationUser::where('notification_id', $notification_id)->where('user_id', $from_user_id)
            ->update(['user_id' => $to_user_id]);

        $message = (object)[];
        $message->title = '新通知提醒';
        $message->content = '您收到一条转交通知，请及时处理';
        $message->type = MSG_TP_NTC;
        $message->subtype = MSG_SP_NTC_CA_NEW;
        $message->params = json_encode([
            'id' => $notification_id
        ]);
        $message->user_id = $to_user_id;

        $notification = Notification::find($notification_id);
        $to_user = User::find($to_user_id);
        // 微信模板消息所需参数
        $message->openid = $to_user['openid'];
        $message->tpl = TPL_WORK_SEND;
        $message->keyword1 = "{$user['name']}转交给您一条通知，点击查看详情";
        $message->keyword2 = '通知';
        $message->keyword3 = "{$notification->title}";
        MessageController::create($message, true);

        return ResponseJson('通知转交成功');
    }

    public function transferHistory(Request $request)
    {
        $user_id = session()->get('user.id');

        // 需要传入的参数：通知ID
        $notification_id = $request->notification_id;

        $curr = WorkTransfer::with(['from_user'])->where([
            ['work_id' , $notification_id],
            ['work_type' , self::$workType],
            ['to_user_id' , $user_id],
        ])->orderBy('id','desc')->first();


        // 迭代找出整个转交历史，到当前用户截止
        // 即：当前用户无法看到该通知的后续转交情况
        $curr_id_arr = [];
        $history = collect([]);
        while ($curr) {
            $curr_id_arr[] = $curr->id;
            $history = $history->merge([$curr]);

            $from_user_id = $curr->from_user_id;

            $curr = WorkTransfer::where([
                'work_id' => $notification_id,
                'work_type' => self::$workType,
                'to_user_id' => $from_user_id
            ])->first();

            if($curr && in_array($curr->id, $curr_id_arr)){
                $curr = null;
            }
        }


        // 过滤中间产生的 null 项
        $history = $history->filter(function ($v, $k) {
            return $v != null;
        });

        return ResponseJson($history);
    }

    // 获取当前通知下所有用户转交记录
    public  function notificationtransfres(Request $request){


        $id = $request->notice_id;



        // 查找到当前项下的所有
        $all_msg = NotificationUser::with([
            'user',
            'worktransfres' =>function ($q){
                $q->where('work_type',self::$workType);
            },
            'worktransfres.from_user',
            'worktransfres.to_user'
        ])->where([
            ['notification_id',$id],
            ['item_type',0]
        ])->get();




        foreach ($all_msg as $k => $v){
            $v->count =  $v->worktransfres->count();
        }


        return ResponseJson($all_msg);

    }



}

