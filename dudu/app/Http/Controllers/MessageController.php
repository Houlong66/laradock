<?php

namespace App\Http\Controllers;

use App\Models\Org;
use App\Models\Dept;
use App\Models\Message;
use App\Models\User;
use Faker\Test\Provider\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\MessageRequest;
use Mockery\Undefined;
use phpDocumentor\Reflection\Types\Boolean;

class MessageController extends Controller
{
    public function __construct()
    {
        // todo
//        $this->middleware(['wx_role:test_role']);
//        $this->middleware('wx_permission:message.unread',['only'=>['unread']]);
//        $this->middleware('wx_permission:message.read',['only'=>['read']]);
    }

    // 获取当前用户的所有未读消息，正序排列
    public function unread(MessageRequest $request)
    {
        $messages = Message::where([
            ['user_id', session()->get('user.id')],
            ['status', MSG_ST_UNREAD]
        ])
            ->get();

        return ResponseJson($messages);
    }

    // 获取当前用户的三条最近已读消息，倒序排列
    public function read(MessageRequest $request)
    {
        $messages = Message::where([
            ['user_id', session()->get('user.id')],
            ['status', MSG_ST_READ]
        ])
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get();

        return ResponseJson($messages);
    }

    // 根据参数获取当前用户的最近已读消息，倒序排列
    public function readByParams(MessageRequest $request, $after, $limit)
    {
        $messages = Message::where([
            ['user_id', session()->get('user.id')],
            ['status', MSG_ST_READ],
            ['id', '<', $after]
        ])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();

        return ResponseJson($messages);
    }


    // 根据消息ID设置消息已读 或设置为全部已读
    public function setRead(MessageRequest $request, $msgId)
    {

        Message::where('id', $msgId)->update(['status' => MSG_ST_READ]);
        return ResponseJson();
    }


    // 设置全部消息为已读
    public function allsetread(MessageRequest $request){
            $user_id = User::find(session()->get("user.id"));

            Message::where("user_id",$user_id->id)->update(
                ['status' => 1]
            );
            return ResponseJson();
    }


    // 根据 ID 获取消息
    public function getMsg(Message $message)
    {
        return ResponseJson($message);
    }

    // 新增一条消息 todo 控制器间相互调用，待优化
    public static function create($request, $wx_msg = false, $sys_msg = true)
    {
        $message = new Message;


        // 微信模板消息用到的变量
        $wx_params = [
            'openid' => $request->openid ?? null,
            'tpl' => $request->tpl ?? null,
            'first' => $request->first ?? null,
            'keyword1' => $request->keyword1 ?? null,
            'keyword2' => $request->keyword2 ?? null,
            'keyword3' => $request->keyword3 ?? null,
            'keyword4' => $request->keyword4 ?? null,
            'remark' => $request->remark ?? null,
        ];


        //  需要发送系统消息时
        if ($sys_msg) {
            $message->title = $request->title;
            $message->content = $request->content;
            $message->type = $request->type;
            $message->subtype = $request->subtype;
            $message->params = $request->params;

            $message->send_id = isset($request->send_id) ? $request->send_id : 0;


            $message->user_id = $request->user_id;

            $params = json_decode($request->params);

            // 生成微信消息模板详情跳转url
            switch ($message->type) {

                // 任务
                case MSG_TP_TSK:
                    switch ($message->subtype) {
                        // 新任务提醒
                        case MSG_SP_TSK_NEW:
                            $message->url = '/task_detail/' . $params->id . '?dept_id=' . $params->dept_id;

                            $wx_params['data'] = [
                                'keyword1' => $wx_params['keyword1'],
                                'keyword2' => [$wx_params['keyword2'], COL_BLUE],
                                'keyword3' => [$wx_params['keyword3'], COL_RED],
                            ];

                            break;

                        // 任务上报待审核
                        case MSG_SP_TSK_PENDING:
                            $message->url = '/to_report_task/' . $params->id . '?user_id=' . $params->report_user_id .
                                '&dept_id=' . $params->dept_id;

                            $wx_params['data'] = [
                                'first' => $wx_params['first'],
                                'keyword1' => $wx_params['keyword1'],
                                'keyword2' => [$wx_params['keyword2'], COL_BLUE],
                                'keyword3' => $wx_params['keyword3'],
                                'keyword4' => [$wx_params['keyword4'], COL_RED],
                            ];
                            break;
                        // 任务上报审核结果
                        case MSG_SP_TSK_RESULT:
                            $message->url = '/task_detail/' . $params->id . '?dept_id=' . $params->dept_id;

                            $wx_params['data'] = [
                                'first' => $wx_params['first'],
                                'keyword1' => [$wx_params['keyword1'], COL_BLUE],
                                'keyword2' => [$wx_params['keyword2'], COL_RED],
                            ];
                            break;
                        // 任务流转审批待审核
                        case MSG_SP_TSK_CA_PENDING:
                            $message->url = '/to_audit_task/' . $params->id . '?type=0';

                            $wx_params['data'] = [
                                'first' => $wx_params['first'],
                                'keyword1' => $wx_params['keyword1'],
                                'keyword2' => [$wx_params['keyword2'], COL_BLUE],
                                'keyword3' => $wx_params['keyword3'],
                                'keyword4' => [$wx_params['keyword4'], COL_RED],
                            ];
                            break;
                        // 任务流转审批结果
                        case MSG_SP_TSK_CA_RESULT:
                            // 审批通过
                            if ($params->status == 2) {
                                $message->url = '/task_detail_self/' . $params->id;
                            } // 审核不通过
                            else {
                                $message->url = '/create_task?task_id=' . $params->id;
                            }

                            $wx_params['data'] = [
                                'first' => $wx_params['first'],
                                'keyword1' => [$wx_params['keyword1'], COL_BLUE],
                                'keyword2' => [$wx_params['keyword2'], COL_RED],
                            ];

                            break;
                        // 任务到期提醒
                        case MSG_SP_TSK_EXPIRED:
                            $message->url = '/task_detail/' . $params->id . '?dept_id=' . $params->dept_id;

                            $wx_params['data'] = [
                                'first' => $wx_params['first'],
                                'keyword1' => [$wx_params['keyword1'], COL_BLUE],
                                'keyword2' => [$wx_params['keyword2'], COL_RED],
                                'keyword3' => [$wx_params['keyword3'], COL_BLUE]
                            ];
                            break;
                        default:
                            break;
                    }
                    break;


                // 通知
                case MSG_TP_NTC:
                    switch ($message->subtype) {
                        // 新通知提醒
                        case MSG_SP_NTC_CA_NEW:
                            $message->url = '/notice_detail/' . $params->id;

                            $wx_params['data'] = [
                                'keyword1' => $wx_params['keyword1'],
                                'keyword2' => [$wx_params['keyword2'], COL_BLUE],
                                'keyword3' => [$wx_params['keyword3'], COL_RED],
                            ];
                            break;
                        // 流转审批待审批
                        case MSG_SP_NTC_CA_PENDING:
                            $message->url = '/to_audit_task/' . $params->id . '?type=1';

                            $wx_params['data'] = [
                                'first' => $wx_params['first'],
                                'keyword1' => $wx_params['keyword1'],
                                'keyword2' => [$wx_params['keyword2'], COL_BLUE],
                                'keyword3' => $wx_params['keyword3'],
                                'keyword4' => [$wx_params['keyword4'], COL_RED],
                            ];
                            break;
                        // 通知流转审批结果
                        case MSG_SP_NTC_CA_RESULT:
                            // 审批通过
                            if ($params->status == 2) {
                                $message->url = '/notice_detail_self/' . $params->id;
                            } // 审批不通过
                            else {
                                $message->url = '/create_notice?notice_id=' . $params->id;
                            }

                            $wx_params['data'] = [
                                'first' => $wx_params['first'],
                                'keyword1' => [$wx_params['keyword1'], COL_BLUE],
                                'keyword2' => [$wx_params['keyword2'], COL_RED],
                            ];

                            break;
                        default:
                            break;
                    }
                    break;

                // 请示
                case MSG_TP_QST:
                    switch ($message->subtype) {
                        // 待处理请示
                        case MSG_SP_QST_PENDING:
                            $message->url = '/ask_detail/' . $params->id . '?self_send=0';

                            $wx_params['data'] = [
                                'keyword1' => $wx_params['keyword1'],
                                'keyword2' => [$wx_params['keyword2'], COL_BLUE],
                                'keyword3' => [$wx_params['keyword3'], COL_RED],
                            ];
                            break;
                        // 已处理请示
                        case MSG_SP_QST_PROCESSED:
                            $message->url = '/ask_detail/' . $params->id . '?self_send=1';

                            $wx_params['data'] = [
                                'first' => $wx_params['first'],
                                'keyword1' => $wx_params['keyword1'],
                                'keyword2' => [$wx_params['keyword2'], COL_BLUE],
                            ];
                            break;
                        default:
                            break;
                    }
                    break;



                // 日程
                // TODO: 用于后端接口测试，因为前端还没做，做完之后需要把 URL 改成实际的 URL
                case MSG_TP_SCH:
                    switch ($message->subtype) {
                        // 新增日程提醒
                        case MSG_SP_SCH_NEW:
                            $message->url = '/schedule/detail?id=' . $params->id;
                            break;

                        // 日程到期提醒
                        case MSG_SP_SCH_EXPIRED:
                            $message->url = '/schedule/detail?id=' . $params->id;

                            $wx_params['data'] = [
                                'first' => $wx_params['first'],
                                'keyword1' => [$wx_params['keyword1'], COL_BLUE],
                                'keyword2' => [$wx_params['keyword2'], COL_RED],
                                'keyword3' => [$wx_params['keyword3'], COL_BLUE]
                            ];
                            break;

                        // 纪念日提醒
                        case MSG_SP_MEM_EXPIRED:
                            $message->url = '/schedule/detail?id=' . $params->id;

                            $wx_params['data'] = [
                                'first' => $wx_params['first'],
                                'keyword1' => [$wx_params['keyword1'], COL_BLUE],
                                'keyword2' => [$wx_params['keyword2'], COL_RED],
                                'keyword3' => [$wx_params['keyword3'], COL_BLUE]
                            ];
                            break;
                    }
                    break;


                // 组织
                case MSG_TP_ORG:
                    switch ($message->subtype) {
                        // 机构申请结果
                        case MSG_SP_ORG_RES:
                            $message->url = '/messages';
                            $wx_params['data'] = [
                                'keyword1' => [$wx_params['keyword1'], COL_BLUE],
                                'keyword2' => [$wx_params['keyword2'], COL_RED]
                            ];
                            break;


                        case 2:
                            $message->url = '/merge_org_msg';

                            // 微信模板todo
                            $wx_params['tpl'] = 1;
                            $wx_params['data'] = [];
                            break;


                        case 3:
                            $message->url = '/merge_org_msg';
                            // 微信模板todo
                            break;

                        //移出机构结果
                        case MSG_SP_ORG_DELETE_RES:
                            //点击跳转的页面
                            $message->url = '/messages';
                            // 微信模板todo
                            $wx_params['data'] = [
                                'keyword1' => [$wx_params['keyword1'], COL_BLUE],
                                'keyword2' => [$wx_params['keyword2'], COL_RED],
                                'keyword3' => [$wx_params['keyword3'], COL_BLUE],
                            ];
                            break;


                        default:
                            break;
                    }
                    break;


                // 用户
                case MSG_TP_USR:
                    switch ($message->subtype) {
                        //  加入机构申请
                        case MSG_SP_USR_JOIN_REQ:
                            $message->url = "/check_join_org";
                            $wx_params['data'] = [
                                'keyword1' => [$wx_params['keyword1'], COL_BLUE],
                                'keyword2' => [$wx_params['keyword2'], COL_BLUE],
                                'keyword3' => [$wx_params['keyword3'], COL_BLUE],
                                'keyword4' => [$wx_params['keyword4'], COL_RED],
                            ];
                            break;
                        //  加入机构结果
                        case MSG_SP_USR_JOIN_RES:
                            $message->url = '/messages';
                            $wx_params['data'] = [
                                'keyword1' => [$wx_params['keyword1'], COL_BLUE],
                                'keyword2' => [$wx_params['keyword2'], COL_RED],
                                'keyword3' => $wx_params['keyword3'],
                            ];
                            break;

                        //  邀请加入群组
                        case MSG_SP_USR_GRP_REQ:
                            $message->url = '/check_invite_group';

                            $wx_params['data'] = [
                                'keyword1' => [$wx_params['keyword1'], COL_BLUE],
                                'keyword2' => [$wx_params['keyword2'], COL_RED],
                                'keyword3' => $wx_params['keyword3'],
                            ];
                            break;

                        // 邀请加入群组结果
                        case MSG_SP_USR_GRP_RES:
                            $message->url = '/group_user_list?group_id='.$params->group_id;

                            $wx_params['data'] = [
                                'first' => $wx_params['first'],
                                'keyword1' => [$wx_params['keyword1'], COL_BLUE],
                                'keyword2' => [$wx_params['keyword2'], COL_RED],
                                'keyword3' => $wx_params['keyword3'],
                            ];
                            break;
                        // 讨论板@消息通知
                        case MSG_SP_USR_DIS:
                            $msg = Message::where('type', $params->type)->where('user_id', $request->user_id)
                                ->where('params', 'like', '%"id":' . $params->id . '%')->first();
                            if ($msg != null) {
                                $message->url = $msg->url;
                            } else {
                                if ($params->type == 1) {
                                    $message->url = '/task_detail_self/' . $params->id;
                                } else {
                                    if ($params->type == 2) {
                                        $message->url = '/notice_detail_self/' . $params->id;
                                    } else {
                                        $message->url = '/ask_detail/' . $params->id . '?self_send=1';
                                    }
                                }
                            }
                            // 微信模板todo
                            $wx_params['data'] = [
                                'keyword1' => [$wx_params['keyword1'], COL_BLUE],
                                'keyword2' => [$wx_params['keyword2'], COL_RED],
                                'keyword3' => $wx_params['keyword3'],
                            ];
                            break;
                        // 主动退群通知
                        case 8:
                            $message->url = '';
                            // 微信模板todo
                            $wx_params['tpl'] = 1;
                            $wx_params['data'] = [];
                            break;

                        // 主动申请加入群组
                        case MSG_SP_USR_APPLY_GRP_REQ:
                            $message->url = '/check_join_group';
                            $wx_params['data'] = [
                                'keyword1' => [$wx_params['keyword1'], COL_BLUE],
                                'keyword2' => [$wx_params['keyword2'], COL_BLUE],
                                'keyword3' => [$wx_params['keyword3'], COL_BLUE],
                                'keyword4' => [$wx_params['keyword4'], COL_RED],
                            ];
                            break;
                        // 同意 主动加入群组申请
                        case MSG_SP_USR_APPLY_GRP_AGREE_RES:
                            $message->url = '/group_user_list?group_id='.$params->group_id;
                            $wx_params['data'] = [
                                'keyword1' => [$wx_params['keyword1'], COL_BLUE],
                                'keyword2' => [$wx_params['keyword2'], COL_RED],
                            ];
                            break;
                        // 拒绝 主动加入群组申请
                        case MSG_SP_USR_APPLY_GRP_REJECT_RES:
                            $message->url = '/messages';
                            $wx_params['data'] = [
                                'keyword1' => [$wx_params['keyword1'], COL_BLUE],
                                'keyword2' => [$wx_params['keyword2'], COL_RED],
                            ];
                            break;

                        //  拒绝加入机构
                        case MSG_SP_USR_JOIN_ORG_REJCET_RES:
                            $message->url = '/messages';
                            $wx_params['data'] = [
                                'keyword1' => [$wx_params['keyword1'], COL_BLUE],
                                'keyword2' => [$wx_params['keyword2'], COL_RED],
                                'keyword3' => $wx_params['keyword3'],
                            ];
                            break;

                        default:
                            break;
                    }
                    break;
                // 系统
                case 7:
                    switch ($message->subtype) {
                        case 1:

                            $message->url = '/feedback/detail/' . $params->id;
                            // 微信模板todo
                            $wx_params['data'] = [
                                'keyword1' => [$wx_params['keyword1'], COL_BLUE],
                                'keyword2' => [$wx_params['keyword2'], COL_BLUE],
                                'keyword3' => [$wx_params['keyword3'], COL_BLUE],
                                'keyword4' => [$wx_params['keyword4'], COL_BLUE],
                            ];
                            break;
                    }
                    break;

                default:
                    break;
            }

            $message->save();

            // url 拼上messageId
            if (count(explode("?", $message->url)) === 1) {
                $message->url .= "?fo_msg_id={$message->id}";
            } else {
                if(count(explode("fo_msg_id=", $message->url)) === 1) {
                    $message->url .= "&fo_msg_id={$message->id}";
                }
            }
            $message->save();
        } else {
            // 反馈回复
            $message->url = '';
            $wx_params['data'] = [
                'keyword1' => [$wx_params['keyword1'], COL_BLUE],
                'keyword2' => [$wx_params['keyword2'], COL_BLUE],
                'keyword3' => [$wx_params['keyword3'], COL_BLUE],
                'keyword4' => [$wx_params['keyword4'], COL_BLUE],
            ];
        }


        if ($wx_msg) {

            /** 微信模板消息推送 */
            // 微信消息推送 -- 新任务提醒消息
            $app_conf = 'wechat.official_account.dudu';

            $app = app($app_conf);

            $app->template_message->send([
                'touser' => $wx_params['openid'],
                'template_id' => $wx_params['tpl'],
                'url' => wxUrlHandle($message->url), // 格式化 URL
                'data' => $wx_params['data'],
            ]);

        }

        return ResponseJson();
    }


    // 获取当前用户主动发送的加入工作群组
    public function getsend(MessageRequest $request)
    {

        $user_id = session()->get('user.id');

        // 加入机构消息
        if ($request->addGroups == null) {
            $msg_all = Message::where([
                ['send_id', $user_id],
                ['status', 0],
                ['type', 6],
                ['subtype', 1]
            ])->orderBy('id', 'desc')->first();
            return ResponseJson($msg_all);
        }

        // 加入群组消息
        $add_groups = Message::where([
            ['send_id', $user_id],
            ['status', 0],
            ['type', 6],
            ['subtype', 9]
        ])->orderBy('id', 'desc')->get();

        if (count($add_groups) == 0) {
            return ResponseJson(null);
        }


        $ok = null;
        // 循环判断是否存在一个type 为0 的工作群组
        foreach ($add_groups as $k => $v) {
            // 转换JSON字符串
            $params = json_decode($v['params']);
            // 找到工作群组申请直接返回
            if ($params->group_type == 0) {
                $ok = 1;
                return ResponseJson($v);
            }
        }
        if (empty($ok)) {
            return ResponseJson(null);
        }
    }
}
