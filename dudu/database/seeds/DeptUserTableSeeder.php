<?php

use Illuminate\Database\Seeder;

class DeptUserTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('dept_user')->delete();
        
        \DB::table('dept_user')->insert(array (
            array (
                'dept_id' => 2,
                'user_id' => 1,
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            array (
                'dept_id' => 1,
                'user_id' => 2,
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            array (
                'dept_id' => 2,
                'user_id' => 2,
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            array (
                'dept_id' => 3,
                'user_id' => 3,
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            array (
                'dept_id' => 4,
                'user_id' => 4,
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            array (
                'dept_id' => 4,
                'user_id' => 2,
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            array (
                'dept_id' => 4,
                'user_id' => 3,
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            array (
                'dept_id' => 11,
                'user_id' => 5,
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            array (
                'dept_id' => 11,
                'user_id' => 6,
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            array (
                'dept_id' => 12,
                'user_id' => 7,
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            array (
                'dept_id' => 12,
                'user_id' => 8,
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            array (
                'dept_id' => 13,
                'user_id' => 8,
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
        ));

    }
}