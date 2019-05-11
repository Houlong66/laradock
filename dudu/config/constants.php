<?php
define('SITE_URL_INDEX','http://ksh.fun/');
define('MESSAGE_BOX','http://ksh.fun/#/messages');

/**
 * 微信模板消息相关参数
 */

/**
 * 工作提醒
 * keyword1 : 任务编号（任务名称）
 * keyword2 : 任务类型
 * keyword3 : 任务描述
 */
define('TPL_WORK_SEND', getTPL('TPL_WORK_SEND'));


/** 申请待审核通知
 *  keyword1 : 申请人
 *  keyword2 : 申请项目
 *  keyword3 : 申请时间
 *  keyword4 : 申请状态
 */
define('TPL_APPLY_WAIT_AUDIT', getTPL('TPL_APPLY_WAIT_AUDIT'));

/** 审核结果通知
 *  keyword1： 审核事项
 *  keyword2： 审核结果
 */
define('TPL_APPLY_AUDIT_RESULT', getTPL('TPL_APPLY_AUDIT_RESULT'));

/** 收到用户反馈
 *  keyword1 ： 用户姓名
 *  keyword2 : 手机号
 */
define('TPL_FEEDBACK', getTPL('TPL_FEEDBACK'));

/** 意见建议处理提醒
 *  keyword1 ： 用户姓名
 *  keyword2 : 手机号
 */
define('TPL_FEEDBACK_RESULT', getTPL('TPL_FEEDBACK_RESULT'));

/** 消息发送通知
 *  keyword1 ： 标题
 *  keyword2 : 状态
 *  keyword3 : 时间
 */
define('TPL_MESSAGES_RESULT', getTPL('TPL_MESSAGES_RESULT'));


/** 模板消息文案颜色预设值 **/
define('COL_RED','#ff4039');
define('COL_BLUE','#3f7fff');
define('COL_GRAY','#cccccc');
define('COL_LIGHT_BLACK','#333333');

// 任务
define('TPL_TASK_NEW', 'NcwABppAsnitVRW36vPho_macpQDdEHpOG1iJ2yS-6Q');
define('TPL_TASK_REPORT_WAIT_AUDIT', 'rjouRULQezHonbQn91tljAZeJF05LFgfiXGcSYSuYfc');
define('TPL_TASK_REPORT_AUDIT_RESULT', 'AaAxnMxqTRu5L3WVIvBMid4M7ItSg49AoOu2d8PDums');
define('TPL_TASK_NEW_WAIT_AUDIT', 'M_7MEMaUfIOllXO2H3jIVtj_eKQef0_jaK0sbH2MptE');
define('TPL_TASK_NEW_AUDIT_RESULT', 'ZW28v7qSWq2gwGqnZUJtNl3w1SvVxvYg3ZF9Balc53s');
// 通知
define('TPL_NOTICE_NEW', 'aV1IB0i5S3dlxNBYbsRDH5fIKlp1eMOFOxp76zpdoo0');
define('TPL_NOTICE_NEW_WAIT_AUDIT', 'bPDtDAzmrD3yY0E6ENVSN1L5BFvA7usqOeFo8TOoRIY');
define('TPL_NOTICE_NEW_AUDIT_RESULT', 'snqhgvYKHXuNGgMqnUdBxIco97JHVxRo-dMpNyAFMpw');
// 请示
define('TPL_ASK_NEW_WAIT_AUDIT', '4RejfWxlbiHx2n7L3S4K7FRnKQlvcTuxYBQo2QT57Hk');
define('TPL_ASK_NEW_AUDIT_RESULT', 'YmO8l_5BRwGiE199nHYb1echPQC_U7cG0Czt3RB43g8');


function getTPL($tpl_name){
    return env('APP_ENV') === 'local' ? env("DEV_{$tpl_name}") : env($tpl_name);
}



/*
    xjb约定：

    ST abbr. STATUS 状态
    TP abbr. TYPE 类型
    SP abbr. SUBTYPE 子类型

    TSK abbr. TASK 任务
    NTC abbr. NOTICE 通知
    QST abbr. REQUEST 请示
    SCH abbr. SCHEDULE 日程
    USR abbr. USER 用户
    SYS abbr. SYSTEM 系统

    CA abbr. Circulation Approval 流转审批

    REQ abbr. REQUEST 申请
    RES abbr. RESULT 结果
    GRP abbr. GROUP 群主
    EXC abbr. EXCHANGE 交换
*/



// 消息表status字段：
define('MSG_ST_UNREAD', 0); // 未读
define('MSG_ST_READ', 1); // 已读
define('MSG_ST_TODO', 2); // 待办



// 消息表type字段：
define('MSG_TP_TSK', 1); // 工作（任务）为1
define('MSG_TP_NTC', 2); // 工作（通知）为2
define('MSG_TP_QST', 3); // 工作（请示）为3
define('MSG_TP_SCH', 4); // 日程为4
define('MSG_TP_ORG', 5); // 组织为5
define('MSG_TP_USR', 6); // 用户为6
define('MSG_TP_SYS', 7); // 系统为7



// 消息表subtype字段：
// type1 工作（任务）
define('MSG_SP_TSK_NEW', 1); // 新任务提醒为1
define('MSG_SP_TSK_PENDING', 2); // 任务待审核为2
define('MSG_SP_TSK_RESULT', 3); // 任务审核结果为3
define('MSG_SP_TSK_CA_PENDING', 4); // 任务流转审批待审核为4
define('MSG_SP_TSK_CA_RESULT', 5); // 任务流转审批审核结果为5
define('MSG_SP_TSK_EXPIRED', 6); // 任务到期提醒为6

// type2 工作（通知）
define('MSG_SP_NTC_CA_NEW', 1); // 新通知提醒为１
define('MSG_SP_NTC_CA_PENDING', 2); // 流转审批待审核为2
define('MSG_SP_NTC_CA_RESULT', 3); // 流转审批审核结果为3

// type3 工作（请示）
define('MSG_SP_QST_PENDING', 1); // 待处理请示为1
define('MSG_SP_QST_PROCESSED', 2); // 已处理请示为2

// type4 日程
define('MSG_SP_SCH_NEW', 1); // 新增日程提醒为1
define('MSG_SP_SCH_EXPIRED', 2); // 日程到期提醒为2
define('MSG_SP_MEM_EXPIRED', 3); // 纪念日提醒 3

// type5 组织
define('MSG_SP_ORG_RES', 1); // 机构申请结果消息为1
define('MSG_SP_ORG_DOCK_REQ', 2); // 机构对接申请消息为2
define('MSG_SP_ORG_DOCK_RES', 3); // 机构对接结果消息为3
define('MSG_SP_ORG_TRANS_REQ', 4); // 机构转移申请消息为4
define('MSG_SP_ORG_TRANS_RES', 5); // 机构转移结果消息为5
define('MSG_SP_ORG_DELETE_RES', 6); // 移出机构结构消息为6


// type6 用户
define('MSG_SP_USR_JOIN_REQ', 1); // 加入机构申请为1
define('MSG_SP_USR_JOIN_RES', 2); // 加入机构结果为2
define('MSG_SP_USR_GRP_REQ', 3);  // 加入群组申请为3, 被动邀请加入
define('MSG_SP_USR_GRP_RES', 4);  // 加入群组结果为4, 被动邀请结果
define('MSG_SP_USR_EXC_REQ', 5);  // 交换名片申请为5
define('MSG_SP_USR_EXC_RES', 6);  // 名片交换结果为6
define('MSG_SP_USR_DIS', 7); // 讨论版的消息为7
define('MSG_SP_USR_APPLY_GRP_REQ', 9);          // 加入群组申请9, 主动申请加入
define('MSG_SP_USR_APPLY_GRP_AGREE_RES', 10);   // 加入群组申请10, 主动申请结果, 同意
define('MSG_SP_USR_APPLY_GRP_REJECT_RES', 11);  // 加入群组申请11, 主动申请结果, 拒绝
define('MSG_SP_USR_JOIN_ORG_REJCET_RES',12);    // 拒绝申请加入机构
// type7 系统
// 【默认值为0】



/**
 * 角色表对应id：
 * SYS abbr. SYSTEM 系统
 * GRP abbr. GROUP 群组
 *
 * SUPER 超级管理员
 * SYSTEM 系统管理员
 * TASK 任务管理员
 * INNER 内部成员
 * NONE 未加入机构的用户
 *
 * CREATE 群组创建人
 * ASSIGN 任务发放人
 * SIGN 任务签收人
 * DO 部门/单位领导
 * NONE 未加入群组的用户
 */
define('ROLE_SYS', 1); // 系统
define('ROLE_GRP', 2); // 群组

define('ROLE_SYS_SUPER', 1); // 超级管理员
define('ROLE_SYS_SYSTEM', 2); // 系统管理员
define('ROLE_SYS_TASK', 3); // 任务管理员
define('ROLE_SYS_INNER', 4); // 内部成员

define('ROLE_GRP_CREATE', 5); // 群组创建人
define('ROLE_GRP_ASSIGN', 6); // 任务发放人
define('ROLE_GRP_SIGN', 7); // 任务签收人
define('ROLE_GRP_DO', 8); // 部门/单位领导


define('ROLE_GRP_SHARED', 9); // 日程共享人
define('ROLE_GRP_TRACKING', 10); // 日程跟踪人



/**
 * 机构表状态
 */
define('ORG_ST_DISABLED', 0); // 禁用
define('ORG_ST_ENABLED', 1); // 启用
define('ORG_ST_PENDING', 2); // 待审核


// 群组类型、状态
define('GRP_TP_WORK', 0); // 工作通知群
define('GRP_TP_SCH', 1); // 日程共享群

define('GRP_ST_DISABLED', 0); // 停用
define('GRP_ST_ENABLED', 1); // 启用


// 部门等级、状态
define('DPT_LV_MAIN', 0); // 本级部门
define('DPT_LV_SUB', 1); // 下级单位

define('DPT_ST_DISABLED', 0); // 停用
define('DPT_ST_ENABLED', 1); // 启用


// 反馈推送用户id
define('MAINTAINER', [1,2]);




/* 显示在页面的数值与字符串的对应匹配 */

function orgStatus($status) {
    return [
        ORG_ST_DISABLED => '已禁用',
        ORG_ST_ENABLED => '已启用',
        ORG_ST_PENDING => '待审核'
    ][$status];
}

function userSex($sex) {
    return [
        1 => '男',
        2 => '女',
        0 => '未设置'
    ][$sex];
}

function groupType($type) {
    return [
        GRP_TP_WORK => '工作通知群',
        GRP_TP_SCH => '日程共享群'
    ][$type];
}

function groupStatus($status) {
    return [
        GRP_ST_DISABLED => '已停用',
        GRP_ST_ENABLED => '已启用'
    ][$status];
}

function deptLevel($level) {
    return [
        DPT_LV_MAIN =>  '本级部门',
        DPT_LV_SUB =>  '下级单位'
    ][$level];
}

function deptStatus($status) {
    return [
        DPT_ST_DISABLED =>  '已停用',
        DPT_ST_ENABLED =>  '已启用'
    ][$status];
}


/**
 * 处理微信的URL，防止授权后丢失跳转链接
 * @param String $url 传入URL
 * @return String $wx_url 微信专属处理的url
 */
function wxUrlHandle($url){
    // HOST 部分
    $site_host = env('SITE_HOST');
    $wx_url = $site_host . $url;
    return $wx_url;
}
