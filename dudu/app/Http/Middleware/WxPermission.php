<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\User;

class WxPermission
{
//    public function handle($request, Closure $next, $permission)
//    {
//        $user_id = session()->get('user.id');
//        $user = User::find($user_id);
//        $user->getAllPermissions();
//
//        $permissions = is_array($permission)
//            ? $permission
//            : explode('|', $permission);
//
//        foreach ($permissions as $permission) {
//            if ($user->hasPermissionTo($permission)) {
//                return $next($request);
//            }
//        }
//
//        $mes = "User does not have the right role";
//        return ResponseJson([],$mes);
//    }
}
