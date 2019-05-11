<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

/* 微信模块 */
Route::group(['middleware' => env('APP_ENV')==='local'?['web', 'set_custom_user_id']:['web', 'wechat.oauth:dudu, snsapi_base', 'set_user_id']], function () {
    Route::get('/', function () {
        return view('index');
    });

    // Vue 开启 history 模式后，需如下设置配合;
    Route::view('/{query}', 'index')->where('query', '^((?!(admin/)|(attach/)|(file?)|(js/)).)*$');
});


/* 管理模块 */

// 由于框架要求默认的登录跳转路由名必须为 'login' 且不便修改
// 参考 https://github.com/laravel/ideas/issues/800
// 因此这里将其单独抽出，否则其名称会变为 'admin.login' 而导致无法访问
Route::get('admin/login', function() { return view('admin/login'); })->name('login');


Route::group(['prefix' => 'admin', 'namespace' => 'Admin', 'as' => 'admin.'], function () {
    // 临时用于重置管理员用户名密码的隐藏接口，默认注释掉
//     Route::get('/reset-admin', 'LoginController@resetAdmin');

    Route::post('/login', 'LoginController@login')->name('authenticate');
    Route::post('/logout', 'LoginController@logout')->name('logout');


    Route::middleware('auth')->group(function () {
        Route::get('/control', function() { return view('admin/index'); })->name('index');

        Route::get('/orgs', 'AdminController@orgs')->name('orgs');
        Route::get('/orgs_check', 'AdminController@orgsCheck')->name('orgs.check');

        Route::get('/users', 'AdminController@users')->name('users');

        Route::get('/depts', 'AdminController@depts')->name('depts');
        Route::get('/groups', 'AdminController@groups')->name('groups');

        Route::get('/depts/{id}/users', 'AdminController@deptUsers')->name('dept.users');
        Route::get('/groups/{id}/users', 'AdminController@groupUsers')->name('group.users');

        Route::get('/orgs/enable/{id}', 'AdminController@enableOrg')->name('orgs.enable');
        Route::get('/orgs/disable/{id}', 'AdminController@disableOrg')->name('orgs.disable');
    });
});

// 外部浏览器无状态文件下载接口 By Kingsley
Route::get('file/', 'CommonController@index')->name('file.index');

// 给图片资源做的单独路由，用于显示对图片链接的直接请求
Route::get('/attach/{name}', function($name) {
    $name = storage_path('app/attachments/attach/') . $name;
    if (!File::exists($name)) abort(404);

    return response()->file($name);
})->where('name', '.+\.(jpe?g|png|tiff|gif|bmp|webp)$');
