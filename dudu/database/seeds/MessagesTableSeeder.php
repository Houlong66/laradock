<?php

use Illuminate\Database\Seeder;

class MessagesTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('messages')->delete();
        
        \DB::table('messages')->insert(array (
            0 => 
            array (
                'id' => 1,
                'title' => '机构注册审核未通过',
                'content' => '您的机构注册申请未通过，点击查看详情',
                'status' => '1',
                'type' => 1,
                'subtype' => 2,
                'params' => '{"id": 3}',
                'user_id' => 1,
                'created_at' => '2018-09-07 21:33:52',
                'updated_at' => NULL,
            ),
            1 => 
            array (
                'id' => 2,
                'title' => '您已被管理员踢出群组',
                'content' => '您的机构注册申请已通过审核，点击消息开始完善机构信息和设置管理密码。您的机构注册申请已通过审核，点击消息开始完善机构信息和设置管理密码。您的机构注册申请已通过审核，点击消息开始完善机构信息和设置管理密码。',
                'status' => '0',
                'type' => 5,
                'subtype' => 0,
                'params' => NULL,
                'user_id' => 1,
                'created_at' => '2018-09-07 21:33:52',
                'updated_at' => NULL,
            ),
            2 => 
            array (
                'id' => 3,
                'title' => '机构注册审核通过',
                'content' => '您的机构注册申请已通过审核，点击消息开始完善机构信息和设置管理密码。',
                'status' => '1',
                'type' => 2,
                'subtype' => 3,
                'params' => '{"id": 1}',
                'user_id' => 1,
                'created_at' => '2018-09-07 21:33:52',
                'updated_at' => NULL,
            ),
        ));
        
        
    }
}