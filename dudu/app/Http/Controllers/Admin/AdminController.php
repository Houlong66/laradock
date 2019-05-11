<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\MessageController;
use App\Http\Requests\Request;
use App\Models\AdminUser;
use App\Models\Article;
use App\Models\Attachment;
use App\Models\Org;
use App\Models\User;
use App\Models\Dept;
use App\Models\Group;
use App\Models\Message;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{


    // 添加  删除  编辑
    public function changeArticles(Request $request){

//        $admin = AdminUser::find(session()->get("user.id"));

        if (isset($request->is)){
            switch ($request->is){
                case "delete":
                    Article::where("id",$request->id)->delete();
                    return ResponseJson("删除成功");
                    break;
                case "change":
                    $getArticle =  Article::where("id",$request->id)->first();
                    $getArticle->desc = $request->desc;
                    $getArticle->type = $request->type;
                    $getArticle->title = $request->title;
                    $getArticle->save();
                    return ResponseJson("修改成功");
                    break;
            }
        }else{
            Article::create([
                'title' => $request->title,
                "desc"  => $request->desc,
                "type"  => $request->type,
//                "author" => $admin->id
            ]);
            return ResponseJson("添加成功");

        }
    }

    // 获取所有的文章
    public function getAcricles(){
//        $all =  Article::with(['author'])->get();
        $all =  Article::orderBy('id', 'asc')->get();
        return ResponseJson($all);
    }



//    旧资源
    public function orgs()
    {
        // $orgs = Org::all()->where('status', '<>', 2);
        $orgs = Org::withoutGlobalScope('status')->get();
        return view('admin.orgs', compact('orgs'));
    }

    public function orgsCheck()
    {
        $orgs = Org::withoutGlobalScope('status')->where('status', 2)->get();
        return view('admin.orgs_check', compact('orgs'));
    }

    public function users()
    {
        $users = User::all();
        return view('admin.users', compact('users'));
    }

    public function enableOrg($orgId)
    {
        $org = Org::withoutGlobalScope('status')->find($orgId);
        $wx_msg_flag = false;

        // 判断是否首次操作
        if ($org->status == ORG_ST_PENDING) {
            $same = Org::where('name', $org->name)->where('region', $org->region)->get();
            if (!$same->isEmpty()) {
                session()->flash('fail', "同地区已有该机构名称");
                return redirect()->back();
            }
            $msg = [
                'title' => '机构审核结果',
                'body' => '您申请的机构「' . $org->name . '」已被管理员审核通过，请点击详情完善机构信息并设置管理密码',
                'alert' => '机构「' . $org->name . '」已被审核通过，申请者将在消息列表中看到结果。'
            ];
            $wx_msg_flag = true;
        } else {
            $msg = [
                'title' => '机构状态变更',
                'body' => '管理员启用了您的机构「' . $org->name . '」，请点击查看详情',
                'alert' => '机构「' . $org->name . '」已被启用。'
            ];
        }

        $org->status = ORG_ST_ENABLED;
        $org->save();

        // 添加默认请示类型
        DB::table('ask_types')->insert(array(
            array('org_id' => $org->id, 'name' => '工作'),
            array('org_id' => $org->id, 'name' => '请假'),
            array('org_id' => $org->id, 'name' => '用车'),
        ));


        foreach ($org->users as $user) {
            $message = (object)[];
            $message->title = $msg['title'];
            $message->content = $msg['body'];
            $message->type = MSG_TP_ORG;
            $message->subtype = MSG_SP_ORG_RES;
            $message->params = json_encode(['id' => $orgId]);
            $message->user_id = $user->id;

            // 初次注册机构的审核,需要发送微信模板消息
            if($wx_msg_flag){
                // 微信模板消息所需参数
                $message->openid = $user->openid;
                $message->tpl = TPL_APPLY_AUDIT_RESULT;
                $message->keyword1 = '注册顶级机构';
                $message->keyword2 = '审核通过';
            }
            MessageController::create($message, $wx_msg_flag);
        }

        session()->flash('success', $msg['alert']);
        return redirect()->back();
    }

    public function disableOrg($orgId)
    {
        $org = Org::withoutGlobalScope('status')->find($orgId);
        $wx_msg_flag = false;

        // 判断是否首次操作
        if ($org->status == ORG_ST_PENDING) {
            $msg = [
                'title' => '机构审核结果',
                'body' => '管理员拒绝了您对机构「' . $org->name . '」的申请，请点击查看详情',
                'alert' => '已拒绝机构「' . $org->name . '」的申请，申请者将在消息列表中看到结果。'
            ];

            $wx_msg_flag = true;
        } else {
            $msg = [
                'title' => '机构状态变更',
                'body' => '管理员禁用了您的机构「' . $org->name . '」，请点击查看详情',
                'alert' => '机构「' . $org->name . '」已被禁用。'
            ];
        }

        $org->status = ORG_ST_DISABLED;
        $org->save();

        foreach ($org->users as $user) {
            $message = (object)[];
            $message->title = $msg['title'];
            $message->content = $msg['body'];
            $message->type = MSG_TP_ORG;
            $message->subtype = MSG_SP_ORG_RES;
            $message->params = json_encode(['id' => $orgId]);
            $message->user_id = $user->id;

            // 初次注册机构的审核,需要发送微信模板消息
            if($wx_msg_flag){
                // 微信模板消息所需参数
                $message->openid = $user->openid;
                $message->tpl = TPL_APPLY_AUDIT_RESULT;
                $message->keyword1 = '注册顶级机构';
                $message->keyword2 = '审核不通过';
            }
            MessageController::create($message, $wx_msg_flag);
        }

        session()->flash('success', $msg['alert']);
        return redirect()->back();
    }

    public function depts()
    {
        $depts = Dept::all();
        return view('admin.depts', compact('depts'));
    }

    public function groups()
    {
        $groups = Group::all();
        return view('admin.groups', compact('groups'));
    }

    public function deptUsers($deptId)
    {
        $dept = Dept::find($deptId);

        $dept_name = $dept->name;
        $users = $dept->users->unique();
        return view('admin.users', compact('dept_name', 'users'));
    }

    public function groupUsers($deptId)
    {
        $group = Group::find($deptId);

        $group_name = $group->name;
        $users = $group->users->unique();
        return view('admin.users', compact('group_name', 'users'));
    }
}
