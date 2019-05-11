<?php

use Illuminate\Database\Seeder;

class OrgUserTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('org_user')->delete();
        
        \DB::table('org_user')->insert(array (
            array (
                'org_id' => 1,
                'user_id' => 1,
                'role_id' => 1,
                'is_default' => 1,
                'created_at' => NULL,
                'updated_at' => NULL,
                'agreed_user' => 1,
                'agreed_time' => date("Y-m-d H:i:s")
            ),
            array (
                'org_id' => 1,
                'user_id' => 2,
                'role_id' => 2,
                'is_default' => 1,
                'created_at' => NULL,
                'updated_at' => NULL,
                'agreed_user' => 1,
                'agreed_time' => date("Y-m-d H:i:s")
            ),
            array (
                'org_id' => 1,
                'user_id' => 3,
                'role_id' => 4,
                'is_default' => 1,
                'created_at' => NULL,
                'updated_at' => NULL,
                'agreed_user' => 1,
                'agreed_time' => date("Y-m-d H:i:s")
            ),
            array (
                'org_id' => 2,
                'user_id' => 5,
                'role_id' => 1,
                'is_default' => 1,
                'created_at' => NULL,
                'updated_at' => NULL,
                'agreed_user' => 1,
                'agreed_time' => date("Y-m-d H:i:s")
            ),
            array (
                'org_id' => 2,
                'user_id' => 6,
                'role_id' => 2,
                'is_default' => 1,
                'created_at' => NULL,
                'updated_at' => NULL,
                'agreed_user' => 1,
                'agreed_time' => date("Y-m-d H:i:s")
            ),
            array (
                'org_id' => 1,
                'user_id' => 4,
                'role_id' => 4,
                'is_default' => 1,
                'created_at' => NULL,
                'updated_at' => NULL,
                'agreed_user' => 1,
                'agreed_time' => date("Y-m-d H:i:s")
            ),
            array (
                'org_id' => 3,
                'user_id' => 7,
                'role_id' => 1,
                'is_default' => 1,
                'created_at' => NULL,
                'updated_at' => NULL,
                'agreed_user' => 1,
                'agreed_time' => date("Y-m-d H:i:s")
            ),
            array (
                'org_id' => 3,
                'user_id' => 8,
                'role_id' => 2,
                'is_default' => 1,
                'created_at' => NULL,
                'updated_at' => NULL,
                'agreed_user' => 1,
                'agreed_time' => date("Y-m-d H:i:s")
            ),
        ));
        
        
    }
}