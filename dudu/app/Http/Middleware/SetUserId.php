<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\User;

class SetUserId
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $user = session('wechat.oauth_user'); // 拿到授权用户资料
        $openid = $user['dudu']['id'];  // 用户openid
//        $openid = $user['dudu']['token']['openid'];  // 用户openid
        $model = User::where('openid', $openid)->first();
        $user_id = $model != null ? $model->id : null;

        // 纯新用户则添加一条记录
        if(!$user_id){
            $user = new User;
            $user->openid = $openid;
            $user->save();
        }

        session()->put('user.openid', $openid);
        session()->put('user.id', $user_id);

        return $next($request);
    }
}
