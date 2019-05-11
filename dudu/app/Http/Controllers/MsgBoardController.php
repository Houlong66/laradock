<?php

namespace App\Http\Controllers;

use App\Http\Requests\Request;
use App\Models\MsgBoard;
use App\Models\Task;
use App\Models\Notification;
use App\Models\Ask;
use App\Models\User;


class MsgBoardController extends Controller{
    // 添加讨论板信息
    public function create(Request $request){
        $check = null;
        switch($request->type){
            case MSG_TP_TSK:
               $check = Task::find($request->foreign_id);
               break;
            case MSG_TP_NTC:
                $check = Notification::find($request->foreign_id);
                break;
            case MSG_TP_QST:
                $check = Ask::find($request->foreign_id);
                break;
        }
        if($check === null)
            return ResponseJson([], "该消息不存在");

        $user_id = session()->get('user.id');
        $board = new MsgBoard;
        $board->foreign_id = $request->foreign_id;
        $board->user_id = $user_id;
        $board->type = $request->type;
        $board->content = $request->content;
        $board->at_sign = $request->at_sign;
        $board->save();



        // 推送消息
        if($request->at_sign != null && $request->at_sign != ''){
            $users = explode(',', $request->at_sign);
            foreach($users as $user){
                // 创建推送消息
                $message = (object)[];
                $message->title = '您有新的消息';
                $message->content = '您在讨论板中被@,点击查看';
                $message->type = MSG_TP_USR;
                $message->subtype = MSG_SP_USR_DIS;
                $message->params = json_encode([
                    'id' => intval($request->foreign_id),
                    'type' => intval($request->type)
                ]);
                $message->user_id = intval($user);
                $message->wx_title = "讨论板消息";
                $message->wx_msg_time = date("Y-m-d H:i:s");
                // 微信模板消息所需参数
                $message->openid = User::find($user)->openid;
                $message->tpl = TPL_MESSAGES_RESULT;
                $message->keyword1 = "@你的工作讨论消息";
                $message->keyword2 = "待查看";
                $message->keyword3 = date("Y-m-d H:i:s");
                MessageController::create($message, true);
            }
        }

        return ResponseJson();
    }
}