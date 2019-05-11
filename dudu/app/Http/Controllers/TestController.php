<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Schedule as ScheduleModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\TaskUser;
use App\Libraries\LunarSolarConverter;
use App\Libraries\Solar;

class TestController extends Controller
{
    public function index()
    {
        $timezone = 'America/New_York';
        $startDate = new \DateTime('2013-06-12 20:00:00', new \DateTimeZone($timezone));
        $endDate = new \DateTime('2013-06-14 20:00:00', new \DateTimeZone($timezone));
        $rule = new \Recurr\Rule('FREQ=MONTHLY;COUNT=5', $startDate, $endDate, $timezone);
        // dd($rule);
        echo $rule->getString();

        // $rule = (new \Recurr\Rule)
        //     ->setStartDate($startDate)
        //     ->setTimezone($timezone)
        //     ->setFreq('DAILY')
        //     ->setByDay(['MO', 'TU'])
        //     ->setUntil(new \DateTime('2017-12-31'))
        // ;

        echo $rule->getString(); //FREQ=DAILY;UNTIL=20171231T000000;BYDAY=MO,TU
    }



    public function test()
    {
        // 1：未签收；2：未上报
            $tasks = TaskUser::whereIn('status', [1, 2])
                ->whereDate('report_deadline', date('Y-m-d'))
                ->get();

            // 获取所有相关用户
            $user_id_list = [];
            foreach ($tasks as $task) {
                $user_id_list[] = $task->user_id;
            }
            $users = User::whereIn('id', $user_id_list)->get();
            $smart_users = [];
            foreach ($users as $k => $u) {
                $smart_users[$u->id] = $u;
            }

//            dd($tasks->toArray());

            // 循环发通知提醒
            foreach ($tasks as $task) {
//                dd(strtotime(date('Y-m-d H:i:s')));
//                dd(strtotime($task->report_deadline));
//                dd($task->report_deadline);
                if(strtotime($task->report_deadline) >= strtotime(date('Y-m-d H:i:s'))) {
                    $title = '任务即将逾期提醒';
                    $content = '您有任务今天内即将截止上报，请点击查看详情';
                }else{
                    $title = '任务已逾期提醒';
                    $content = '您有任务已逾期，请尽快上报，点击查看详情';
                }

                $message = (object)[];
                $message->title = $title;
                $message->content = $content;
                $message->type = MSG_TP_TSK;
                $message->subtype = MSG_SP_TSK_EXPIRED;
                $message->user_id = $task->user_id;
                $message->params = json_encode([
                    'id' => $task->task_id,
                    'dept_id' => $task->dept_id
                ]);

                // 微信模板消息所需参数
                $message->openid = $smart_users[$task->user_id]->openid;
                $message->tpl = TPL_MESSAGES_RESULT;
                $message->first = $content;
                $message->keyword1 = $title;
                $message->keyword2 = $task->status === 1 ? "任务未签收" : "任务未上报";
                $message->keyword3 = "[上报截止时间]：{$task->report_deadline}";
                MessageController::create($message, true);
            }
//            $schedules = ScheduleModel::with([
//                'reminds' => function ($query) {
//                    $query->where('remind_at', date('Y-m-d H:i:00'));
//                }
//            ])->where('type', '!=', 3)->get();
//
//
//
//            // 获取所有相关用户
//            $user_id_list = [];
//
//            foreach ($schedules as $schedule) {
//                $user_id_list[] = $schedule->user_id;
//            }
//
//            $users = User::whereIn('id', $user_id_list)->get();
//
//            $smart_users = [];
//
//            foreach ($users as $k => $u) {
//                $smart_users[$u->id] = $u;
//            }
//
//            foreach ($schedules as $schedule) {
//
//                if (!$schedule->reminds->isEmpty()) {
//
//                    $message = (object)[];
//                    $message->title = '日程提醒';
//                    $message->content = '您有即将开始的日程，请点击查看详情';
//                    $message->type = MSG_TP_SCH;
//                    $message->subtype = MSG_SP_SCH_EXPIRED;
//                    $message->user_id = $schedule->user_id;
//                    $message->params = json_encode([
//                        'id' => $schedule->id
//                    ]);
//
//                    $start_time = $schedule->start_at === Null ? $schedule->reminds[0]->remind_at : $schedule->start_at;
//
//                    // 微信模板消息所需参数
//                    $message->openid = $smart_users[$schedule->user_id]->openid;
//                    $message->tpl = TPL_MESSAGES_RESULT;
//                    $message->first = "日程提醒";
//                    $message->keyword1 = "{$schedule->name}";
//                    $message->keyword2 = "即将开始";
//                    $message->keyword3 = "[开始时间]：{$start_time}";
//
//                    MessageController::create($message, true);
//
//                }
//            }
//
//            // 纪念日
//            $memorials = ScheduleModel::with([
//                'reminds' => function ($query) {
//                    $query->where('remind_at', date('Y-m-d H:i:00'));
//                }
//            ])->where('type', 3)->where('is_solar', 1)->get();
//
//            // 农历
//            $solar = new Solar();
//            $solar->solarYear = date('Y');
//            $solar->solarMonth = date('m');
//            $solar->solarDay = date('d');
//            $lunar = LunarSolarConverter::SolarToLunar($solar);
//            $lunar = LunarSolarConverter::lunarToDate($lunar);
//            $lunar = date($lunar . ' H:i:00');
//            $lunar = ScheduleModel::with([
//                'reminds' => function ($query) use ($lunar) {
//                    $query->where('remind_at', $lunar);
//                }
//            ])->where('type', 3)->where('is_solar', 0)->get();
//
//            $memorials = $memorials->merge($lunar);
//
//            // 获取所有相关用户
//            $user_id_list = [];
//            foreach ($memorials as $memorial) {
//                $user_id_list[] = $memorial->user_id;
//            }
//            $users = User::whereIn('id', $user_id_list)->get();
//            $smart_users = [];
//            foreach ($users as $k => $u) {
//                $smart_users[$u->id] = $u;
//            }
//
//            foreach ($memorials as $memorial) {
//                if (!$memorial->reminds->isEmpty()) {
//                    $message = (object)[];
//                    $message->title = '纪念日提醒';
//                    $message->content = '您有即将开始的纪念日，请点击查看详情';
//                    $message->type = MSG_TP_SCH;
//                    $message->subtype = MSG_SP_MEM_EXPIRED;
//                    $message->user_id = $memorial->user_id;
//                    $message->params = json_encode([
//                        'id' => $memorial->id
//                    ]);
//                    // 微信模板消息所需参数
//                    $message->openid = $smart_users[$memorial->user_id]->openid;
//                    $message->tpl = TPL_MESSAGES_RESULT;
//                    $message->first = "纪念日提醒";
//                    $message->keyword1 = "{$memorial->name}";
//                    $message->keyword2 = "即将开始";
//                    $message->keyword3 = "[开始时间]：{$memorial->start_at}";
//                    MessageController::create($message, true);
//                }
//            }


            // 任务提醒
            $tasks = TaskUser::with(['task'])->where('item_type', 0)->whereIn('status', [1, 2])
                ->where(function ($query) {
                    $query->where('report_deadline', date('Y-m-d H:i:00', strtotime('+1 day')))
                        ->orWhere('report_deadline', date('Y-m-d H:i:00', strtotime('+3 hours')));
                })->get();


            $check_id = [];

            foreach ($tasks as $v){

             if (!in_array($v->task_id,$check_id)){

                 $check_id[] = $v->task_id;

                 $test =  TaskUser::with('user')->where([
                     ['item_type',3],
                     ['task_id',$v->task_id]
                 ])->get();

                 if (count($test) > 0 ){
                     $v->supervision = $test;
                 }
             }

            }

            // 获取所有相关用户
            $user_id_list = [];

            foreach ($tasks as $task) {
                $user_id_list[] = $task->user_id;
            }

            $users = User::whereIn('id', $user_id_list)->get();

            $smart_users = [];

            foreach ($users as $k => $u) {
                $smart_users[$u->id] = $u;
            }


            foreach ($tasks as $task) {

                $deadline = '';

                if ($task->report_deadline == date('Y-m-d H:i:00', strtotime('+1 day'))) {
                    $deadline = "24";
                } elseif ($task->report_deadline == date('Y-m-d H:i:00', strtotime('+3 hours'))) {
                    $deadline = "3";
                }

                $message = (object)[];
                $message->title = '任务到期提醒';
                $message->content = '您的任务'. $task->task->title .'将在' . $deadline . "小时后到期,请注意上报";
                $message->type = MSG_TP_TSK;
                $message->subtype = MSG_SP_TSK_EXPIRED;
                $message->user_id = $task->user_id;
                $message->params = json_encode([
                    'id' => $task->task_id,
                    'dept_id' => $task->dept_id
                ]);

                // 微信模板消息所需参数
                $message->openid = $smart_users[$task->user_id]->openid;
                $message->tpl = TPL_MESSAGES_RESULT;
                $message->first = '您的任务'. $task->task->title .'将在' . $deadline . "小时后到期,请注意上报";
                $message->keyword1 = '任务到期提醒';
                $message->keyword2 = $task->status === 1 ? "任务未签收" : "任务未上报";
                $message->keyword3 = "[上报截止时间]：{$task->report_deadline}";
                MessageController::create($message, true);

                // 监督人发送消息
                if (isset($task->supervision)){
                    foreach ($task->supervision as $v){
                        $message = (object)[];
                        $message->title = '监督任务到期提醒';
                        $message->content = '您监督的任务'. $task->task->title .'将在' . $deadline . "小时后到期,请注意上报";
                        $message->type = MSG_TP_TSK;
                        $message->subtype = MSG_SP_TSK_EXPIRED;
                        $message->user_id = $v->user_id;
                        $message->params = json_encode([
                            'id' => $task->task_id,
                            'dept_id' => $task->dept_id
                        ]);

                        // 微信模板消息所需参数
                        $message->openid = $v->user->openid;
                        $message->tpl = TPL_MESSAGES_RESULT;
                        $message->first = '您监督的任务'. $task->task->title .'将在' . $deadline . "小时后到期,请注意上报";
                        $message->keyword1 = '监督任务到期提醒';
                        $message->keyword2 = $task->status === 1 ? "任务未签收" : "任务未上报";
                        $message->keyword3 = "[上报截止时间]：{$task->report_deadline}";
                        MessageController::create($message, true);
                    }
                }


            }



        // 任务逾期提醒
        // 1：未签收；2：未上报
//        $tasks = TaskUser::whereIn('status', [1, 2])
//            ->whereDate('report_deadline', date('Y-m-d'))
//            ->get();
//
//        // 获取所有相关用户
//        $user_id_list = [];
//        foreach ($tasks as $task) {
//            $user_id_list[] = $task->user_id;
//        }
//        $users = User::whereIn('id', $user_id_list)->get();
//        $smart_users = [];
//        foreach ($users as $k => $u) {
//            $smart_users[$u->id] = $u;
//        }
//
//        // 循环发通知提醒
//        foreach ($tasks as $task) {
//            $message = (object)[];
//            $message->title = '任务即将逾期提醒';
//            $message->content = '您有任务今天内即将截止上报，请点击查看详情';
//            $message->type = MSG_TP_TSK;
//            $message->subtype = MSG_SP_TSK_EXPIRED;
//            $message->user_id = $task->user_id;
//            $message->params = json_encode([
//                'id' => $task->task_id,
//                'dept_id' => $task->dept_id
//            ]);
//
//            // 微信模板消息所需参数
//            $message->openid = $smart_users[$task->user_id]->openid;
//            $message->tpl = TPL_MESSAGES_RESULT;
//            $message->first = "您有任务今天内即将截止上报，请注意上报";
//            $message->keyword1 = "任务即将逾期提醒";
//            $message->keyword2 = $task->status === 1 ? "任务未签收" : "任务未上报";
//            $message->keyword3 = "[上报截止时间]：{$task->report_deadline}";
//            MessageController::create($message, true);
//        }
//

        // 日程提醒
//        $schedules = ScheduleModel::with([
//            'reminds' => function ($query) {
//                $query->where('remind_at', date('Y-m-d H:i:00'));
//            }
//        ])->where('type', '!=', 3)->get();
//
//        // 获取所有相关用户
//        $user_id_list = [];
//        foreach ($schedules as $schedule) {
//            $user_id_list[] = $schedule->user_id;
//        }
//        $users = User::whereIn('id', $user_id_list)->get();
//        $smart_users = [];
//        foreach ($users as $k => $u) {
//            $smart_users[$u->id] = $u;
//        }
//
//        foreach ($schedules as $schedule) {
//            if (!$schedule->reminds->isEmpty()) {
//                $message = (object)[];
//                $message->title = '日程提醒';
//                $message->content = '您有即将开始的日程，请点击查看详情';
//                $message->type = MSG_TP_SCH;
//                $message->subtype = MSG_SP_SCH_EXPIRED;
//                $message->user_id = $schedule->user_id;
//                $message->params = json_encode([
//                    'id' => $schedule->id
//                ]);
//
//                // 微信模板消息所需参数
//                $message->openid = $smart_users[$schedule->user_id]->openid;
//                $message->tpl = TPL_MESSAGES_RESULT;
//                $message->first = "日程提醒";
//                $message->keyword1 = "{$schedule->name}";
//                $message->keyword2 = "即将开始";
//                $message->keyword3 = "[开始时间]：{$schedule->start_at}";
//                MessageController::create($message, true);
//            }
//        }

//        // 纪念日
//        $memorials = ScheduleModel::with([
//            'reminds' => function ($query) {
//                $query->where('remind_at', date('Y-m-d H:i:00'));
//            }
//        ])->where('type', 3)->where('is_solar', 1)->get();
//
//        // 农历
//        $solar = new Solar();
//        $solar->solarYear = date('Y');
//        $solar->solarMonth = date('m');
//        $solar->solarDay = date('d');
//        $lunar = LunarSolarConverter::SolarToLunar($solar);
//        $lunar = LunarSolarConverter::lunarToDate($lunar);
//        $lunar = date($lunar . ' H:i:00');
//        $lunar = ScheduleModel::with([
//            'reminds' => function ($query) use ($lunar) {
//                $query->where('remind_at', $lunar);
//            }
//        ])->where('type', 3)->where('is_solar', 0)->get();
//
//        $memorials = $memorials->merge($lunar);
//
//        // 获取所有相关用户
//        $user_id_list = [];
//        foreach ($memorials as $memorial) {
//            $user_id_list[] = $memorial->user_id;
//        }
//        $users = User::whereIn('id', $user_id_list)->get();
//        $smart_users = [];
//        foreach ($users as $k => $u) {
//            $smart_users[$u->id] = $u;
//        }
//
//        foreach ($memorials as $memorial) {
//            if (!$memorial->reminds->isEmpty()) {
//                $message = (object)[];
//                $message->title = '纪念日提醒';
//                $message->content = '您有即将开始的纪念日，请点击查看详情';
//                $message->type = MSG_TP_SCH;
//                $message->subtype = MSG_SP_MEM_EXPIRED;
//                $message->user_id = $memorial->user_id;
//                $message->params = json_encode([
//                    'id' => $memorial->id
//                ]);
//                // 微信模板消息所需参数
//                $message->openid = $smart_users[$memorial->user_id]->openid;
//                $message->tpl = TPL_MESSAGES_RESULT;
//                $message->first = "纪念日提醒";
//                $message->keyword1 = "{$memorial->name}";
//                $message->keyword2 = "即将开始";
//                $message->keyword3 = "[开始时间]：{$memorial->start_at}";
//                MessageController::create($message, true);
//            }
//        }

        // 任务提醒
//        $tasks = TaskUser::where('item_type', 0)->whereIn('status', [1, 2])
//            ->where(function ($query) {
//                $query->where('report_deadline', date('Y-m-d H:i:00', strtotime('+1 day')))
//                    ->orWhere('report_deadline', date('Y-m-d H:i:00', strtotime('+12 hours')))
//                    ->orWhere('report_deadline', date('Y-m-d H:i:00', strtotime('+6 hours')))
//                    ->orWhere('report_deadline', date('Y-m-d H:i:00', strtotime('+3 hours')))
//                    ->orWhere('report_deadline', date('Y-m-d H:i:00', strtotime('+1 hour')));
//            })->get();
//
//        // 获取所有相关用户
//        $user_id_list = [];
//        foreach ($tasks as $task) {
//            $user_id_list[] = $task->user_id;
//        }
//        $users = User::whereIn('id', $user_id_list)->get();
//        $smart_users = [];
//        foreach ($users as $k => $u) {
//            $smart_users[$u->id] = $u;
//        }
//
//        foreach ($tasks as $task) {
//            $deadline = '';
//            if ($task->report_deadline == date('Y-m-d H:i:00', strtotime('+1 day'))) {
//                $deadline = "24";
//            } elseif ($task->report_deadline == date('Y-m-d H:i:00', strtotime('+12 hours'))) {
//                $deadline = "12";
//            } elseif ($task->report_deadline == date('Y-m-d H:i:00', strtotime('+6 hours'))) {
//                $deadline = "6";
//            } elseif ($task->report_deadline == date('Y-m-d H:i:00', strtotime('+3 hours'))) {
//                $deadline = "3";
//            } elseif ($task->report_deadline == date('Y-m-d H:i:00', strtotime('+1 hour'))) {
//                $deadline = "1";
//            }
//            $message = (object)[];
//            $message->title = '任务到期提醒';
//            $message->content = '您有任务将在' . $deadline . "小时后到期,请注意上报";
//            $message->type = MSG_TP_TSK;
//            $message->subtype = MSG_SP_TSK_EXPIRED;
//            $message->user_id = $task->user_id;
//            $message->params = json_encode([
//                'id' => $task->task_id,
//                'dept_id' => $task->dept_id
//            ]);
//
//            // 微信模板消息所需参数
//            $message->openid = $smart_users[$task->user_id]->openid;
//            $message->tpl = TPL_MESSAGES_RESULT;
//            $message->first = "您有任务将在" . $deadline . "小时后到期,请注意上报";
//            $message->keyword1 = "任务即将逾期提醒";
//            $message->keyword2 = $task->status === 1 ? "任务未签收" : "任务未上报";
//            $message->keyword3 = "[上报截止时间]：{$task->report_deadline}";
//            MessageController::create($message, true);
//        }
    }


    public function log()
    {
        Log::emergency("系统挂掉了");
        Log::alert("数据库访问异常");
        Log::critical("系统出现未知错误");
        Log::error("指定变量不存在");
        Log::warning("该方法已经被废弃");
        Log::notice("用户在异地登录");
        Log::info("用户xxx登录成功");
        Log::debug("调试信息");
    }
}
