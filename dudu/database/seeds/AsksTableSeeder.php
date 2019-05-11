<?php

use Illuminate\Database\Seeder;

class AsksTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \DB::table('asks')->delete();

        \DB::table('asks')->insert(array (
            array (
                'org_id' => 1,
                'title' => '请示A-工作类-我发送的-未处理',
                'desc' => '这是一条请示数据用于测试',
                'ask_type' => 1,
                'send_time' => '2018-09-09 17:25:00',
                'created_at' => '2018-09-10 17:25:00',
                'updated_at' => '2018-09-12 22:53:54',
            ),
            array (
                'org_id' => 1,
                'title' => '请示B-工作类-我接收的-未处理',
                'desc' => '这是一条请示数据用于测试',
                'ask_type' => 1,
                'send_time' => '2018-09-12 17:25:00',
                'created_at' => '2018-09-10 17:25:00',
                'updated_at' => '2018-09-12 22:53:54',
            ),
            array (
                'org_id' => 1,
                'title' => '请示C-请假类-我接收的-未处理',
                'desc' => '这是一条请示数据用于测试',
                'ask_type' => 2,
                'send_time' => '2018-09-20 17:25:00',
                'created_at' => '2018-09-10 17:25:00',
                'updated_at' => '2018-09-12 22:53:54',
            ),
            array (
                'org_id' => 1,
                'title' => '请示D-用车类-我接收的-未处理',
                'desc' => '这是一条请示数据用于测试',
                'ask_type' => 3,
                'send_time' => '2018-09-21 17:25:00',
                'created_at' => '2018-09-10 17:25:00',
                'updated_at' => '2018-09-12 22:53:54',
            ),
            array (
                'org_id' => 1,
                'title' => '请示E-用车类-我发出的-已处理',
                'desc' => '这是一条请示数据用于测试',
                'ask_type' => 3,
                'send_time' => '2018-09-24 17:25:00',
                'created_at' => '2018-09-10 17:25:00',
                'updated_at' => '2018-09-12 22:53:54',
            ),
            array (
                'org_id' => 1,
                'title' => '请示F-请假类-我接收的-已处理',
                'desc' => '这是一条请示数据用于测试',
                'ask_type' => 2,
                'send_time' => '2018-09-24 17:25:00',
                'created_at' => '2018-09-10 17:25:00',
                'updated_at' => '2018-09-12 22:53:54',
            ),
        ));
    }
}
