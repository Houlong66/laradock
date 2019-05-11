<?php

use Illuminate\Database\Seeder;

class SchedulesTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('schedules')->delete();
        
        \DB::table('schedules')->insert(array (
            0 => 
            array (
                'id' => 1,
                'creater_id' => 1,
                'user_id' => 1,
                'type' => 1,
                'name' => '日程A',
                'comment' => '日程A测试',
                'start_at' => '2019-01-22 19:26:00',
                'end_at' => '2019-01-30 21:00:00',
                'public' => 1,
                'fullday' => 0,
                'is_solar' => 1,
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            1 => 
            array (
                'id' => 2,
                'creater_id' => 3,
                'user_id' => 1,
                'type' => 2,
                'name' => '提醒B',
                'comment' => '提醒B测试',
                'start_at' => '2019-01-22 19:26:00',
                'end_at' => '2019-01-23 :00:00',
                'public' => 1,
                'fullday' => 0,
                'is_solar' => 1,
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            2 => 
            array (
                'id' => 3,
                'creater_id' => 3,
                'user_id' => 1,
                'type' => 2,
                'name' => '日程C',
                'comment' => '日程C测试',
                'start_at' => '2019-01-22 00:00:00',
                'end_at' => '2019-01-26 23:59:59',
                'public' => 1,
                'fullday' => 1,
                'is_solar' => 1,
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            3 => 
            array (
                'id' => 4,
                'creater_id' => 1,
                'user_id' => 1,
                'type' => 3,
                'name' => '纪念日D',
                'comment' => '纪念日D测试',
                'start_at' => '2019-01-22 00:00:00',
                'end_at' => '2019-01-22 23:59:59',
                'public' => 1,
                'fullday' => 1,
                'is_solar' => 1,
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            4 => 
            array (
                'id' => 5,
                'creater_id' => 1,
                'user_id' => 1,
                'type' => 3,
                'name' => '纪念日-农历',
                'comment' => '纪念日-农历-测试',
                'start_at' => '2018-12-17 00:00:00',
                'end_at' => '2018-12-17 23:59:59',
                'public' => 1,
                'fullday' => 1,
                'is_solar' => 0,
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
        ));
        
        
    }
}