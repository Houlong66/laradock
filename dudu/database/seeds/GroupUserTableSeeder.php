<?php

use Illuminate\Database\Seeder;

class GroupUserTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('group_user')->delete();
        
        \DB::table('group_user')->insert(array (
            array (
                'group_id' => 1,
                'user_id' => 1,
                'role_id' => 6,
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            array (
                'group_id' => 1,
                'user_id' => 2,
                'role_id' => 5,
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            array (
                'group_id' => 1,
                'user_id' => 3,
                'role_id' => 7,
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            array (
                'group_id' => 2,
                'user_id' => 2,
                'role_id' => 5,
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            array (
                'group_id' => 2,
                'user_id' => 3,
                'role_id' => 8,
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            array (
                'group_id' => 1,
                'user_id' => 4,
                'role_id' => 6,
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            array (
                'group_id' => 8,
                'user_id' => 6,
                'role_id' => 8,
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            array (
                'group_id' => 8,
                'user_id' => 4,
                'role_id' => 7,
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            array (
                'group_id' => 11,
                'user_id' => 7,
                'role_id' => 5,
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            array (
                'group_id' => 11,
                'user_id' => 8,
                'role_id' => 7,
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
        ));
        
        
    }
}