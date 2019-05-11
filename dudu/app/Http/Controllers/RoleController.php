<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Role;
use App\Models\User;
use App\Models\OrgUser;
use App\Models\GroupUser;

class RoleController extends Controller
{
    // 根据type，获取所有角色
    public function getByType(Request $request)
    {
    	$data = Role::where('type', $request->type)->get();
    	return ResponseJson($data);
    }

    // 批量修改角色
    public function batchChange(Request $request)
    {
    	$targets = explode(",", $request->targets);
    	$role = $request->roles;

    	// if ($role == 1 || $role == 5) {
    	// 	return ResponseJson([], "不能修改为该角色！");
        // }

        if ($role == 1) {
    		return ResponseJson([], "不能修改为该角色！");
        }
        
        $user_id = session()->get('user.id');
        $user = User::findOrFail($user_id);

        // TODO
        // role_type 1：机构批量角色修改 2：群组角色修改
        if ($request->role_type == '1') {
            // 判断权限
            $role_id = $user->orgs()->where('orgs.id', $request->item)->first()->pivot->role_id;
            hasRole($role_id, [ROLE_SYS, ROLE_GRP], "权限不足，无法操作");
        	foreach ($targets as $key => $value) {
        		$data = OrgUser::where([['org_id', $request->item], ['user_id', $value]])->first();
        		$data->role_id = $role;
        		$data->save();
        	}
        } else if ($request->role_type == '2') {
            $role_id = $user->groups()->where('groups.id', $request->item)->first()->pivot->role_id;
            hasRole($role_id, [ROLE_GRP_CREATE], "权限不足，无法操作");
            foreach ($targets as $key => $value) {
                $data = GroupUser::where([['group_id', $request->item], ['user_id', $value]])->first();
                $data->role_id = $role;
                $data->save();
            } 
        }
    	return ResponseJson([]);
    }
}
