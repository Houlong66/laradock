<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\User;

class SetCustomUserId
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     *
     */

    public function handle($request, Closure $next)
    {
        $user_id = 1;
        $model = User::find($user_id);
        $openid = $model != null ? $model->openid : null;
        session()->put('user.openid', $openid);
        session()->put('user.id', $user_id);
        return $next($request);
    }

}
