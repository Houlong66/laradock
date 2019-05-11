<?php

use Illuminate\Database\Seeder;

class AdminUsersTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('admin_users')->delete();
        
        \DB::table('admin_users')->insert(array (
            0 => 
            array (
                'id' => 1,
                'username' => 'admin',
                'password' => '$2y$10$DDyNYmAOYYRBGY9QGbSA1uC5nLJFzgRnslXIoazfGiqROjBPbNgFK',
                'remember_token' => 'QxtcvPe3SDw3LD13es1qgdUxFaW9QOCZaLG3SM9KvWkreYKXkVH38WU1yzXE',
                'created_at' => '2018-11-21 10:57:39',
                'updated_at' => '2018-11-21 10:57:39',
            ),
        ));
        
        
    }
}