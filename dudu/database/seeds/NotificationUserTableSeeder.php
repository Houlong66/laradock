<?php

use Illuminate\Database\Seeder;

class NotificationUserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \DB::table('notification_user')->delete();

        \DB::table('notification_user')->insert(array (
            array (
                'item_type' => 1,
                'notification_id' => 1,
                'user_id' => 1,
                'check_time' => NULL,
                'audit_text' => NULL,
                'audit_time' => NULL,
                'status' => 0,
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            array (
                'item_type' => 0,
                'notification_id' => 1,
                'user_id' => 2,
                'check_time' => '2018-09-12 10:00:00',
                'audit_text' => NULL,
                'audit_time' => NULL,
                'status' => 2,
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            array (
                'item_type' => 0,
                'notification_id' => 1,
                'user_id' => 3,
                'check_time' => '2018-09-12 10:00:00',
                'audit_text' => NULL,
                'audit_time' => NULL,
                'status' => 2,
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            array (
                'item_type' => 0,
                'notification_id' => 1,
                'user_id' => 4,
                'check_time' => '2018-09-12 10:00:00',
                'audit_text' => NULL,
                'audit_time' => NULL,
                'status' => 2,
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            array (
                'item_type' => 0,
                'notification_id' => 1,
                'user_id' => 5,
                'check_time' => '2018-09-12 10:00:00',
                'audit_text' => NULL,
                'audit_time' => NULL,
                'status' => 2,
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            array (
                'item_type' => 1,
                'notification_id' => 2,
                'user_id' => 1,
                'check_time' => NULL,
                'audit_text' => NULL,
                'audit_time' => NULL,
                'status' => 1,
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            array (
                'item_type' => 0,
                'notification_id' => 2,
                'user_id' => 2,
                'check_time' => '2018-09-12 10:00:00',
                'audit_text' => NULL,
                'audit_time' => NULL,
                'status' => 2,
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            array (
                'item_type' => 0,
                'notification_id' => 2,
                'user_id' => 3,
                'check_time' => NULL,
                'audit_text' => NULL,
                'audit_time' => NULL,
                'status' => 1,
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            array (
                'item_type' => 1,
                'notification_id' => 3,
                'user_id' => 1,
                'check_time' => NULL,
                'audit_text' => NULL,
                'audit_time' => NULL,
                'status' => 3,
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            array (
                'item_type' => 2,
                'notification_id' => 3,
                'user_id' => 2,
                'check_time' => NULL,
                'audit_text' => NULL,
                'audit_time' => NULL,
                'status' => 3,
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            array (
                'item_type' => 0,
                'notification_id' => 3,
                'user_id' => 3,
                'check_time' => NULL,
                'audit_text' => NULL,
                'audit_time' => NULL,
                'status' => 0,
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            array (
                'item_type' => 1,
                'notification_id' => 4,
                'user_id' => 1,
                'check_time' => NULL,
                'audit_text' => '审核通过，可发送',
                'audit_time' => '2018-09-19 10:00:00',
                'status' => 2,
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            array (
                'item_type' => 2,
                'notification_id' => 4,
                'user_id' => 2,
                'check_time' => NULL,
                'audit_text' => '审核通过，可发送',
                'audit_time' => '2018-09-19 10:00:00',
                'status' => 2,
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            array (
                'item_type' => 0,
                'notification_id' => 4,
                'user_id' => 3,
                'check_time' => NULL,
                'audit_text' => NULL,
                'audit_time' => NULL,
                'status' => 1,
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            array (
                'item_type' => 1,
                'notification_id' => 5,
                'user_id' => 1,
                'check_time' => NULL,
                'audit_text' => '审核通过，可发送',
                'audit_time' => '2018-09-28 10:00:00',
                'status' => 3,
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            array (
                'item_type' => 2,
                'notification_id' => 5,
                'user_id' => 2,
                'check_time' => NULL,
                'audit_text' => '审核通过，可发送',
                'audit_time' => '2018-09-28 10:00:00',
                'status' => 2,
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            array (
                'item_type' => 0,
                'notification_id' => 5,
                'user_id' => 3,
                'check_time' => NULL,
                'audit_text' => NULL,
                'audit_time' => NULL,
                'status' => 1,
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            array (
                'item_type' => 1,
                'notification_id' => 6,
                'user_id' => 2,
                'check_time' => NULL,
                'audit_text' => NULL,
                'audit_time' => NULL,
                'status' => 0,
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            array (
                'item_type' => 0,
                'notification_id' => 6,
                'user_id' => 1,
                'check_time' => NULL,
                'audit_text' => NULL,
                'audit_time' => NULL,
                'status' => 1,
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            array (
                'item_type' => 1,
                'notification_id' => 7,
                'user_id' => 2,
                'check_time' => NULL,
                'audit_text' => NULL,
                'audit_time' => NULL,
                'status' => 0,
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            array (
                'item_type' => 0,
                'notification_id' => 7,
                'user_id' => 1,
                'check_time' => '2018-09-20 10:00:00',
                'audit_text' => NULL,
                'audit_time' => NULL,
                'status' => 2,
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
        ));
    }
}