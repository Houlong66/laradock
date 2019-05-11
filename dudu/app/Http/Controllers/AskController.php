<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Ask;
use App\Models\User;
use App\Models\Group;
use App\Models\Message;
use App\Http\Controllers\MessageController;

class AskController extends Controller
{
    /**
     * 请示列表接口
     * @return \json
     */
    public function show()
    {
        // 返回数据数组
        $data = [];

        // 用户id
        $user_id = session()->get('user.id');
        // 用户的请示集合
        $user = User::with([
            'asks' => function ($q) {
                $q->orderBy('send_time', 'desc');
            }
        ])->find($user_id);

        // 构造每个请示项集合的数据项
        foreach ($user->asks as $i => $ask) {
            // 请示类型文案
            $a_type = $ask->asktype['name'];

            // 获取请示者的信息
            if ($ask->pivot->item_type === 0) {
                // 找到请示者
                $ask_send_user_id = Ask::find($ask->pivot->ask_id)->ask_items()->where([
                    ['item_type', 1]
                ])->first()->user_id;
                $ask_user = User::find($ask_send_user_id);

                // 找到请示者所属机构名称
                $user_org_name = $ask_user->orgs($ask->org_id)->value('name'); // 若查无此机构，属于数据异常需要日志记录

                // 找到请示者所属部门名称
                $user_dept_name = $ask_user->depts($ask->org_id)->value('name'); // 若查无此部门，属于数据异常需要日志记录

                // 构建请示者信息
                $ask_from = $user_org_name . '-' . $user_dept_name . '-' . $ask_user->name;
            } else {
                $ask_from = null;
            }

            // 构建返回数据
            $obj = array(
                'ask_id' => $ask->id,  // 请示id
                'title' => $ask->title,  // 请示标题
                'ask_time' => $ask->send_time,
                'send_time' => $ask->send_time,
                'status' => $ask->pivot->status===2?1:$ask->pivot->status, // 请示状态,若为2,则表明被别人审批了，所以也应是已处理状态
                'a_type' => $a_type, // 请示类型
                'ask_from' => $ask_from,
                'self_send' => $ask->pivot->item_type, // 是否我发送的
            );

            // 通知项插入返回数据数组
            $data[] = $obj;
        }

        return ResponseJson($data);
    }

    /**
     * 创建请示
     * @param Request $request
     * @return \json
     */
    public function store(Request $request)
    {
        // 用户创建者id
        $creator_id = session()->get('user.id');

        $ask = new Ask;

        // 创建ask
        $ask->org_id = $request->org_id;
        $ask->title = $request->title;
        $ask->desc = $request->desc;
        $ask->ask_type = $request->ask_type;
        $ask->send_time = date('Y-m-d H:i:s');
        $ask->save();

        // 通知创建者添加中间关联数据
        $ask->users()->attach($creator_id, [
            'item_type' => 1,
            'status' => 0,
            'work_send_id' => $creator_id
        ]);

        // 关联附件
        $common = new CommonController();
        $common->attachAttachment($ask->id, 0 , $request->attachment,0, 'ask');

        $res = [
            'text' => '请示创建成功',
            'id' => $ask->id,
        ];

        return ResponseJson($res);
    }


    public function detail($ask_id)
    {
        // 找到该id的请示
        $org_id = Ask::find($ask_id)->org_id;

        $ask = Ask::with([
            'org',
            'ask_items',
            'ask_items.user',
            'ask_items.work_send',
            'ask_items.user.orgs' => function ($q) use ($org_id) {
                $q->where('org_id', $org_id);
            },
            'ask_items.user.depts' => function ($q) use ($org_id) {
                $q->where('org_id', $org_id);
            },
            'msg_boards' => function($query){
                $query->where('type', 3);
                $query->orderBy('created_at', 'desc');
            },
            'msg_boards.user' => function($query){
                $query->select(['id', 'name', 'avatar']);
            }
        ])->find($ask_id);

        // 获取关联附件 todo 待优化，改为只获取用户相关的附件信息
        foreach($ask->attachments as $attachment){
            $attachment->total_path = asset($attachment->total_path);
        }

        $ask = $ask->toArray();





        // 若有人部门为空，补全
        foreach ($ask['ask_items'] as $key => $value) {
            if (count($value['user']['depts']) == 0) {
                $ask['ask_items'][$key]['user']['depts'] = [
                    [
                        'name' => '无'
                    ]
                ];
            }
        }
        return ResponseJson($ask);
    }

    // 流转审批接口
    public function addFlowAuditors(Request $request)
    {
        $user = User::find(session()->get("user.id"));

        $send_to_objs = explode(',', $request->send_to_objs);

        // 通知的内容
        $ask = Ask::find($request->id);

        // 请示创建者
        $ask_user_creator = $ask->users()->where([
            ['item_type', 1]
        ])->first();

        // 判断通知状态是否为待审核
        if ($ask_user_creator->pivot->status !== 0) {
            return ResponseJson([], '请示状态有误，无法添加审批人');
        }

        // 判断审批对象数组中的用户，是否已经添加过流转审批记录
        $ask_existing_auditors = $ask->users()
            ->where('item_type', 0)
            ->whereIn('user_id', $send_to_objs)
            ->get();

        // 过滤已存在审批记录的用户
        foreach ($ask_existing_auditors as $key => $a) {
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

        // 微信需要的消息
        if($request->wx_send_id){
            $msg_params['work_send_id'] = $request->wx_send_id;
            $msg_params['note_text']    = $request->wx_note_text;
        }

        // 获取要添加的审批者
        $ask_new_auditors = User::whereIn('id', $send_to_objs)->get();

        // 是否发送微信消息标记
        $wx_msg_flag = $request->if_send_wx_message === 1 ? true : false;

        foreach ($ask_new_auditors as $auditor) {
            // 过滤任务创建者自己
            if ((int)$auditor->id === $ask_user_creator->pivot->user_id) {
                continue;
            }

            // 添加流转审批记录
            $ask->users()->attach($request->id, [
                'item_type' => 0,
                'user_id' => $auditor->id,

                'work_send_id' => $msg_params ? $msg_params['work_send_id'] :  $user->id,
                'note_text' => $request->notetext ? $request->notetext : $msg_params['note_text'] ,

                'status' => 0,
            ]);

            $ask_type = "请示类型（{$ask->asktype['name']}）";

            // 添加消息记录
            $message = (object)[];
            $message->title = '新请示提醒';
            $message->content = '您有新的请示事项，请及时审批';
            $message->type = 3;
            $message->subtype = 1;
            // $message->params = "{'id':" . $ask->id . "}";
            $message->params = json_encode([
                'id' => $ask->id,
                'work_send_id' => $user->id,
                'note_text' => $request->notetext,
            ]);
            $message->user_id = $auditor->id;
            // 微信模板消息所需参数
            $message->openid = $auditor->openid;
            $message->tpl = TPL_WORK_SEND;
            $message->keyword1 = "{$ask_type}";
            $message->keyword2 = '请示';
            $message->keyword3 = "{$ask->title}";
            MessageController::create($message, $wx_msg_flag);

        }

        return ResponseJson('成功给指定用户发送流转审批项');
    }

    public function flowAudit(Request $request)
    {
        $user_id = session()->get('user.id');

        // 请示项
        $ask = Ask::find($request->ask_id);

        // 发送人的请示项
        $ask_creator = Ask::find($request->ask_id)->users()->where([
            ['item_type',1],
        ]);
        $ask_creator_user = $ask_creator->first();
        if($ask_creator_user->pivot->user_id === $user_id){
            return ResponseJson([],'不可审批自己创建的请示');
        }

        // 流转审批人请示项
        $ask_auditor = Ask::find($request->ask_id)->users()->where([
            ['item_type',0],
            ['user_id',$user_id],
        ]);
        $ask_auditor_user = $ask_auditor->first();

        if($ask_creator_user->pivot->status===0 && $ask_auditor_user->pivot->status===0){
            $time = date('Y-m-d H:i:s');

            // 更新中间表相关项的值
            $ask_auditor->updateExistingPivot($user_id,[
                'audit_text' => $request->audit_text,
                'audit_time' => $time,
                'status' => $request->audit_result,
            ]);
            $ask_creator->updateExistingPivot($ask_creator_user->pivot->user_id,[
                'audit_text' => $request->audit_text,
                'audit_time' => $time,
                'status' => $request->audit_result,
            ]);
            // 将其他审批人的待审批请示项状态改为废弃
            DB::table('ask_user')->where([
                ['ask_id',$request->ask_id],
                ['user_id','<>',$user_id],
                ['item_type',0],
            ])->update(['status'=>2]);

            // 发送消息请示创建者
            $message = (object)[];
            $message->title = '请示批复结果';
            $message->content = '您的请示已被审批，请查看审批结果';
            $message->type = MSG_TP_QST;
            $message->subtype = MSG_SP_QST_PROCESSED;
            $message->params = json_encode([
                'id' => $request->ask_id,
                'status' => $request->audit_result
            ]);
            $message->user_id = $ask_creator_user->pivot->user_id;

            // 微信模板消息所需参数 Message done
            $message->openid = $ask_creator_user->openid;
            $message->tpl = TPL_APPLY_AUDIT_RESULT;
            $message->keyword1 = "请示审批[{$ask->title}]";
            $message->keyword2 = "点击查看详情";
            MessageController::create($message, true);

        }else{
            // 任务流转审批项状态值有误，无法进行审批
            return ResponseJson([],'请示已被审批,无法再次审批');
        }

        return ResponseJson('审批已完成');
    }
}

