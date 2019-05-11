<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::any('wechat/{account}', 'WeChatController@serve');

Route::get('wx/js_sdk_config','WeChatController@jsSdkConfig');



//Route::middleware('auth:api')->get('/user', function (Request $request) {
//    return $request->user();
//});

/*
    Mock data api
*/
Route::get('/missions', function (Request $request){
    $arr = json_decode(file_get_contents('../mock_data/mock-data.json'), true);
    $arr = $arr['missionLists'];
    return $arr;
});
Route::get('/work/notifications', function (Request $request){
    $arr = json_decode(file_get_contents('../mock_data/mock-data.json'), true);
    $arr = $arr['notifications'];
    return ['data'=>$arr];
});
Route::get('/work/asks', function (Request $request){
    $arr = json_decode(file_get_contents('../mock_data/mock-data.json'), true);
    $arr = $arr['asks'];
    return ['data'=>$arr];
});
// organizations
Route::get('/organizations', function (Request $request){
    $arr = json_decode(file_get_contents('../mock_data/mock-organization-data.json'), true);
    $arr = $arr['organizations'];
    return $arr;
});
Route::get('/organizations/dept', function (Request $request){
    $arr = json_decode(file_get_contents('../mock_data/mock-organization-data.json'), true);
    $arr = $arr['dept'];
    return $arr;
});
Route::get('/organizations/group', function (Request $request){
    $arr = json_decode(file_get_contents('../mock_data/mock-organization-data.json'), true);
    $arr = $arr['group'];
    return $arr;
});
Route::get('/organizations/outerContacts', function (Request $request){
    $arr = json_decode(file_get_contents('../mock_data/mock-organization-data.json'), true);
    $arr = $arr['outerContacts'];
    return $arr;
});
Route::get('/organizations/starContacts', function (Request $request){
    $arr = json_decode(file_get_contents('../mock_data/mock-organization-data.json'), true);
    $arr = $arr['starContacts'];
    return $arr;
});
Route::get('/organizations/search', function (Request $request){
    $arr = json_decode(file_get_contents('../mock_data/mock-organization-data.json'), true);
    $arr = $arr['searchUsers'];
    return $arr;
});
// mine
Route::get('/mine/user', function (Request $request){
    $arr = json_decode(file_get_contents('../mock_data/mock-mine-data.json'), true);
    $arr = $arr['user'];
    return $arr;
});
Route::get('/mine/orgs', function (Request $request){
    $arr = json_decode(file_get_contents('../mock_data/mock-mine-data.json'), true);
    $arr = $arr['orgsSelector'];
    return $arr;
});
Route::post('/mine/exitOrg', function (Request $request){
    return ($request);
});
Route::post('/mine/finishEdit', function (Request $request){
    return ($request);
});


// work
Route::get('/work_organizations', function (Request $request){
	$arr = json_decode(file_get_contents('../mock_data/mock-works-data.json'), true);
	$arr = $arr['organizations'];
    return $arr;
});
Route::get('/workgroups', function (Request $request){
    $arr = json_decode(file_get_contents('../mock_data/mock-works-data.json'), true);
    $arr = $arr['workgroups'][$request->id - 1]['workgroup'];
    return $arr;
});
Route::get('/targets', function (Request $request){
    $arr = json_decode(file_get_contents('../mock_data/mock-works-data.json'), true);
    $arr = $arr['targets'];
    return $arr;
});
Route::post('/submit', function (Request $request){
    return ($request);
});
Route::get('/messages', function (Request $request){
    $arr = json_decode(file_get_contents('../mock_data/mock-data.json'), true);
    $arr = $arr['messages'];
    return $arr;
});

/*
    Production Environment API
*/

// Route::group(['middleware' => ['web', 'wechat.oauth:dudu, snsapi_base', 'set_user_id']], function () {
Route::group(['middleware' => env('APP_ENV')==='local'?['web', 'set_custom_user_id']:['web', 'wechat.oauth:dudu, snsapi_base', 'set_user_id']], function () {
    // 测试用接口
    Route::get('/test', 'TestController@index')->name('test');
    Route::post('/token/verify', 'TokenController@verifyToken')->name('token.verify');

    // 通用模块 By ChoChik
    Route::prefix('file')->group(function () {
        Route::post('/upload','CommonController@upload')->name('file.upload');
        Route::get('/getimg','CommonController@getimg')->name('file.getimg');
        Route::post('/download','CommonController@download')->name('file.download');
        Route::post('/download_all','CommonController@downloadAll')->name('file.downloadAll');
        Route::post('/delete_attach','CommonController@deleteAttach')->name('file.deleteAttach');
    });

    // 工作模块 By ChoChik
    // 任务接口
    Route::prefix('task')->group(function () {
        // 获取当前任务下所有用户的转交记录
        Route::get('/worktransfres','TaskController@worktransfres')->name('task.worktransfres');

        // 删除当前任务的附件
        Route::post('/deltefile/{file_id}','TaskController@deletefile')->name('task.deltefile');

        // 查询任务数
        Route::post('/check_work','TaskController@checkwork')->name('task.checkwork');

        // 办结任务
        Route::post('/postwork','TaskController@setworkstatus')->name('task.setworkstatus');


        Route::get('/show','TaskController@show')->name('task.show');

        Route::post('/store','TaskController@store')->name('task.store');
        Route::get('/detail/{task_id}','TaskController@detail')->name('task.detail');
        Route::post('/sign','TaskController@sign')->name('task.sign');
        Route::post('/report','TaskController@report')->name('task.report');
        Route::post('/audit_report','TaskController@auditReport')->name('task.auditReport');
        Route::post('/add_flow_auditors','TaskController@addFlowAuditors')->name('task.addFlowAuditors');

        Route::post('/flow_audit','TaskController@flowAudit')->name('task.flowAudit');

        Route::post('/transfer','TaskController@transfer')->name('task.transfer');

        Route::post('/transfer_history','TaskController@transferHistory')->name('task.transferHistory');
        Route::post('/uploadUrl', 'UrlController@uploadUrl')->name('task.UploadUrl');
        Route::post('/lookupUrl', 'UrlController@lookupUrl')->name('task.lookupUrl');
        Route::post('/deleteUrl','UrlController@deleteUrl')->name('task.deleteUrl');
        Route::post('/editUrl', 'UrlController@editUrl')->name('task.editUrl');
        Route::post('/updateTaskId','UrlController@updateTaskId')->name('task.updateTaskId');
    });

    // 通知接口
    Route::prefix('notification')->group(function (){
        // 获取当前通知下所有用户转交记录
        Route::get('/notificationtransfres','NotificationController@notificationtransfres')->name('notification.notificationtransfres');

        Route::post('/postnotice','NotificationController@setnoticestatus')->name('notification.setworkstatus');


        Route::get('/show','NotificationController@show')->name('notification.show');
        Route::post('/store','NotificationController@store')->name('notification.store');
        Route::get('/detail/{notification_id}','NotificationController@detail')->name('notification.detail');
        Route::post('/sign','NotificationController@sign')->name('notification.sign');
        Route::post('/add_flow_auditors','NotificationController@addFlowAuditors')->name('notification.addFlowAuditors');
        Route::post('/flow_audit','NotificationController@flowAudit')->name('notification.flowAudit');
        Route::post('/transfer','NotificationController@transfer')->name('notification.transfer');
        Route::post('/transfer_history','NotificationController@transferHistory')->name('notification.transferHistory');
    });

    // 请示接口
    Route::prefix('ask')->group(function (){
        Route::get('/show','AskController@show')->name('ask.show');
        Route::post('/store','AskController@store')->name('ask.store');
        Route::get('/detail/{ask_id}','AskController@detail')->name('ask.detail');

        Route::post('/add_flow_auditors','AskController@addFlowAuditors')->name('ask.addFlowAuditors');
        Route::post('/flow_audit','AskController@flowAudit')->name('ask.flowAudit');
    });


    // 消息模块接口 By Kingsley
    Route::prefix('message')->group(function () {
        Route::get('/msg/{message}', 'MessageController@getMsg')->name('message.getMsg');
        Route::get('/unread', 'MessageController@unread')->name('message.unread');
        Route::get('/read', 'MessageController@read')->name('message.read');
        Route::get('/read/after/{msgId}/limit/{limit}', 'MessageController@readByParams')->name('message.readByParams');
        // 获取当前用户为发送人的信息
        Route::get('/getsend', 'MessageController@getsend')->name('message.getsend');

        Route::get('/setread/{msgId}', 'MessageController@setRead')->name('message.read');

        Route::get('/allsetread','MessageController@allsetRead')->name('message.allread');
    });

    // 日程模块接口 By Kingsley
    Route::prefix('schedule')->group(function () {
        Route::get('/', 'ScheduleController@index')->name('schedule.index');
        Route::get('/detail/{id}', 'ScheduleController@getDetailById')->name('schedule.getDetailById');
        Route::get('/ymd/{ymd}', 'ScheduleController@getByDay')->name('schedule.getByDay');
        Route::get('/remind/ymd/{ymd}', 'ScheduleController@getRemindByDay')->name('schedule.getRemindByDay');
        Route::get('/getmemorials', 'ScheduleController@getMemorialDay')->name('schedule.getMemorialDay');
        Route::get('/eventdays/from/{from}/to/{to}', 'ScheduleController@getEventDays')->name('schedule.getEventDays');

        Route::post('/create', 'ScheduleController@create')->name('schedule.create');
        Route::post('/update/{id}', 'ScheduleController@update')->name('schedule.update');
        Route::get('/delete/{id}', 'ScheduleController@delete')->name('schedule.delete');
        // 创建纪念日
        Route::post('/create_memorial_day', 'ScheduleController@createMemorialDay')->name('schedule.create_memorial');
        Route::post('/update_memorial_day', 'ScheduleController@updateMemorialDay')->name('schedule.update_memorial');
        // 群组共享日程
        Route::post('/group_schedule', 'ScheduleController@groupSchedule')->name('schedule.groupSchedule');
    });

    // 申请模块接口 By DEEP, Kingsley, Houlong
    Route::prefix('my/apply')->group(function () {
        Route::post('/org/{org}/role/{role}/joinOrg', 'ApplyController@joinOrgWithLogin')->name('my.apply.joinOrgWithLogin');
        Route::post('/signOrg', 'ApplyController@signOrgWithLogin')->name('my.apply.signOrgWithLogin');
        Route::post('/invite_join_group', 'ApplyController@inviteJoinGroup')->name('my.apply.inviteJoinGroup');
        // By ChoChik
        Route::get('/invite_info/{dept}/{inviter_id}/{role_id}', 'ApplyController@getInviteInfo')->name('user.getInviteInfo');
    });

    // 审批模块接口 By DEEP, Kingsley
    Route::prefix('my/approval')->group(function () {
        // 拿到流转审批记录
        Route::post('/approvaldetails', 'ApprovalController@approvaldetails')->name('my.approval.approvaldetails');

        Route::post('/message/{message}/grant/joinOrg', 'ApprovalController@grantJoinOrgWithLogin')->name('my.approval.grantJoinOrgWithLogin');
        // By ChoChik
        Route::post('/message/{message}/reject/joinOrg', 'ApprovalController@rejectJoinOrgWithLogin')->name('my.approval.rejectJoinOrgWithLogin');
        Route::post('/grant/signOrg/{orgId}', 'ApprovalController@grantSignOrgWithLogin')->name('my.approval.grantSignOrgWithLogin');
        Route::post('/reject/signOrg/{orgId}', 'ApprovalController@rejectSignOrgWithLogin')->name('my.approval.rejectSignOrgWithLogin');
        //同意进入群组
        Route::post('/grant/join_group', 'ApprovalController@grantJoinGroupWithLogin')->name('my.approval.grantJoinGroupWithLogin');
    });

    // 机构模块接口 By DEEP
    Route::prefix('org')->group(function () {
        Route::get('/', 'OrgController@index')->name('org.index');
        // 获取机构信息,并按部门分类成员

        // 获取某用户下所有机构 或查询当前机构下有无某用户,传入机构ID跟用户ID
        Route::get('/getallorgs', 'OrgController@getallorgs')->name('org.getallorgs');
        // 检查验证码
        Route::post('/check_sms_code', 'OrgController@checkSmsCode')->name('org.checkSmsCode');
        Route::get('/{org}', 'OrgController@show')->name('org.show');
        // 注册机构审核接口
        Route::post('/store', 'OrgController@store')->name('org.store');
        // 注册结构直通接口
        Route::post('/storeagree', 'OrgController@storeagree')->name('org.storeagree');
        // 拒绝用户加入机构申请
        Route::post("/refused",'OrgController@refused')->name('org.refused');
        Route::get('/getByCode/{code}', 'OrgController@getByCode')->name('org.getByCode');
        Route::get('/getByCodeMergeOrg/{code}', 'OrgController@getByCodeMergeOrg')->name('org.getByCodeMergeOrg');
        // By Houlong
        Route::post('/leave/{org}', 'OrgController@leaveOrg')->name('org.leaveOrg');
        Route::post('/update/{org}', 'OrgController@updateOrg')->name('org.updateOrg');
        // By ChoChik
        Route::get('/users_by_depts/{org}', 'OrgController@getUsersByDepts')->name('user.getUsersByDepts');
        // 修改超管密码 by cbf
        Route::post('/change_password', 'OrgController@changePassword')->name('org.changePassword');
        // 发送短信验证码
        Route::post('/send_sms_code', 'OrgController@sendSmsCode')->name('org.sendSmsCode');

        // 剔除机构下部门中用户
        Route::post('/exitOrg/{exit_user_id}/org_id/{org_id}', 'OrgController@exitOrg')->name('org.exitOrg');

        Route::post('/exit_org', 'OrgController@exitOrg')->name('org.exitOrg');
        // search功能
        Route::post('/search', 'OrgController@search')->name('org.search');
        Route::post('/merge_org', 'OrgController@MergeOrg')->name('org.mergeOrg');
        Route::get('/merge_org_msg/{org}', 'OrgController@MergeOrgMsg')->name('org.mergeOrgMsg');
        Route::post('/merge_org_reply', 'OrgController@MergeOrgReply')->name('org.mergeOrgReply');
        Route::post('/add_ask_type', 'OrgController@addAskType')->name('org.addAskType');
    });

    Route::prefix('my/org')->group(function () {
        Route::get('/', 'OrgController@indexWithLogin')->name('my.org.index');
        Route::get('/{org}', 'OrgController@indexWithLogin')->name('my.org.indexOne');
        Route::get('/{org}/change_default', 'OrgController@changeDefault')->name('my.org.changeDefault');
        Route::get('/{org}/user', 'OrgController@getOrgUsers')->name('my.org.getOrgUsers');
    });

    // 部门模块接口 By DEEP, Houlong
    Route::prefix('dept')->group(function () {
        Route::get('/group/{group}', 'DeptController@getByGroup')->name('dept.getByGroup');
        Route::post('/store', 'DeptController@store')->name('dept.store');
        //获取机构信息
        Route::get('/{orgsid}', 'DeptController@getOrg')->name('dept.getOrg');
        //获取该机构下所有部门and单位
        Route::get('/depts/{orgsid}', 'DeptController@geDepts')->name('dept.getDepts');

        // 根据机构获取部门
        Route::get('/org/{org}', 'DeptController@getByOrg')->name('dept.getByOrg');
        Route::post('/batch_change', 'DeptController@batchChange')->name('dept.batchChange');
        Route::post('/modify_name', 'DeptController@modifyName')->name('dept.modifyName');

        //删除指定机构下指定部门
        Route::post('/delete_depts', 'DeptController@deleteDepts')->name('dept.deleteDepts');

    });
    Route::prefix('my/dept')->group(function () {
    });

    // 群组模块接口 By DEEP, Kingsley, Houlong
    Route::prefix('group')->group(function () {
        Route::post('/store', 'GroupController@store')->name('group.store');

        // 申请加入群组功能
        Route::post('/addgroup/{groupid}', 'GroupController@addgroup')->name('group.addgroup');

        // 同意用户加入群组
        Route::post('/agreedjoin', 'GroupController@agreedjoin')->name('group.agreedjoin');
        // 拒绝用户加入群组
        Route::post('/refusedjoin', 'GroupController@refusedjoin')->name('group.refusedjoin');
        // 群主更改群名
        Route::post('editGroupName','GroupController@editGroupName')->name('group.editGroupName');


        // 获取机构下所有群组
        Route::get('/getallgroups/{orgsid}', 'GroupController@getallgroups')->name('group.getallgroups');

        Route::get('/user/{group}', 'GroupController@showUserByGroup')->name('group.showUserByGroup');

        Route::get('/org/{org}', 'GroupController@getByOrg')->name('group.getByOrg');

        Route::get('/info/{group}', 'GroupController@getInfo')->name('group.getInfo');

        Route::post('/leave_group', 'GroupController@leaveGroup')->name('group.leaveGroup');
        Route::get('leave_group_detail', 'GroupController@leaveGroupDetail')
                ->name('group.leaveGroupDetail');
    });
    Route::prefix('my/group')->group(function () {
        Route::get('/org/{org}', 'GroupController@getByOrgWithLogin')->name('my.group.getByOrg');
        Route::post('/org/{org}', 'GroupController@createWithLogin')->name('my.group.create');
    });



    // 用户模块接口 By DEEP, Houlong
    Route::prefix('user')->group(function () {
        Route::get('/{user}', 'UserController@show')->name('user.show');
        Route::get('/dept/{dept}', 'UserController@getByDept')->name('user.getByDept');
        Route::get('/group/{group}', 'UserController@getByGroup')->name('user.getByGroup');
        Route::get('/org/{org}/flag/{flag}', 'UserController@getUserByOrg')->name('user.getUserByOrg');
        Route::get('/group/{group}/groupByDept', 'UserController@getByGroupGroupByDept')->name('user.getByGroupGroupByDept');
        Route::get('/group/{group}/without_join', 'UserController@getByGroupWithoutJoin')->name('
            user.getByGroupWithoutJoin');
        Route::get('/org/{org}', 'UserController@getByOrg')->name('user.getByOrg');

    });

    Route::prefix('my/user')->group(function () {
        Route::get('/', 'UserController@showWithLogin')->name('my.user.showWithLogin');
        Route::get('/searchByName', 'UserController@searchByNameWithLogin')->name('my.user.searchByNameWithLogin');
        Route::get('/tel_code', 'UserController@getTelCodeWithLogin')->name('my.user.getTelCodeWithLogin');
        Route::match(['put', 'patch'], '/', 'UserController@updateWithLogin')->name('my.user.updateWithLogin');
        Route::match(['put', 'patch'], '/tel', 'UserController@updateTelWithLogin')->name('my.user.updateTelWithLogin');
        Route::get('/check_is_like', 'UserController@checkIsLike')->name('user.checkIsLike');
        Route::post('/update_info', 'UserController@updateInfo')->name('my.user.updateInfo');
    });

    Route::prefix('role')->group(function () {
        Route::get('/get', 'RoleController@getByType')->name('role.getByType');
        Route::post('/batch_change', 'RoleController@batchChange')->name('role.batchChange');
    });

    // 讨论板接口
    Route::prefix('msgboard')->group(function () {
        Route::post('/create', 'MsgBoardController@create')->name('msgboard.create');
    });

    // 反馈接口
    Route::prefix('feedback')->group(function() {
        Route::post('/create', 'FeedbackController@create')->name('feedback.create');
        Route::get('/detail/{id}', 'FeedbackController@detail')->name('feedback.detail');
        Route::get('/list', 'FeedbackController@index')->name('feedback.index');
        //  已修复反馈消息推送 和 状态修改
        Route::post('/update/{id}', 'FeedbackController@update')->name('feedback.update');

    });

    // admin
    Route::prefix('admin')->group(function() {
        Route::post('/postArticles', 'Admin\AdminController@changeArticles')->name('admin.addArticles');
        Route::get('/getAcricles', 'Admin\AdminController@getAcricles')->name('admin.getAcricles');

    });


    Route::prefix('test')->group(function() {
        Route::get('/test', 'TestController@test')->name('test.test');
    });

});
