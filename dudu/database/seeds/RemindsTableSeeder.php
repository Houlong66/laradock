<?php

use Illuminate\Database\Seeder;

class RemindsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \DB::table('reminds')->delete();
        
        \DB::table('reminds')->insert(array (
            array(
                'remind_at' => '2019-01-22 20:50:00',
                'schedule_id' => 1,
                'is_solar' => 1,
            ),
            array(
                'remind_at' => '2019-01-22 20:50:00',
                'schedule_id' => 2,
                'is_solar' => 1,
            ),
            array(
                'remind_at' => '2019-01-22 20:50:00',
                'schedule_id' => 3,
                'is_solar' => 1,
            ),
            array(
                'remind_at' => '2019-01-22 12:50:00',
                'schedule_id' => 4,
                'is_solar' => 1,
            ),
            array(
                'remind_at' => '2018-12-17 12:00:00',
                'schedule_id' => 5,
                'is_solar' => 0,
            ),
        ));
    }
}
