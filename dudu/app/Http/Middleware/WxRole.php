<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\User;

class WxRole
{
//    public function handle($request, Closure $next, $role)
//    {
//        $user_id = session()->get('user.id');
//        $user = User::find($user_id);
//        $user->getRoleNames();
//
//        $roles = is_array($role)
//            ? $role
//            : explode('|', $role);
//
//        if (! $user->hasAnyRole($roles)) {
//            $mes = "User does not have the right role";
//            return ResponseJson([],$mes);
//        }
//
//        return $next($request);
//    }
}
