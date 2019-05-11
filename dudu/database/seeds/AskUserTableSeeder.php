<?php

use Illuminate\Database\Seeder;

class AskUserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \DB::table('ask_user')->delete();

        \DB::table('ask_user')->insert(array (
            array (
                'item_type' => 1,
                'ask_id' => 1,
                'user_id' => 1,
                'audit_text' => NULL,
                'audit_time' => NULL,
                'status' => 0,
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            array (
                'item_type' => 0,
                'ask_id' => 1,
                'user_id' => 2,
                'audit_text' => NULL,
                'audit_time' => NULL,
                'status' => 0,
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
        ));
    }
}
