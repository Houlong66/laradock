<?php

namespace App\Http\Controllers;

use App\Models\GroupUser;
use App\Models\Schedule;
use function foo\func;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Libraries\Lunar;
use App\Libraries\Solar;
use App\Libraries\LunarSolarConverter;
use Illuminate\Database\Eloquent\Collection;
use App\Models\Group;
use App\Models\Remind;

class ScheduleController extends Controller
{
    // 默认的返回字段（简略版本日程）
    const DEFAULT_COLUMNS = [
        'id',
        'name',
        'type',
        'fullday',
        'start_at',
        'end_at',
        'is_solar'
    ];

    // 获取当前用户的所有日程
    public function index(Request $request)
    {
        $schedules = Schedule::select(self::DEFAULT_COLUMNS)
            ->where([
                ['user_id', session()->get('user.id')]
            ])
            ->where('is_active', 1)
            ->where('is_solar', 1)
            ->with(['reminds'])
            ->get();
        $lunars = Schedule::select(self::DEFAULT_COLUMNS)
            ->where([
                ['user_id', session()->get('user.id')]
            ])
            ->where('is_active', 1)
            ->where('type', 3)
            ->where('is_solar', 0)
            ->with(['reminds'])
            ->get();
        foreach ($lunars as $lunar) {
            $l = Lunar();
            $l->lunarDay = date('d', strtotime($lunar->start_at));
            $l->lunarMonth = date('m', strtotime($lunar->start_at));
            $l->lunarYear = date('Y', strtotime($lunar->start_at));
            $l->isleap = false;
            $solar = LunarSolarConverter::LunarToSolar($l);
            $solar = $solar->solarYear . '-' . $solar->solarMonth . '-' . $solar->solarDay;
            $lunar->start_at = $solar . ' ' . date('H:i:00', strtotime($lunar->start_at));
            $lunar->end_at = $solar . ' ' . date('H:i:00', strtotime($lunar->end_at));
            // $lunar->remind_at = $solar.' '.date('H:i:00', strtotime($lunar->remind_at));
        }
        $schedules = $schedules->merge($lunars);

        return ResponseJson($schedules);
    }

    // 根据日程ID获取详细信息
    public function getDetailById(Request $request, $id)
    {
        $schedules = Schedule::with(['user'])->where([
            ['user_id', session()->get('user.id')],
            ['id', $id]
        ])
            ->where('is_active', 1)
            ->first();
        $schedules->remind_at = collect();
        foreach ($schedules->reminds as $remind) {
            $schedules->remind_at->push($remind->remind_at);
        }
        // 添加纪念日识别
        if ($schedules->type === 3) {
            $schedules->start_at = date('m-d', strtotime($schedules->start_at));
            $schedules->end_at = $schedules->start_at;
//            $schedules->remind_at[0] = date('H:i:00', strtotime($schedules->remind_at[0]));
        }
        $schedules->makeHidden('reminds');
        return ResponseJson($schedules);
    }

    // 根据日期获取当天的日程
    public function getByDay(Request $request, $ymd)
    {
        $schedules = Schedule::select(self::DEFAULT_COLUMNS)
            ->where('user_id', session()->get('user.id'))
            ->where('type', 1)
            ->where(function ($query) use ($ymd) {
                $query->whereRaw('DATE_FORMAT(start_at, "%Y%m%d") <= ?', [$ymd])
                    ->WhereRaw('DATE_FORMAT(end_at, "%Y%m%d") >= ?', [$ymd])
                    ->whereRaw('is_active = ?', [1]);
            })
            ->get();
        // 纪念日
        $memorials = Schedule::select(self::DEFAULT_COLUMNS)
            ->where('user_id', session()->get('user.id'))
            ->where('type', 3)
            ->where('is_solar', 1)
            ->where(function ($query) use ($ymd) {
                $query->whereRaw('DATE_FORMAT(start_at, "%m%d") = ?', [substr($ymd, 4, 4)]);
            })
            ->get();

        // 农历 纪念日
        $solar = LunarSolarConverter::dateStrToSolar($ymd);
        $lunar = LunarSolarConverter::SolarToLunar($solar);
        $lunar = LunarSolarConverter::lunarToDate($lunar);
        $lunar_schedules = Schedule::select(self::DEFAULT_COLUMNS)
            ->where('user_id', session()->get('user.id'))
            ->where('type', 3)
            ->where('is_solar', 0)
            ->where(function ($query) use ($lunar) {
                $query->whereRaw('DATE_FORMAT(start_at, "%m-%d") = ?', [date('m-d', strtotime($lunar))]);
            })
            ->get();

        $memorials = $memorials->merge($lunar_schedules);

        foreach ($memorials as $memorial) {
            $memorial->start_at = date('m-d H:i:00', strtotime($memorial->start_at));
            $memorial->end_at = date('m-d H:i:00', strtotime($memorial->end_at));
            // $memorial->remind_at = date('m-d H:i:00', strtotime($memorial->remind_at));
        }
        $schedules = $schedules->merge($memorials);
        return ResponseJson($schedules);
    }

    // 根据日期获取当天的提醒
    public function getRemindByDay(Request $request, $ymd)
    {
        $user_id = session()->get('user.id');
        // 获取任务类提醒
        $users = User::with([
            'tasks' => function ($query) use ($user_id, $ymd) {
                $query->select(
                    DB::raw('"task" AS type'),
                    DB::raw('DATE_FORMAT(report_deadline, "%H:%i") AS time'),
                    'task_id', 'title'
                )
                    ->whereRaw('DATE_FORMAT(report_deadline, "%Y%m%d") = ?', [$ymd]);
            }
        ])
            ->find($user_id);
        $tasks_reminds = [];
        foreach ($users->tasks as $task) {
            // 判断任务是否已发送
            if (($task->pivot->item_type == 0 || $task->pivot->item_type == 3) && $task->pivot->status == 0) {
                continue;
            }
            // 过滤审批人
            if ($task->pivot->item_type == 2) {
                continue;
            }
            $temp = new \stdClass;
            $temp->id = $task->task_id;
            $temp->title = $task->title;
            $temp->type = $task->type;
            $temp->time = $task->time;
            array_push($tasks_reminds, $temp);
        }

        // 日程 and 提醒
        $schedules = Schedule::with([
            'reminds' => function ($query) use ($ymd) {
                $query->whereRaw('DATE_FORMAT(remind_at, "%Y%m%d")=?', [$ymd])->orderBy('remind_at', 'asc');
            }
        ])->where('user_id', $user_id)
            ->where('is_solar', 1)
            ->where('type', '!=', 3)
            ->where('is_active', 1)->get();
        $schedule_reminds = [];
        foreach ($schedules as $schedule) {
            // 有提醒的项才进入后续操作
            $reminds_num = $schedule->reminds->count();
            if ($reminds_num !== 0) {
                // 提醒时间拼凑
                $reminds_time = "";
                foreach($schedule->reminds as $k => $r) {
                    if($k < $reminds_num-1){
                        $reminds_time .= date('H:i', strtotime($r->remind_at)).",  ";
                    }else{
                        $reminds_time .= date('H:i', strtotime($r->remind_at));
                    }
                }
                array_push($schedule_reminds, [
                    'type' => $schedule->type == 1 ? "schedule" : "remind",
                    'time' => $reminds_time,
                    'id' => $schedule->id,
                    'title' => $schedule->name
                ]);
            }
        }

        // 纪念日
        $memorials = Schedule::with([
            'reminds' => function ($query) use ($ymd) {
                $query->whereRaw('DATE_FORMAT(remind_at, "%m%d")=?', [substr($ymd, 4, 4)]);
            }
        ])->where('user_id', $user_id)
            ->where('is_solar', 1)
            ->where('type', 3)
            ->where('is_active', 1)->get();
        $memorial_reminds = [];
        foreach ($memorials as $memorial) {
            if (!$memorial->reminds->isEmpty()) {
                array_push($memorial_reminds, [
                    'type' => 'memorial',
                    'time' => date('H:i', strtotime($memorial->reminds[0]->remind_at)),
                    'id' => $memorial->id,
                    'title' => $memorial->name
                ]);
            }
        }

        // 农历
        $solar = LunarSolarConverter::dateStrToSolar($ymd);
        $lunar = LunarSolarConverter::SolarToLunar($solar);
        $lunar = LunarSolarConverter::lunarToDate($lunar);

        $lunars = Schedule::with([
            'reminds' => function ($query) use ($lunar) {
                $query->whereRaw('DATE_FORMAT(remind_at, "%Y-%m-%d")=?', [$lunar]);
            }
        ])->where('user_id', $user_id)
            ->where('is_solar', 0)
            ->where('type', 3)
            ->where('is_active', 1)->get();
        $lunar_reminds = [];
        foreach ($lunars as $lunar) {
            if (!$lunar->reminds->isEmpty()) {
                array_push($lunar_reminds, [
                    'type' => 'memorial',
                    'time' => date('H:i', strtotime($lunar->reminds[0]->remind_at)),
                    'id' => $lunar->id,
                    'title' => $lunar->name
                ]);
            }
        }
        // 合并
        $reminds = array_merge($tasks_reminds, $schedule_reminds,
            $memorial_reminds, $lunar_reminds);

        return ResponseJson($reminds);
    }

    // 根据起止日期获取有提醒的日期列表
    public function getEventDays(Request $request, $from, $to)
    {
        $user_id = session()->get('user.id');
        $users = User::with([
            'tasks' => function ($query) use ($from, $to) {
                $query->select(DB::raw('DATE_FORMAT(report_deadline, "%Y-%m-%d") AS day'))
                    ->whereRaw('DATE_FORMAT(report_deadline, "%Y%m%d") >= ?', [$from])
                    ->whereRaw('DATE_FORMAT(report_deadline, "%Y%m%d") <= ?', [$to])
                    ->distinct();
            }
        ])
            ->find($user_id);
        $task_days = [];
        foreach ($users->tasks as $task) {
            $temp = new \stdClass();
            $temp->day = $task->day;
            array_push($task_days, $temp);
        }

        $_from = date('Y-m-d 00:00:00', strtotime($from));
        $_to = date('Y-m-d 23:59:00', strtotime($to));

        $schedules = Schedule::select('id')->with([
            'reminds' => function ($query) use ($_from, $_to) {
                $query->where('remind_at', '>=', $_from)
                    ->where('remind_at', '<=', $_to);
            }
        ])->where('is_solar', 1)
            ->where('is_active', 1)
            ->get();
        $schedule_days = [];
        foreach ($schedules as $schedule) {
            foreach ($schedule->reminds as $remind) {
                array_push($schedule_days, [
                    'day' => date('Y-m-d', strtotime($remind->remind_at))
                ]);
            }
        }
        $schedule_days = array_unique($schedule_days, SORT_REGULAR);

        $solar = LunarSolarConverter::dateStrToSolar($from);
        $lunar_from = LunarSolarConverter::SolarToLunar($solar);
        $lunar_from = LunarSolarConverter::lunarToDate($lunar_from);
        $solar = LunarSolarConverter::dateStrToSolar($to);
        $lunar_to = LunarSolarConverter::SolarToLunar($solar);
        $lunar_to = LunarSolarConverter::lunarToDate($lunar_to);

        $schedules = Schedule::select('id')->with([
            'reminds' => function ($query) use ($lunar_from, $lunar_to) {
                $query->where('remind_at', '>=', $lunar_from . " 00:00:00")
                    ->where('remind_at', '<=', $lunar_to . " 23:59:00");
            }
        ])->where('is_solar', 0)
            ->where('is_active', 1)
            ->get();
        $lunar_days = [];
        foreach ($schedules as $schedule) {
            foreach ($schedule->reminds as $remind) {
                array_push($lunar_days, [
                    'day' => date('Y-m-d', strtotime($remind->remind_at))
                ]);
            }
        }
        $lunar_days = array_unique($lunar_days, SORT_REGULAR);
        foreach ($lunar_days as $lunar_day) {
            $lunar_day['day'] = LunarSolarConverter::lunarDateToSolarDate($lunar_day['day']);
        }

        // Convert from stdClass to Array
        // $days = array_values(json_decode(json_encode($days), true));

        $days = array_merge($task_days, $schedule_days, $lunar_days);

        return ResponseJson($days);
    }

    // 创建一条日程
    public function create(Request $request)
    {
        $user_ids = [];
        if ($request->is_others == true) {
            $user = User::findOrFail(session()->get('user.id'));
            $org = $user->orgs()->where('orgs.id', $request->org_id)->first();
            hasRole($org->pivot->role_id, [ROLE_SYS, ROLE_GRP, ROLE_SYS_TASK], "权限不足，无法为他人创建日程");
            $user_ids = explode(",", $request->targets);
        }

        if ($request->is_self == true) {
            $user_ids[] = session()->get('user.id');
        }

        foreach ($user_ids as $user_id) {
            $schedule = new Schedule;
            $schedule->creater_id = session()->get('user.id');
            $schedule->user_id = $user_id;
            $schedule->type = $request->type;
            $schedule->name = $request->name;
            $schedule->comment = $request->comment;
            $schedule->start_at = $request->start_at;
            $schedule->end_at = $request->end_at;
            // $schedule->remind_at = $request->remind_at;
            $schedule->public = $request->public;
            $schedule->fullday = $request->fullday;
            $schedule->save();
            $reminds = explode(',', $request->remind_at);
            foreach ($reminds as $remind_at) {
                $remind = new Remind;
                $remind->remind_at = $remind_at;
                $remind->schedule_id = $schedule->id;
                $remind->save();
            }
        }

        return ResponseJson();
    }

    // 更新一条日程
    public function update(Request $request, $id)
    {
        $schedule = Schedule::find($id);
        if ($schedule->user_id == session()->get('user.id')) {
            $schedule->type = $request->type;
            $schedule->name = $request->name;
            $schedule->comment = $request->comment;
            $schedule->start_at = $request->start_at;
            $schedule->end_at = $request->end_at;
            // $schedule->remind_at = $request->remind_at;
            $schedule->public = $request->public;
            $schedule->fullday = $request->fullday;
            $schedule->save();
            Remind::where('schedule_id', $schedule->id)->delete();
            $reminds = explode(',', $request->remind_at);
            foreach ($reminds as $remind_at) {
                $remind = new Remind;
                $remind->remind_at = $remind_at;
                $remind->schedule_id = $schedule->id;
                $remind->save();
            }
        }

        return ResponseJson();
    }

    // 删除一条日程
    public function delete(Request $request, $id)
    {
        $schedule = Schedule::find($id);
        if ($schedule->user_id == session()->get('user.id')) {
            $schedule->delete();
            Remind::where('schedule_id', $id)->delete();
        }
        return ResponseJson();
    }

    // 创建纪念日
    public function createMemorialDay(Request $request)
    {
        $user_ids = [];
        if ($request->is_others == true) {
            $user = User::findOrFail(session()->get('user.id'));
            $org = $user->orgs()->where('orgs.id', $request->org_id)->first();
            hasRole($org->pivot->role_id, [ROLE_SYS, ROLE_GRP, ROLE_SYS_TASK], "权限不足，无法为他人创建日程");
            $user_ids = explode(",", $request->targets);
        }
        if ($request->is_self == true) {
            $user_ids[] = session()->get('user.id');
        }

        if ($request->is_solar == 1) {
            if (date(date("Y") . '-' . $request->start_at) >= date("Y-m-d")) {
                $request->start_at = date("Y") . '-' . $request->start_at;
            } else {
                $request->start_at = date("Y", strtotime("+1 year")) . '-' . $request->start_at;
            }
        } else {
            // 农历
            $solar = new Solar();
            $solar->solarYear = date('Y');
            $solar->solarMonth = date('m');
            $solar->solarDay = date('d');
            $lunar = LunarSolarConverter::SolarToLunar($solar);
            $lunar = LunarSolarConverter::lunarToDate($lunar);
            if (date("Y", strtotime("-1 year")) . '-' . $request->start_at >= $lunar) {
                $request->start_at = date("Y", strtotime('-1 year')) . '-' . $request->start_at;
            } else {
                if (date("Y") . '-' . $request->start_at >= $lunar) {
                    $request->start_at = date("Y") . '-' . $request->start_at;
                } else {
                    $request->start_at = date("Y", strtotime("+1 year")) . '-' . $request->start_at;
                }
            }
        }

        foreach ($user_ids as $user_id) {
            $schedule = new Schedule;
            $schedule->creater_id = session()->get('user.id');
            $schedule->user_id = $user_id;
            $schedule->type = 3;
            $schedule->name = $request->name;
            $schedule->comment = $request->comment;
            $schedule->start_at = $request->start_at . " 00:00";
            $schedule->end_at = $request->start_at . " 23:59";
            // $schedule->remind_at = $request->start_at . " " . $request->remind_at;
            $schedule->public = $request->public;
            $schedule->fullday = 1;
            $schedule->is_solar = $request->is_solar;
            $schedule->save();

            $reminds = explode(',', $request->remind_time);
            foreach ($reminds as $remind_at) {
                $remind = new Remind;
                $remind->is_solar = $request->is_solar;
                $remind->schedule_id = $schedule->id;
                $remind->remind_at = $request->start_at . " " . $remind_at;
                $remind->save();
            }
        }
        return ResponseJson();
    }

    // 获取纪念日
    public function getMemorialDay(Request $request) { 
        // 纪念日
        $memorials = Schedule::select(self::DEFAULT_COLUMNS)->where('type','=', 3)->where('user_id', session()->get('user.id'))->get();
        return ResponseJson($memorials);
    }

    // 更新纪念日
    public function updateMemorialDay(Request $request)
    {
        $memorial = Schedule::find($request->id);
        if (session()->get('user.id') == $memorial->user_id) {

            if ($request->is_solar == 1) {
                if (date(date("Y") . '-' . $request->start_at) >= date("Y-m-d")) {
                    $request->start_at = date("Y") . '-' . $request->start_at;
                } else {
                    $request->start_at = date("Y", strtotime("+1 year")) . '-' . $request->start_at;
                }
            } else {
                // 农历
                $solar = new Solar();
                $solar->solarYear = date('Y');
                $solar->solarMonth = date('m');
                $solar->solarDay = date('d');
                $lunar = LunarSolarConverter::SolarToLunar($solar);
                $lunar = LunarSolarConverter::lunarToDate($lunar);
                if (date("Y", strtotime("-1 year")) . '-' . $request->start_at >= $lunar) {
                    $request->start_at = date("Y", strtotime('-1 year')) . '-' . $request->start_at;
                } else {
                    if (date("Y") . '-' . $request->start_at >= $lunar) {
                        $request->start_at = date("Y") . '-' . $request->start_at;
                    } else {
                        $request->start_at = date("Y", strtotime("+1 year")) . '-' . $request->start_at;
                    }
                }
            }

            $memorial->name = $request->name;
            $memorial->comment = $request->comment;
            $memorial->start_at = $request->start_at . " 00:00";
            $memorial->end_at = $request->start_at . " 23:59";
            // $memorial->remind_at = $request->start_at . " " . $request->remind_at;
            $memorial->public = $request->public;
            $memorial->is_solar = $request->is_solar;

            $memorial->save();
            Remind::where('schedule_id', $memorial->id)->delete();
            $reminds = explode(',', $request->remind_time);
            foreach ($reminds as $remind_at) {
                $remind = new Remind;
                $remind->is_solar = $memorial->is_solar;
                $remind->remind_at = $request->start_at . " " . $remind_at;
                $remind->schedule_id = $memorial->id;
                $remind->save();
            }

            return ResponseJson();
        }
        return ResponseJson([], "没有权限操作");
    }


    // 共享日程获取
    public function groupSchedule(Request $request)
    {
        $user_id = session()->get('user.id');

        //  获取到所有不被显示的用户ID
        $user = GroupUser::where([['group_id','=',$request->group_id],['role_id','=',10]])->orWhere([['group_id','=',$request->group_id],['role_id','=',5]])
                ->pluck('user_id')
                ->toArray();

        $group_users = [];

        foreach ($user as $k=>$v){
            //  存在数组即当前用户 可以查找到非追踪人的共享，包含自己的
                if ($user_id == $v){
                    array_push($group_users,$v);
                }
        }

        //获取到群组对应的用户ID
        $users_id = GroupUser::where([['group_id','=',$request->group_id],['role_id','!=',10],['role_id','!=',5]])->pluck('user_id')->toArray();

        foreach ($users_id as $id){
            array_push($group_users,$id);
        }


        hasRole($user_id, $group_users, "没有权限查看");

        $data = collect();


        foreach ($group_users as $id) {

            $user_name = User::find($id)->name;


            $data->put($user_name, collect());

            $schedules = Schedule::where('type', 1)->where('user_id', $id)
                ->where('is_active', 1)
                ->where('start_at', '<', $request->end_time)
                ->where('end_at', '>', $request->start_time)
                ->orderBy('start_at')
                ->get([
                    'user_id',
                    'name',
                    'comment',
                    'start_at',
                    'end_at',
                    'public',
                    'fullday'
                ]);

            foreach ($schedules as $schedule) {
                if ($schedule->start_at < $request->start_time) {
                    $schedule->start_at = $request->start_time;
                }
                if ($schedule->end_at > $request->end_time) {
                    $schedule->end_at = $request->end_time;
                }
                if ($schedule->public == 0) {
                    $schedule->name = "已安排";
                    $schedule->comment = "已安排";
                }
            }
            if (!$schedules->isEmpty()) {
                $data[$user_name] = $data[$user_name]->merge($schedules);
            }
        }


        return ResponseJson($data->toArray());
    }
}
