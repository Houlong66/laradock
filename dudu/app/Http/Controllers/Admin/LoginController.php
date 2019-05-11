<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\AdminUser;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->only('username', 'password');

        if (Auth::attempt($credentials)) {
            return redirect(route('admin.index'));
        } else {
            session()->flash('fail', '用户名或密码错误');
            return redirect(route('login'))->withInput(
                $request->except('password')
            );
        }
    }

    public function logout(Request $request)
    {
        Auth::logout();
        session()->flash('success', '已退出登录系统');
        return redirect(route('login'));
    }

    public function resetAdmin(Request $request)
    {
        // 临时的管理员账号密码设置方案，默认注释掉对应路由。
        // 使用方法：设置好下一行的用户名密码然后删掉路由的注释即可

        $username = 'admin'; $password = 'admin';
        // 运行后请恢复路由的注释状态 避免安全等问题

        AdminUser::truncate();
        AdminUser::create([
            'username' => $username,
            'password' => bcrypt($password),
        ]);

        session()->flash('success', "重置用户名： $username ，密码： $password 。");
        return redirect(route('login'));
    }
}
