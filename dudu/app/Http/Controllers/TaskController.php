<?php

namespace App\Http\Controllers;

use App\Models\MergeDeptOrg;
use App\Models\Remind;
use App\Models\Url;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use App\Models\Task;
use App\Models\Notification;
use App\Models\Ask;
use App\Models\User;
use App\Models\TaskUser;
use App\Models\WorkTransfer;
use App\Models\Org;
use App\Models\Dept;
use App\Models\Group;
use App\Models\Message;
use App\Http\Controllers\MessageController;
use App\Models\Attachment;
use EasyWeChat;

class TaskController extends Controller
{
    public static $workType = 'App\Models\Task';

    /**
     * 任务列表接口
     * @return \json
     */
    public function show()
    {
        // 返回数据数组
        $data = [];

        // 用户id
        $user_id = session()->get('user.id');


        // 用户的任务集合
        $user = User::with([
            'tasks' => function ($q) {
                $q->orderBy('tasks.send_time', 'desc');
            },
        ])->find($user_id);

        // 构造每个任务项集合的数据项
        foreach ($user->tasks as $i => $task) {

            // 跳过审核项
            if ($task->pivot->item_type === 2 ||
                (($task->pivot->item_type === 0 || $task->pivot->item_type === 3) && $task->pivot->status === 0)) {
                continue;
            }


            // 构建任务类型文案
            if ($task->pivot->item_type === 1) {
                // 我发送的任务，dept_id字段标示任务是个人还是单位
                $t_type = (int)$task->pivot->dept_id === 0 ? '个人任务' : '单位任务';
            } else {
                // 我接收的任务，dept_id字段记录的是部门的id
                $t_type = (int)$task->pivot->dept_id === 0 ? '个人任务' : '单位任务 (' . Dept::find($task->pivot->dept_id)->name . ')';
            }


            // 计算任务的完成情况
            if ((int)$task->pivot->dept_id === 0) {  // 个人任务
                // 任务收到人数
                $t_receive_num = Task::find($task->id)->task_items()
                    ->where([
                        ['item_type', 0],
                    ])->count();
                // 任务已办结人数
                $t_accomplish_num = Task::find($task->id)->task_items()
                    ->where([
                        ['item_type', 0],
                        ['status', '=', 4],
                    ])->count();
            } else {  // 部门任务
                // 任务收到人数
                $t_receive_num = Task::find($task->id)->task_items()
                    ->where([
                        ['item_type', 0],
                    ])
                    ->distinct()
                    ->count('dept_id');
                // 任务已办结人数
                $t_accomplish_num = Task::find($task->id)->task_items()
                    ->where([
                        ['item_type', 0],
                        ['status', '=', 4],
                    ])
                    ->distinct()
                    ->count('dept_id');
            }


            // 我发送的任务的状态
            $self_send_status = $t_accomplish_num === $t_receive_num ? 4 : 1; // 任务的完成状态值为4，未完成状态为1

            // 构建返回数据
            $obj = array(
                // 任务id
                'task_id' => $task->id,
                // 任务相关部门的id
                'dept_id' => $task->pivot->dept_id,
                // 任务标题
                'title' => $task->title,
                // 任务截止时间
                'deadline' => ($task->pivot->report_deadline) ? $task->pivot->report_deadline : $task->deadline,
                // 任务发送时间
                'send_time' => $task->send_time,
                't_type' => $t_type,
                't_receive_num' => $t_receive_num,
                't_accomplish_num' => $t_accomplish_num,
                // 任务审核状态,我接受的任务则为null
                'audit_status' => $task->pivot->item_type === 1 ? $task->pivot->status : null,
                // 任务状态
                'status' => $task->pivot->item_type === 1 ? $self_send_status : $task->pivot->status,
                // 任务重要级
                'important' => $task->significance,
                // 是否为"我发送的"
                'self_send' => ($task->pivot->item_type === 1) ? 1 : 0,
            );


            // 任务项插入返回数据数组
            $data[] = $obj;
        }


        return ResponseJson($data);
    }

    /**
     * 创建任务
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
            return ResponseJson([], "没有权限创建任务");
        }

        // 是否需要流转审批, 0不需要,1需要
        $audit = (int)$request->if_audit;

        $task = Task::updateOrCreate(
            ['id' => $request->task_id],
            [
                'org_id' => $request->org_id,
                'group_id' => $request->group_id,
                'title' => $request->title,
                'desc' => $request->desc,
                'significance' => $request->significance,
                'deadline' => $request->deadline,
                'if_report_need_attachment' => $request->if_report_need_attachment,
                'if_report_need_autotable' => $request->if_report_need_autotable,
                'send_time' => $audit === 0 ? date('Y-m-d H:i:s') : null
            ]
        );

        // 软删除该任务下的所有关联（包括发送者项和流转审批者项）
        if ((int)$request->task_id !== 0) {
            TaskUser::where('task_id', $request->task_id)->delete();
        }

        // 任务关联附件
        $common = new CommonController();
        $common->attachAttachment($task->id, $request->task_id, $request->attachment, 0, 'task');

        // 任务关联URL


        // 任务创建者添加中间关联数据
        $task->users()->attach($creator_id, [
            'item_type' => 1,
            'dept_id' => $request->if_dept, // 对于创建者，dept_id为0则是个人任务，为1则是部门任务
            'status' => $audit === 0 ? 0 : 1,
        ]);

        // 获取要接收任务的用户列表
        $send_users_list = [];

        if ((int)$request->if_dept === 0) { // 个人任务
            // 获取需要发送的用户id
            $users_id_list = explode(",", $request->send_to_objs);
            $users = User::whereIn('id', $users_id_list)->get();
            // 构建统一的数据结构
            foreach ($users as $user) {
                $temp = [
                    'user_id' => $user->id,
                    'openid' => $user->openid,
                    'dept_id' => 0,
                    'item_type' => 0,
                ];
                array_push($send_users_list, $temp);
            }
        } else {
            // 单位任务
            // 获取要发送的部门名单部门列表
            $depts_list = explode(",", $request->send_to_objs);

//            $send_users_list = $this->getDeptTaskUsersList($depts_list, $request->group_id, $request->org_id, $creator_id);

            // 获取属于部门列表且属于工作群组的用户,过滤创建者,预加载部门信息
            $group_users = Group::with([
                'users' => function ($q) use ($creator_id) {
                    $q->where('user_id', '<>', $creator_id);
                },
                'users.depts' => function ($q) use ($depts_list) {
                    $q->whereIn('dept_id', $depts_list);
                },
            ])
            ->find($request->group_id);


            // 构建统一的数据结构
            foreach ($group_users->users as $user) {
                if ($user->depts->toArray() !== []) {
                    foreach ($user->depts as $dept) {
                        $temp = [
                            'user_id' => $user->id,
                            'openid' => $user->openid,
                            'dept_id' => $dept->id,
                            'item_type' => $user->pivot->role_id === ROLE_GRP_DO ? 3 : 0,
                            // 判断权限，若身份为9的创建item_type为3的监督项
                        ];
                        array_push($send_users_list, $temp);
                    }
                } else {
                    continue;
                }
            }

        }


//        // 往定时表中插入数据
//        $times = $request->reminds;
//
//        var_dump($times);



        $significance = '重要级别（普通）';
        switch ((int)$request->significance) {
            case 1:
                $significance = '重要级别（重要）';
                break;
            case 2:
                $significance = '重要级别（非常重要）';
                break;
        }

        // 任务接收者添加中间关联数据
        foreach ($send_users_list as $user) {
            $task->users()->attach($user['user_id'], [
                'item_type' => $user['item_type'],
                'dept_id' => $user['dept_id'],
                'report_deadline' => $request->deadline,
                'status' => $audit === 0 ? 1 : 0,
            ]);

            // 添加消息提醒
            // 无需流转审核的才发送

            if ($audit === 0) {

                $message = (object)[];
                $message->title = '新任务提醒';
                $message->content = '您有新的任务，请及时处理';
                $message->type = MSG_TP_TSK;
                $message->subtype = MSG_SP_TSK_NEW;

                $message->params = json_encode([
                    'id' => $task->id,
                    'dept_id' => $user['dept_id']
                ]);

                $message->user_id = $user['user_id'];

                // 微信模板消息所需参数
                $message->openid = $user['openid'];
                $message->tpl = TPL_WORK_SEND;
                $message->keyword1 = "{$significance}";
                $message->keyword2 = '任务';
                $message->keyword3 = "{$request->title}";
                MessageController::create($message, true);
            }
        }

        $res = [
            'text' => '任务创建成功',
            'id' => $task->id,
        ];
        return ResponseJson($res);

    }

    // 办结,废止任务
    public function setworkstatus(Request $request){


        // 办结任务
        if ($request->type == 0){

            $task_user =   TaskUser::where([
                ["task_id",$request->task_id],
                ["item_type",0],
                ["status","<>",4]
            ])->get();

            if (count($task_user) < 1){
                return ResponseJson([],"任务已完成,请不要重复点击");
            }

            foreach ($task_user as $v){
                $v->status = 4;
                $v->receiver_id = $v->user_id;
                $v->report_time = $v->report_time == null ?  date('Y-m-d H:i:s') : $v->report_time;
                $v->receive_time = $v->receive_time == null ?  date('Y-m-d H:i:s') : $v->receive_time;
                $v->save();
            }

            return ResponseJson();
        }

        // 废止任务
        TaskUser::where([["task_id",$request->task_id],])->delete();
        // 删除任务项
        Task::where("id",$request->task_id)->delete();

        Attachment::where([
                ["works_id",$request->task_id],
                ["works_type","App\Models\Task"]
        ])->delete();

        // 删除url
        Url::where([
            ["works_id",$request->task_id],
            ["works_type","App\Models\Task"]
        ])->delete();

        return ResponseJson();
    }

    //    任务详情接口
    public function detail($task_id)
    {

        // 判断是否有该任务
        $check_task = Task::find($task_id);

        if (!$check_task){
            return ResponseJson(["该任务不存在,或已被废除"]);
        }

        // 找到该id的任务
        $org_id = Task::find($task_id)->org_id;


        $task = Task::with([
            'org',
            'group',
            'task_items',
            'task_items.dept',
            'task_items.user',
            'task_items.send_user',

            'task_items.user.orgs' => function ($q) use ($org_id) {
                $q->where('org_id', $org_id);
            },

            //根据 机构 id 查到信息 但是有些机构的ID是不一样的
            'task_items.user.depts' => function ($q) use ($org_id) {
                $q->where('org_id', $org_id);
            },

            'msg_boards' => function ($query) {
                $query->where('type', 1);
                $query->orderBy('created_at', 'desc');
            },

            'msg_boards.user' => function ($query) {
                $query->select(['id', 'name', 'avatar']);
            },
            'url',
        ])->find($task_id);



        // 获取关联附件 todo 待优化，改为只获取用户相关的附件信息
        foreach ($task->attachments as $attachment) {
            $attachment->total_path = asset($attachment->total_path);
        }

        $tasks = $task->toArray();

        $task_list = $task['task_items']->toArray();

        foreach ($task_list as $k => $v) {
            // 拿到用户对应的上级机构的ID
            $orgs_id = DB::table('org_user')->where('user_id', $task_list[$k]['user']['id'])->value('org_id');
            $task_list[$k]['org_id'] = $orgs_id;
            if ($task['org_id'] != $task_list[$k]['org_id']) {

                $dept_org_id = DB::table('merge_dept_org')->where('org_id',
                    $task_list[$k]['org_id'])->value('dept_org_id');

                $depts = DB::table('depts')->where('id', $dept_org_id)->get();

                $tasks['task_items'][$k]['user']['depts'] = $depts->toArray();

            }
        }

        // 若有人部门为空，补全
        foreach ($tasks['task_items'] as $key => $value) {
            if (count($value['user']['depts']) == 0) {
                $tasks['task_items'][$key]['user']['depts'] = [
                    [
                        'name' => '无'
                    ]
                ];
            }
        }


        return ResponseJson($tasks);
    }


    /**
     * 任务签收
     * @param Request $request
     * @return \json
     */
    public function sign(Request $request)
    {
        $user_id = session()->get('user.id');

        $update_data = [
            'receiver_id' => $user_id,
            'receive_time' => date('Y-m-d H:i:s'),
            'status' => 2, // 未签收->未上报
        ];
        $task = Task::find($request->task_id);

        // 更新签收人的item --关联方法
        if ($request->dept_id !== 0) {
            // 如果是部门任务，同步更新监督人的item --原生方法待优化 todo
            // 督办人item更新
            // $temp = $task->users()->where([['dept_id',$request->dept_id], ['item_type',3]])->updateExistingPivot($update_data);
            TaskUser::where(
                [
                    ['task_id', $request->task_id],
                    ['dept_id', $request->dept_id],
                    ['item_type', 3],
                ])
                ->update($update_data);
            // 删除部门其他签收成员的item
            TaskUser::where(
                [
                    ['task_id', $request->task_id],
                    ['dept_id', $request->dept_id],
                    ['item_type', 0],
                    ['user_id', '<>', $user_id],
                ])
                ->forceDelete(); // 物理删除，非软删除

            $where = [
                ['task_id', $request->task_id],
                ['dept_id', $request->dept_id],
                ['item_type', 0],
            ];
        } else { // 个人任务
            // $task->users()->where('item_type', 0)->updateExistingPivot($user_id, $update_data);
            $where = [
                ['task_id', $request->task_id],
                ['user_id', $user_id],
                ['item_type', 0],
            ];
        }

        TaskUser::where($where)->update($update_data);

        return ResponseJson('任务签收成功');
    }

    /**
     * 任务上报接口
     * @param Request $request
     * @return \json
     */
    public function report(Request $request)
    {
        $user_id = session()->get('user.id');

        $time = date('Y-m-d H:i:s');
        $update_data = [
            'report_text' => $request->report_text,
            'report_time' => $time,
            'status' => 3 // 状态：已上报
        ];

        $report_user = User::find($user_id);
        $task_user_item = $report_user->tasks()->where([
            ['task_id', $request->task_id],
            ['dept_id', $request->dept_id],
            ['user_id', $user_id],
            ['item_type', 0],
        ])->first();

        // 判断该任务项是否可上报
        if ($task_user_item->pivot->status === 2) { // 当任务项状态为未上报时，可进行上报
            // todo 附件和自动表格
            if ($task_user_item->if_report_need_attachment === 1 || $task_user_item->if_report_need_attachment === 0) {
                // 关联上报附件
                $task_id = $task_user_item->id;
                $common = new CommonController();
                $common->attachAttachment($task_id, $task_id, $request->attachment, $task_user_item->pivot->id, 'task');
            }

            // 向未被软删除的记录中写入上报内容、上报时间并设置状态为已上报
            TaskUser::where(
                [
                    ['task_id', $request->task_id],
                    ['dept_id', $request->dept_id],
                    ['user_id', $user_id],
                    ['item_type', 0],
                ])
                ->orWhere([
                    ['task_id', $request->task_id],
                    ['dept_id', $request->dept_id],
                    ['item_type', 3],
                ])
                ->update($update_data);

            // 给任务发放人添加消息提醒
            $task_creator_user_id = TaskUser::where([
                ['task_id', $request->task_id],
                ['item_type', 1],
            ])->value('user_id');
            $task_creator_user = User::find($task_creator_user_id);

            $message = (object)[];
            $message->title = '任务上报待审批提醒';
            $message->content = '您有新的任务上报，请及时处理';
            $message->type = MSG_TP_TSK;
            $message->subtype = MSG_SP_TSK_PENDING;
            $message->params = json_encode([
                'id' => $request->task_id,
                'report_user_id' => $task_user_item->pivot->user_id,
                'dept_id' => $request->dept_id
            ]);
            $message->user_id = $task_creator_user_id;

            // 微信模板消息所需参数
            $message->openid = $task_creator_user->openid;
            $message->tpl = TPL_APPLY_WAIT_AUDIT;
            $message->first = "任务标题: {$task_user_item->title}";
            $message->keyword1 = $report_user->name;
            $message->keyword2 = '任务上报待审批';
            $message->keyword3 = $time;
            $message->keyword4 = '待审批';
            MessageController::create($message, true);

            return ResponseJson('任务上报成功');
        } else {
            // 状态字段值不对，不允许上报
            $errmsg = '任务项不是“未上报”状态，无法完成上报操作';
            return ResponseJson([], $errmsg);
        }

    }

    public function auditReport(Request $request)
    {
        $task_user_item = User::find($request->report_user_id)->tasks()->where([
            ['task_id', $request->task_id],
            ['dept_id', $request->dept_id],
            ['item_type', 0]
        ])->first();

        // 判断任务项状态是否为已上报待审核
        if ($task_user_item->pivot->status === 3) {
            // 根据是否为部门任务决定审核数据更新操作的查询条件
            if ($task_user_item->pivot->dept_id === 0) {
                $where_query = [
                    ['task_id', $request->task_id],
                    ['user_id', $request->report_user_id],
                    ['dept_id', $request->dept_id],
                ];
            } else {
                $where_query = [
                    ['task_id', $request->task_id],
                    ['dept_id', $request->dept_id],
                ];
            }

            /* A1. 审核结果为通过：直接写入审核结果、审核时间，并设置状态为已完结 */
            if ($request->audit_result == 4) {
                TaskUser::where($where_query)->whereIn('item_type',[0,3])->update([
                    'audit_text' => $request->audit_text,
                    'audit_time' => date('Y-m-d H:i:s'),
                    'status' => 4
                ]);
            }

            /* A2. 审核结果为不通过：写入审核结果、审核时间，针对该行记录复制并重设新截止时间，软删除原记录 */
            if ($request->audit_result == 2) {
                $src = $task_user_item->pivot;

                // 向原记录写入审核结果、审核时间
                $src->update([
                    'audit_text' => $request->audit_text,
                    'audit_time' => date('Y-m-d H:i:s')
                ]);

                // 将数据复制到新建记录
                $rep = TaskUser::create([
                    'item_type' => $src->item_type,
                    'task_id' => $src->task_id,
                    'dept_id' => $src->dept_id,
                    'user_id' => $src->user_id,
                    'receiver_id' => $src->receiver_id,
                    'receive_time' => $src->receive_time,
                    'report_text' => $src->report_text,

                    // 复制上一条不通过的审核结果信息，便于再次上报时的参考
                    // 如果不这样复制的话就需要在 show 接口里面判断状态然后去获取前一条里面的审核结果数据了
                    'audit_text' => "暂无",
                    'audit_time' => NULL,

                    // 重设截止时间，并设置状态为未上传
                    'report_deadline' => $request->dead_line,
                    'status' => 2
                ]);


                // 将上传附件关联的中间表 ID 更新为复制出来的数据的 ID
                Attachment::where('works_item_id', $src->id)->update(['works_item_id' => $rep->id]);

                // 软删除原数据行
                TaskUser::where('id', $src->id)->delete();
            }

            /* B. 剩余通知操作 */

            // 添加消息提醒
            $message = (object)[];
            $message->title = '审批结果';
            $message->content = '您上报的任务已被审批，请点击查看审批结果';
            $message->type = MSG_TP_TSK;
            $message->subtype = MSG_SP_TSK_RESULT;
            $message->params = json_encode([
                'id' => $request->task_id,
                'dept_id' => $request->dept_id
            ]);
            $message->user_id = $request->report_user_id;

            // 微信模板消息所需参数
            $report_user = User::find($request->report_user_id);
            $message->openid = $report_user->openid;
            $message->tpl = TPL_APPLY_AUDIT_RESULT;
            $message->first = "任务标题: {$task_user_item->title}";
            $message->keyword1 = "任务上报审核";
            $message->keyword2 = (int)$request->audit_result === 4 ? '通过' : '不通过';
            MessageController::create($message, true);

            return ResponseJson('任务上报,审批成功');
        } else {
            // 状态字段值不对，不允许审批
            $errmsg = '任务项不是“已上报”状态，无法完成审批操作';
            return ResponseJson([], $errmsg);
        }

    }


    // 流转审批
    public function addFlowAuditors(Request $request)
    {

        $user = User::find(session()->get("user.id"));

        $send_to_objs = explode(',', $request->send_to_objs);

        $task = Task::find($request->id);


        // 判断任务是否已经被删除
        if (!$task){
           return ResponseJson(["该任务已被废止"]);
        }

        // 任务创建者
        $task_user_creator = $task->users()->where([
            ['item_type', 1]
        ])->first();


        // 判断任务状态是否为待审核
        if ($task_user_creator->pivot->status !== 1) {
            return ResponseJson([], '任务状态有误，无法添加流转审批人');
        }

        // 判断流转审批对象数组中的用户，是否已经添加过流转审批记录
        $task_existing_auditors = $task->users()
            ->where('item_type', 2)
            ->whereIn('user_id', $send_to_objs)
            ->get();

        // 过滤已存在流转审批记录的用户
        foreach ($task_existing_auditors as $key => $a) {
            $index = array_search($a->pivot->user_id, $send_to_objs);
            if ($index === 0 || $index !== false) {
                array_splice($send_to_objs, $index, 1);
            }
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

        // 获取要添加的审批者
        $task_new_auditors = User::whereIn('id', $send_to_objs)->get();

        // 是否发送微信消息标记
        $wx_msg_flag = $request->if_send_wx_message === 1 ? true : false;

        foreach ($task_new_auditors as $auditor) {

            // 过滤任务创建者自己
            if ((int)$auditor->id === $task_user_creator->pivot->user_id) {
                continue;
            }

            // 添加流转审批记录
            $task->users()->attach($request->id, [
                'item_type' => 2,
                'dept_id' => $task_user_creator->pivot->dept_id,
                'user_id' => $auditor->id,

                'work_send_id' => $msg_params ? $msg_params['work_send_id'] :  $user->id,
                'note_text' => $request->notetext ? $request->notetext : $msg_params['note_text'] ,

                'status' => 1,
            ]);

            // 添加消息记录
            $message = (object)[];
            $message->title = '新的流转审批提醒';
            $message->content = '您有新的待审批事项，请及时处理';
            $message->type = MSG_TP_TSK;
            $message->subtype = MSG_SP_TSK_CA_PENDING;
            $message->params = json_encode([
                'id' => $task->id,
                'work_send_id' => $msg_params ? $msg_params['work_send_id'] :  $user->id,
                'note_text' => $request->notetext ? $request->notetext : $msg_params['note_text'] ,
            ]);
            $message->user_id = $auditor->id;
            // 微信模板消息所需参数
            $message->openid = $auditor->openid;
            $message->tpl = TPL_APPLY_WAIT_AUDIT;
            $message->first = "任务标题: {$task->title}";
            $message->keyword1 = $task_user_creator->name;
            $message->keyword2 = '发放任务';
            $message->keyword3 = $task->created_at->format('Y-m-d H:i:s');
            $message->keyword4 = '待审核';

            MessageController::create($message, $wx_msg_flag);

        }
        return ResponseJson($msg,'','成功给指定用户发送流转审批项');
    }

    // 拒绝流转审批
    public function flowAudit(Request $request)
    {
        $user_id = session()->get('user.id');

        // 任务项
        $task = Task::find($request->task_id);

        // 发送人的任务项
        $task_creator = Task::find($request->task_id)->users()->where([
            ['item_type', 1],
        ]);

        $task_creator_user = $task_creator->first();

        if ($task_creator_user->pivot->user_id === $user_id) {
            return ResponseJson([], '不可审批自己创建的任务');
        }

        // 流转审批人任务项
        $task_auditor = Task::find($request->task_id)->users()->where([
            ['item_type', 2],
            ['user_id', $user_id],
        ]);

        $task_auditor_user = $task_auditor->first();

        if ($task_creator_user->pivot->status === 1 && $task_auditor_user->pivot->status === 1) {
            $time = date('Y-m-d H:i:s');


            $update_data = [
                'audit_text' => $request->audit_text,
                'audit_time' => $time,
                'status' => $request->audit_result,
            ];

            // 更新中间表相关项的值 todo updateExistingPivot方法，如何增加条件限制
            TaskUser::where([
                ['task_id', $request->task_id],
                ['item_type', 2],
                ['user_id', $user_id],
            ])->orWhere([
                ['task_id', $request->task_id],
                ['item_type', 1],
                ['user_id', $task_creator_user->pivot->user_id],
            ])->update($update_data);

            // 发送消息通知任务创建者
            $message = (object)[];
            $message->title = '流转审批结果';
            $message->content = '您的任务已被审批，请查看审批结果';
            $message->type = MSG_TP_TSK;
            $message->subtype = MSG_SP_TSK_CA_RESULT;
            // $message->params = "{'id':$request->task_id,'status':$request->audit_result}";
            $message->params = json_encode([
                'id' => $request->task_id,
                'status' => $request->audit_result
            ]);
            $message->user_id = $task_creator_user->pivot->user_id;
            // 微信模板消息所需参数
            $message->openid = $task_creator_user->openid;
            $message->tpl = TPL_APPLY_AUDIT_RESULT;
            $message->first = "任务标题: {$task->title}";
            $message->keyword1 = "任务发放申请";
            $message->keyword2 = (int)$request->audit_result === 3 ? '不通过' : '通过';
            MessageController::create($message, true);

            if ((int)$request->audit_result === 2) {
                // 更新任务发送时间
                DB::table('tasks')->where('id', $request->task_id)->update(['send_time' => $time]);
                // 更新任务接收项的状态为未签收
                TaskUser::where([
                    ['task_id', $request->task_id],
                ])->whereIn('item_type', [0, 3])->update(['status' => 1]);

                $task_user_items = Task::find($request->task_id)->users()->where([
                    ['item_type', 0]
                ])
                    ->get();

                $significance = '重要级别（普通）';
                switch ((int)$request->significance) {
                    case 1:
                        $significance = '重要级别（重要）';
                        break;
                    case 2:
                        $significance = '重要级别（非常重要）';
                        break;
                }

                foreach ($task_user_items as $item) {
                    $message = (object)[];
                    $message->title = '新任务提醒';
                    $message->content = '您有新的任务，请及时处理';
                    $message->type = MSG_TP_TSK;
                    $message->subtype = MSG_SP_TSK_NEW;
                    $message->params = json_encode([
                        'id' => $item->pivot->task_id,
                        'dept_id' => $item->pivot->dept_id
                    ]);
                    $message->user_id = $item->pivot->user_id;
                    // 微信模板消息所需参数
                    $message->openid = $item->openid;
                    $message->tpl = TPL_WORK_SEND;
                    $message->keyword1 = "{$significance}";
                    $message->keyword2 = '任务';
                    $message->keyword3 = "{$task->title}";
                    MessageController::create($message, true);
                }
            }
        } else {
            // 任务流转审批项状态值有误，无法进行审批
            return ResponseJson([], '任务状态有误,无法进行流转审批');
        }

        return ResponseJson('流转审批已完成');
    }

    public function transfer(Request $request)
    {
        $from_user_id = session()->get('user.id');
        $user = User::find($from_user_id);

        // 需要传入的四个参数，分别为任务ID、任务部门id、转交目标用户ID、转交文字意见
        $task_id = $request->work_id;
        $work_item_id = $request->work_item_id;
        $dept_id = $request->dept_id;
        $to_user_id = $request->to_user_id;
        $remark = $request->remark;


        if ($from_user_id == $to_user_id) {
            return ResponseJson([], '任务转交的对象不能是自己');
        }

        if (!$user->canTransferTask($task_id, $dept_id)) {
            return ResponseJson([], '不属于用户的任务或已被签收的任务无法进行转交');
        }

        if (!$user->isInTheSameOrgWith($to_user_id)) {
            return ResponseJson([], '任务转交仅限同机构用户内进行');
        }

        $to_user = User::find($to_user_id);
        $res_text = '';
        $can_not_transfer = false;
        if ($to_user->hasTask($task_id, $dept_id)) {
            switch ($to_user->taskRole($task_id, $dept_id)) {
                case 1:
                    $res_text = '对方是任务发放人，不可转交';
                    $can_not_transfer = true;
                    break;
                case 2:
                    $res_text = '对方是任务发放审核人，不可转交';
                    $can_not_transfer = true;
                    break;
//                case 3:
//                    $res_text = '对方是部门/单位领导， 不可转交';
//                    break;
                case 0:
                    $res_text = '对方已收到该任务项 不可转交';
                    $can_not_transfer = true;
                    break;
            }

            // 判断是否能转交
            if ($can_not_transfer) {
                return ResponseJson([], $res_text);
            } else {
                // 删除相应的 task_user 记录
                TaskUser::where([
                    ['task_id', $task_id],
                    ['dept_id', $dept_id],
                    ['user_id', $to_user_id],
                ])->delete();
            }
        }


        // 添加转交记录
        $transfer = new WorkTransfer;
        $transfer->work_type = self::$workType;
        $transfer->work_id = $task_id;
        $transfer->work_item_id = $work_item_id;
        $transfer->from_user_id = $from_user_id;
        $transfer->to_user_id = $to_user_id;
        $transfer->remark = $remark;
        $transfer->save();

        // 修改原有任务关联的用户到新用户
        $task_user = TaskUser::where([
            ['task_id', $task_id],
            ['dept_id', $dept_id],
        ])->where('user_id', $from_user_id)
            ->update(['user_id' => $to_user_id]);

        // 给接收人添加消息提醒
        $message = (object)[];
        $message->title = '新任务提醒';
        $message->content = '您收到一条转交任务，请及时处理';
        $message->type = MSG_TP_TSK;
        $message->subtype = MSG_SP_TSK_NEW;
        // $message->params = "{'id':".$task->id.",'dept_id':".$user['dept_id']."}";
        $message->params = json_encode([
            'id' => $task_id,
            'dept_id' => $dept_id
        ]);
        $message->user_id = $to_user_id;

        $task = Task::find($task_id);
        // 微信模板消息所需参数
        $message->openid = $to_user['openid'];
        $message->tpl = TPL_WORK_SEND;
        $message->keyword1 = "{$user['name']}转交给您一项任务，点击查看详情";
        $message->keyword2 = '任务';
        $message->keyword3 = "{$task->title}";
        MessageController::create($message, true);

        return ResponseJson('任务转交成功');
    }

    // 这个接口其实用 GET 请求会更合适一点，不过考虑到现在的接口并不是 RESTful 风格的
    // 加上方便前端的调用（和上面那个接口格式差不多），就暂时这样用 POST 了
    public function transferHistory(Request $request)
    {
        $user_id = session()->get('user.id');

        // 需要传入的参数：任务ID
        $task_id = $request->task_id;


        $curr = WorkTransfer::with(['from_user'])->where([
            ['work_id' , $task_id],
            ['work_type' , self::$workType],
            ['to_user_id' , $user_id],
        ])->orderBy('id','desc')->first();


        // 迭代找出整个转交历史，到当前用户截止
        // 即：当前用户无法看到该任务的后续转交情况
        $curr_id_arr = [];
        $history = collect([]);

        while ($curr) {
            $curr_id_arr[] = $curr->id;
            $history = $history->merge([$curr]);

            $from_user_id = $curr->from_user_id;

            $curr = WorkTransfer::where([
                'work_id' => $task_id,
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


    // 获取部门任务群组中的所有用户
    public function getDeptTaskUsersList($dept_id_list, $group_id, $org_id, $creator_id)
    {
        // 获取部门所有成员，包括子机构成员
        $users = Group::find($group_id)->users()->with(['depts', 'orgs'])->get();
        $sub_depts = $this->getSubDeptByOrg($org_id);
    }

    // 查找任务数量
    public function checkwork(Request $request){

        $start = $request->start_time ." ". "00:00:00";
        $end   = $request->end_time ." " . "24:00:00";

        // 先查找到任务里面想对应的ＩＤ
        $task = Task::whereDate('send_time', '>=', $request->start_time)
                     ->whereDate('send_time','<=', $request->end_time)
                     ->whereIn('group_id', $request->groups_list)
                     ->get();

        $task_id_arr = [];

        foreach ($task as $k => $v){
            $task_id_arr[] = $v->id;
        }

        $if_dept = null;
        $request->row === "个人" ?   $if_dept = false :    $if_dept = true ;


         $all_task_user = TaskUser::with(['user','dept'])->whereIn('task_id',$task_id_arr)->where([
             ['item_type', 0],
             ['dept_id', $if_dept ? "<>" : "=",0]
         ])->get();

        $check_list = [];

        $user_all_list = [];

        foreach ($all_task_user as $k => $v) {

            $id = null;
            !$if_dept ?  $id = $v->user_id : $id = $v->dept_id;

            // 检查是否存在了这个数组中
           $check =  in_array($id,$check_list);


            // 合并用户
           if (!$check){
               $check_list[] = $id;
               $user_all_list[$id] = $v->toArray();
               // 接收的任务数
               $user_all_list[$id]['接收任务数'] = 1;
           }else{
               // 接收的任务数
               $user_all_list[$id]['接收任务数']++;
           }

            // 状态不为4的时候，为未完成任务;
            if ($v->status != 4){
                !isset($user_all_list[$id]['未完成任务数']) ? $user_all_list[$id]['未完成任务数'] = 1 : $user_all_list[$id]['未完成任务数']++;
            }

            // 状态为4的时候，为已完成任务;
            if ($v->status == 4 ){
                !isset($user_all_list[$id]['已完成任务数']) ?$user_all_list[$id]['已完成任务数'] = 1 : $user_all_list[$id]['已完成任务数']++;
            }

            // 在有限制时间的情况下
            if ($v->report_deadline){
                // 限制时间小于结束时间，
                if ($v->report_deadline < $end){
                    // 状态为4，逾期完成
                    if ($v->status == 4){
                        !isset($user_all_list[$id]['逾期完成任务数']) ? $user_all_list[$id]['逾期完成任务数'] = 1 : $user_all_list[$id]['逾期完成任务数']++;
                    }
                }
            }

            // 检查数组
            // 检查是否有完成键的设置
            if (!isset($user_all_list[$id]['已完成任务数'])){
                $user_all_list[$id]['已完成任务数'] = 0;
            }

            // 检查是否有 未完成的键
            if (!isset($user_all_list[$id]['未完成任务数'])){
                $user_all_list[$id]['未完成任务数'] = 0;
            }

            // 检查是否有 逾期完成的键
            if (!isset($user_all_list[$id]['逾期完成任务数'])){
                $user_all_list[$id]['逾期完成任务数'] = 0;
            }
        }

        // 完成比率
        foreach ($check_list as $k => $v){

            if ($request->row === "个人"){
                $user_all_list[$v]['name'] =   $user_all_list[$v]['user']['name'];
            }else{
                $user_all_list[$v]['name'] =   $user_all_list[$v]['dept']['name'];
            }



            if ($user_all_list[$v]['已完成任务数'] == 0){
                $user_all_list[$v]['完成比率'] = 0;
            }


            if ($user_all_list[$v]['已完成任务数'] != 0){
                $user_all_list[$v]['完成比率'] = round($user_all_list[$v]['已完成任务数']/$user_all_list[$v]['接收任务数'],2);
            }

        }

        $reset_list =   array_values($user_all_list);


        return ResponseJson($reset_list);
    }

    public function deletefile(Request $request){
        Attachment::where('id',$request->file_id)->delete();
        return ResponseJson();
    }

    // 获取全部用户任务转交记录
    public function worktransfres(Request $request){

        $id = $request->taskid;

        // 查找到当前项下的所有
        $all_msg = TaskUser::with([
            'user',
            'worktransfres' =>function ($q){
                $q->where('work_type',self::$workType);
            },
            'worktransfres.from_user',
            'worktransfres.to_user',
            'dept'
        ])->where([
            ['task_id',$id],
            ['item_type',0]
        ])->get();

        foreach ($all_msg as $k => $v){
            $v->count =  $v->worktransfres->count();
        }

        return ResponseJson($all_msg);
    }

    public function getSubDeptByOrg($org_id)
    {
        $depts = MergeDeptOrg::where(['dept_org_id' => $org_id])->get();
        dd($depts);
    }
}
