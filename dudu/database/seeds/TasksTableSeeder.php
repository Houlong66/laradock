<?php

use Illuminate\Database\Seeder;

class TasksTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        \DB::table('tasks')->delete();
        
        \DB::table('tasks')->insert(array (
            array (
                'org_id' => 1,
                'group_id' => 1,
                'title' => '任务A-我发送且无流转审批',
                'desc' => '这条任务主要用于测试任务的不同状态',
                'significance' => 0,
                'deadline' => '2018-09-11 17:25:00',
                'if_report_need_attachment' => 0,
                'if_report_need_autotable' => 0,
                'send_time' => '2018-09-10 17:25:00',
                'created_at' => '2018-09-10 17:25:00',
                'updated_at' => '2018-09-12 22:53:54',
            ),
            array (
                'org_id' => 1,
                'group_id' => 1,
                'title' => '任务B-我发送且流转审核通过',
                'desc' => '这条任务主要用于测试任务流转审批的状态',
                'significance' => 1,
                'deadline' => '2018-09-13 18:25:00',
                'if_report_need_attachment' => 0,
                'if_report_need_autotable' => 0,
                'send_time' => '2018-09-10 17:25:00',
                'created_at' => '2018-09-10 17:25:00',
                'updated_at' => '2018-09-12 22:53:54',
            ),
            array (
                'org_id' => 1,
                'group_id' => 1,
                'title' => '任务C-我发送且待流转审核',
                'desc' => '这是一条任务数据用于测试，这里显示的是任务的详细描述',
                'significance' => 2,
                'deadline' => '2018-09-10 15:25:00',
                'if_report_need_attachment' => 0,
                'if_report_need_autotable' => 0,
                'send_time' => '2018-09-01 17:25:00',
                'created_at' => '2018-09-10 17:25:00',
                'updated_at' => '2018-09-12 22:53:54',
            ),
            array (
                'org_id' => 1,
                'group_id' => 1,
                'title' => '任务D-我发送且流转审核不通过',
                'desc' => '这是一条任务数据用于测试，这里显示的是任务的详细描述',
                'significance' => 0,
                'deadline' => '2018-09-22 11:25:00',
                'if_report_need_attachment' => 0,
                'if_report_need_autotable' => 0,
                'send_time' => '2018-09-20 17:25:00',
                'created_at' => '2018-09-10 17:25:00',
                'updated_at' => '2018-09-12 22:53:54',
            ),
            array (
                'org_id' => 1,
                'group_id' => 1,
                'title' => '任务E-我接受的未签收',
                'desc' => '这是一条任务数据用于测试，这里显示的是任务的详细描述',
                'significance' => 1,
                'deadline' => '2018-09-30 17:25:00',
                'if_report_need_attachment' => 0,
                'if_report_need_autotable' => 0,
                'send_time' => '2018-09-30 17:25:00',
                'created_at' => '2018-09-10 17:25:00',
                'updated_at' => '2018-09-12 22:53:54',
            ),
            array (
                'org_id' => 1,
                'group_id' => 1,
                'title' => '任务F-我接收的未上报',
                'desc' => '这是一条任务数据用于测试，这里显示的是任务的详细描述',
                'significance' => 2,
                'deadline' => '2018-09-16 16:25:00',
                'if_report_need_attachment' => 0,
                'if_report_need_autotable' => 0,
                'send_time' => '2018-09-03 17:25:00',
                'created_at' => '2018-09-10 17:25:00',
                'updated_at' => '2018-09-12 22:53:54',
            ),
            array (
                'org_id' => 1,
                'group_id' => 1,
                'title' => '任务G-我签收的已上报',
                'desc' => '这是一条任务数据用于测试，这里显示的是任务的详细描述',
                'significance' => 1,
                'deadline' => '2018-09-21 19:25:00',
                'if_report_need_attachment' => 0,
                'if_report_need_autotable' => 0,
                'send_time' => '2018-09-10 17:25:00',
                'created_at' => '2018-09-10 17:25:00',
                'updated_at' => '2018-09-12 22:53:54',
            ),
            array (
                'org_id' => 1,
                'group_id' => 1,
                'title' => '任务H-我签收的已办结',
                'desc' => '这是一条任务数据用于测试，这里显示的是任务的详细描述',
                'significance' => 2,
                'deadline' => '2018-09-21 19:25:00',
                'if_report_need_attachment' => 0,
                'if_report_need_autotable' => 0,
                'send_time' => '2018-09-10 17:25:00',
                'created_at' => '2018-09-10 17:25:00',
                'updated_at' => '2018-09-12 22:53:54',
            ),
            array (
                'org_id' => 1,
                'group_id' => 1,
                'title' => '单位任务I-我签收的未签收',
                'desc' => '这是一条任务数据用于测试，这里显示的是任务的详细描述',
                'significance' => 0,
                'deadline' => '2018-09-21 19:25:00',
                'if_report_need_attachment' => 0,
                'if_report_need_autotable' => 0,
                'send_time' => '2018-09-10 17:25:00',
                'created_at' => '2018-09-10 17:25:00',
                'updated_at' => '2018-09-12 22:53:54',
            ),
        ));
    }
}