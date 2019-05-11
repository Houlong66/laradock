<?php

use Illuminate\Database\Seeder;

class AskTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \DB::table('ask_types')->delete();

        \DB::table('ask_types')->insert(array (
            array(
                'org_id' => 1,
                'name' => '工作'
            ),
            array(
                'org_id' => 1,
                'name' => '请假'
            ),
            array(
                'org_id' => 1,
                'name' => '用车'
            ),
            array(
                'org_id' => 2,
                'name' => '工作'
            ),
            array(
                'org_id' => 2,
                'name' => '请假'
            ),
            array(
                'org_id' => 2,
                'name' => '用车'
            ),
            array(
                'org_id' => 3,
                'name' => '工作'
            ),
            array(
                'org_id' => 3,
                'name' => '请假'
            ),
            array(
                'org_id' => 3,
                'name' => '用车'
            ),
        ));
    }
}
