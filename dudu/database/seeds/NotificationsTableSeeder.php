<?php

use Illuminate\Database\Seeder;

class NotificationsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \DB::table('notifications')->delete();

        \DB::table('notifications')->insert(array (
            array (
                'org_id' => 1,
                'group_id' => 1,
                'title' => '通知A-我发送的-无需审核-全部已读',
                'desc' => '这是一条通知数据用于测试，这里显示的是通知的详细描述',
                'significance' => 0,
                'send_time' => '2018-09-09 17:25:00',
                'created_at' => '2018-09-10 17:25:00',
                'updated_at' => '2018-09-12 22:53:54',
            ),
            array (
                'org_id' => 1,
                'group_id' => 1,
                'title' => '通知B-我发送的-无需审核-有未读',
                'desc' => '这是一条通知数据用于测试，这里显示的是通知的详细描述',
                'significance' => 1,
                'send_time' => '2018-09-12 17:25:00',
                'created_at' => '2018-09-10 17:25:00',
                'updated_at' => '2018-09-12 22:53:54',
            ),
            array (
                'org_id' => 1,
                'group_id' => 1,
                'title' => '通知C-我发送的-要审核-未通过',
                'desc' => '这是一条通知数据用于测试，这里显示的是通知的详细描述',
                'significance' => 2,
                'send_time' => '2018-09-20 17:25:00',
                'created_at' => '2018-09-10 17:25:00',
                'updated_at' => '2018-09-12 22:53:54',
            ),
            array (
                'org_id' => 1,
                'group_id' => 1,
                'title' => '通知D-我发送的-要审核-已通过',
                'desc' => '这是一条通知数据用于测试，这里显示的是通知的详细描述',
                'significance' => 1,
                'send_time' => '2018-09-21 17:25:00',
                'created_at' => '2018-09-10 17:25:00',
                'updated_at' => '2018-09-12 22:53:54',
            ),
            array (
                'org_id' => 1,
                'group_id' => 1,
                'title' => '通知E-我发送的-要审核-待审核',
                'desc' => '这是一条通知数据用于测试，这里显示的是通知的详细描述',
                'significance' => 1,
                'send_time' => '2018-09-24 17:25:00',
                'created_at' => '2018-09-10 17:25:00',
                'updated_at' => '2018-09-12 22:53:54',
            ),
            array (
                'org_id' => 1,
                'group_id' => 1,
                'title' => '通知F-我接收的-未读',
                'desc' => '这是一条通知数据用于测试，这里显示的是通知的详细描述',
                'significance' => 0,
                'send_time' => '2018-09-24 17:25:00',
                'created_at' => '2018-09-10 17:25:00',
                'updated_at' => '2018-09-12 22:53:54',
            ),
            array (
                'org_id' => 1,
                'group_id' => 1,
                'title' => '通知G-我接收的-已读',
                'desc' => '这是一条通知数据用于测试，这里显示的是通知的详细描述',
                'significance' => 0,
                'send_time' => '2018-09-24 17:25:00',
                'created_at' => '2018-09-10 17:25:00',
                'updated_at' => '2018-09-12 22:53:54',
            ),
        ));
    }
}
