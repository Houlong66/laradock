<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Feedback;
use App\Models\Attachment;
use App\Models\User;
use Symfony\Component\VarDumper\Cloner\Data;

class FeedbackController extends Controller
{
    // 提交反馈
    public function create(Request $request){
        $user_id = session()->get('user.id');
        $feedback = new Feedback;
        $feedback->title = $request->title;
        $feedback->content = $request->content;
        $feedback->user_id = $user_id;
        $feedback->save();

        if($request->attachments != null && $request->attachments != ""){
            $attachments = explode(',', $request->attachments);
            Attachment::whereIn('id', $attachments)->update(['works_id' => $feedback->id]);
        }


        $submit_user = User::find($user_id);
        // 推送消息
        foreach(MAINTAINER as $user){
            $message = (object)[];
            $message->title = '反馈消息';
            $message->content = '有用户反馈,请查看';
            $message->type = 7;
            $message->subtype = 1;
            $message->params = json_encode([
                'id' => $feedback->id,
            ]);
            $message->user_id = $user;
            // 微信模板消息所需参数
            $get_user = User::find($user);

            $message->openid = $get_user->openid;
            $message->tpl = TPL_FEEDBACK;
            $message->keyword1 = $submit_user->name;
            $message->keyword2 = $submit_user->tel;

            MessageController::create($message, true);
        }
        return ResponseJson();
    }

    public function detail($id){
        $user_id = session()->get('user.id');
        hasRole($user_id, MAINTAINER, "没有权限");

        $feedback = Feedback::findOrFail($id);
        $feedback->user;
        foreach($feedback->attachments as $attachment){
            $attachment->total_path = asset($attachment->total_path);
        }

        return ResponseJson($feedback->toArray());
    }

    public function index(){
        $user_id = session()->get('user.id');
        hasRole($user_id, MAINTAINER, "没有权限");

        $feedbacks = Feedback::orderBy('created_at', 'desc')->get();

        return ResponseJson($feedbacks->toArray());
    }


    // 反馈消息状态修改和推送微信模板消息
    public function update(Request $request){
        $feedback = Feedback::find($request->id);
        $userinfo = User::find($request->user_id);

        if ($feedback->status === 0){
            //构建模板消息推送
            $message = (object)[];
            $message->openid = $userinfo->openid;
            $message->tpl = TPL_FEEDBACK_RESULT;
            $message->keyword1 = $request->feedback_title;
            $message->keyword2 = date("Y-m-d H:i:s");
            $message->keyword3 = "（都督用户提醒）您好！您的反馈已经受理完成！感谢您的反馈，欢迎再次使用都督，祝您生活愉快，谢谢。";
            MessageController::create($message, true,false);


            $feedback->status = 1;
            $feedback->save();
            return ResponseJson();
        }
        return ResponseJson([],"状态已更新，请不要重复提交！");
    }

}
