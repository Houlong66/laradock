<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\MessageController;
use App\Models\TaskUser;
use App\Models\Schedule as ScheduleModel;
use App\Models\File as FileModel;
use App\Models\User;
use App\Libraries\Lunar;
use App\Libraries\LunarSolarConverter;
use App\Libraries\Solar;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        /* 1. 每天 17:00 运行一次定时任务，给当天有截止事务且未完成工作的用户发送消息提醒 */
        $schedule->call(function () {
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

            // 循环发通知提醒
            foreach ($tasks as $task) {
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
        })->dailyAt('17:00');
        // 测试用可以改成每分钟方便查看情况
//         })->everyMinute();


        /* 2. 每分钟运行一次定时任务，检查当前有处于提醒时间的日程发送消息提醒 */
        $schedule->call(function () {
            $schedules = ScheduleModel::with([
                'reminds' => function ($query) {
                    $query->where('remind_at', date('Y-m-d H:i:00'));
                }
            ])->where('type', '!=', 3)->get();

            // 获取所有相关用户
            $user_id_list = [];
            foreach ($schedules as $schedule) {
                $user_id_list[] = $schedule->user_id;
            }
            $users = User::whereIn('id', $user_id_list)->get();
            $smart_users = [];
            foreach ($users as $k => $u) {
                $smart_users[$u->id] = $u;
            }

            foreach ($schedules as $schedule) {
                if (!$schedule->reminds->isEmpty()) {
                    $message = (object)[];
                    $message->title = '日程提醒';
                    $message->content = '您有即将开始的日程，请点击查看详情';
                    $message->type = MSG_TP_SCH;
                    $message->subtype = MSG_SP_SCH_EXPIRED;
                    $message->user_id = $schedule->user_id;
                    $message->params = json_encode([
                        'id' => $schedule->id
                    ]);

                    $start_time = $schedule->start_at === Null ? $schedule->reminds[0]->remind_at : $schedule->start_at;
                    // 微信模板消息所需参数
                    $message->openid = $smart_users[$schedule->user_id]->openid;
                    $message->tpl = TPL_MESSAGES_RESULT;
                    $message->first = "日程提醒";
                    $message->keyword1 = "{$schedule->name}";
                    $message->keyword2 = "即将开始";
                    $message->keyword3 = "[开始时间]：{$start_time}";
                    MessageController::create($message, true);
                }
            }

            // 纪念日
            $memorials = ScheduleModel::with([
                'reminds' => function ($query) {
                    $query->where('remind_at', date('Y-m-d H:i:00'));
                }
            ])->where('type', 3)->where('is_solar', 1)->get();

            // 农历
            $solar = new Solar();
            $solar->solarYear = date('Y');
            $solar->solarMonth = date('m');
            $solar->solarDay = date('d');
            $lunar = LunarSolarConverter::SolarToLunar($solar);
            $lunar = LunarSolarConverter::lunarToDate($lunar);
            $lunar = date($lunar . ' H:i:00');
            $lunar = ScheduleModel::with([
                'reminds' => function ($query) use ($lunar) {
                    $query->where('remind_at', $lunar);
                }
            ])->where('type', 3)->where('is_solar', 0)->get();

            $memorials = $memorials->merge($lunar);

            // 获取所有相关用户
            $user_id_list = [];
            foreach ($memorials as $memorial) {
                $user_id_list[] = $memorial->user_id;
            }
            $users = User::whereIn('id', $user_id_list)->get();
            $smart_users = [];
            foreach ($users as $k => $u) {
                $smart_users[$u->id] = $u;
            }

            foreach ($memorials as $memorial) {
                if (!$memorial->reminds->isEmpty()) {
                    $message = (object)[];
                    $message->title = '纪念日提醒';
                    $message->content = '您有即将开始的纪念日，请点击查看详情';
                    $message->type = MSG_TP_SCH;
                    $message->subtype = MSG_SP_MEM_EXPIRED;
                    $message->user_id = $memorial->user_id;
                    $message->params = json_encode([
                        'id' => $memorial->id
                    ]);
                    // 微信模板消息所需参数
                    $message->openid = $smart_users[$memorial->user_id]->openid;
                    $message->tpl = TPL_MESSAGES_RESULT;
                    $message->first = "纪念日提醒";
                    $message->keyword1 = "{$memorial->name}";
                    $message->keyword2 = "即将开始";
                    $message->keyword3 = "[开始时间]：{$memorial->start_at}";
                    MessageController::create($message, true);
                }
            }


            // 任务提醒
            $tasks = TaskUser::where('item_type', 0)->whereIn('status', [1, 2])
                ->where(function ($query) {
                    $query->where('report_deadline', date('Y-m-d H:i:00', strtotime('+1 day')))
                        ->orWhere('report_deadline', date('Y-m-d H:i:00', strtotime('+3 hours')))
                        ->orWhere('report_deadline', date('Y-m-d H:i:00'));
                })->get();


            // 获取所有相关用户
            $user_id_list = [];

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
                $sign_content = "";
                $supervision_content = "";

                if ($task->report_deadline == date('Y-m-d H:i:00', strtotime('+1 day'))) {
                    $deadline = "24";
                    $sign_content = '您的任务'. $task->task->title .'将在' . $deadline . "小时后到期,请尽快上报";
                    $supervision_content = '您监督的任务'. $task->task->title .'将在' . $deadline . "小时后到期";
                } elseif ($task->report_deadline == date('Y-m-d H:i:00', strtotime('+3 hours'))) {
                    $deadline = "3";
                    $sign_content = '您的任务'. $task->task->title .'将在' . $deadline . "小时后到期,请尽快上报";
                    $supervision_content = '您监督的任务'. $task->task->title .'将在' . $deadline . "小时后到期";
                } elseif ($task->report_deadline == date('Y-m-d H:i:00')){
                    $sign_content = '您的任务'. $task->task->title ."已逾期,请尽快上报";
                    $supervision_content = '您监督的任务'. $task->task->title ."已逾期,请尽快上报";
                }

                $message = (object)[];
                $message->title = '任务到期提醒';
                $message->content = $sign_content;
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
                $message->first = $supervision_content;
                $message->keyword1 = "任务即将逾期提醒";
                $message->keyword2 = $task->status === 1 ? "任务未签收" : "任务未上报";
                $message->keyword3 = "[上报截止时间]：{$task->report_deadline}";
                MessageController::create($message, true);


                // 监督人发送消息
                if (isset($task->supervision)){
                    foreach ($task->supervision as $v){
                        $message = (object)[];
                        $message->title = '监督任务到期提醒';
                        $message->content = $sign_content;
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
                        $message->first = $supervision_content;
                        $message->keyword1 = '监督任务到期提醒';
                        $message->keyword2 = $task->status === 1 ? "任务未签收" : "任务未上报";
                        $message->keyword3 = "[上报截止时间]：{$task->report_deadline}";
                        MessageController::create($message, true);
                    }
                }
            }

        })->everyMinute();


        /* 3. 每天 21:00 运行一次定时任务，检查当前过期的文件下载链接并将对应文件和数据库记录删除 */
        $schedule->call(function () {
            $expired = FileModel::where('expire_at', '<', date('Y-m-d H:i:s'))->get();

            foreach ($expired as $file) {
                // 有对应压缩包的删除压缩包
                if (!empty($file->zip_file_path)) {
                    Storage::disk('attachments')->delete($file->zip_file_path);
                }
                // 删除数据库记录
                $file->delete();
            }
        })->dailyAt('21:00');
        // })->everyMinute();

        // 每天0点修改前一天的纪念日为下一年
        $schedule->call(function () {

            $memorials = ScheduleModel::where('type', 3)
                ->where('start_at', date('Y-m-d 00:00:00', strtotime('-1 days')))->where('is_solar', 1)->get();
            // 农历
            $solar = new Solar();
            $solar->solarYear = date('Y', strtotime('-1 days'));
            $solar->solarMonth = date('m', strtotime('-1 days'));
            $solar->solarDay = date('d', strtotime('-1 days'));
            $lunar = LunarSolarConverter::SolarToLunar($solar);
            $lunar = LunarSolarConverter::lunarToDate($lunar);
            $lunar = $lunar . ' 00:00:00';
            $lunar = ScheduleModel::where('type', 3)
                ->where('start_at', $lunar)->where('is_solar', 0)->get();

            $memorials = $memorials->merge($lunar);

            foreach ($memorials as $memorial) {
                // 修改为下一年
                $memorial->start_at = date("Y-m-d H:i", strtotime("+1 year", strtotime($memorial->start_at)));
                $memorial->end_at = date("Y-m-d H:i", strtotime("+1 year", strtotime($memorial->end_at)));
                // $memorial->remind_at = date("Y-m-d H:i", strtotime("+1 year", strtotime($memorial->remind_at)));
                $memorial->save();
                $remind = $memorial->reminds()->first();
                $remind->remind_at = date("Y-m-d H:i", strtotime("+1 year", strtotime($remind->remind_at)));
                $remind->save();
            }
        })->dailyAt('00:00');
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
