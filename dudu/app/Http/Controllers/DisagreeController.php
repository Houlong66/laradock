<?php

namespace App\Http\Controllers;

use App\Models\Org;
use App\Models\User;
use App\Models\Role;
use App\Models\Dept;
use App\Models\Message;
use App\Http\Requests\AgreeRequest;
use App\Http\Controllers\MessageController;

class DisagreeController extends Controller
{
    // 登录用户申请加入新机构 todo delete?
    public function joinOrgWithLogin(AggreRequest $request)
    {
        $user_id = session()->get('user.id');
        $user = User::findOrFail($user_id);
        $org = Org::findOrFail($request->input('org_id'));
        $role = Role::findOrFail($request->input('role_id'));
        $dept = $request->has('dept_id') ?
            Dept::findOrFail($request->input('dept_id')) : null;

        // 业务逻辑验证参数
        $dept_name = $dept != null ? '-' . $dept->name : '';
        $params = json_encode([
            'org_id'  => $request->input('org_id'),
            'dept_id' => $dept != null ? $dept->id : null,
        ]);
        if ($role->type != 1) {
            return ResponseJson([], '选择的角色不为系统角色');
        }
        if ($dept != null && $dept->org->id != $org->id) {
            return ResponseJson([], '选择的部门不属于选择的机构');
        }

        // 创建消息
        $message = (object)[
            'title'   => '加入机构申请',
            'content' => "用户「{$user->name}」申请以「{$role->name}」角色加入「{$org->name}{$dept_name}」，请及时处理",
            'status'  => MSG_ST_UNREAD,
            'type'    => MSG_TP_USR,
            'subtype' => MSG_SP_USR_JOIN_REQ,
            'params'  => $params,
            'user_id' => $user->id,
        ];
        MessageController::create($message);
        return ResponseJson($message);
    }
}
