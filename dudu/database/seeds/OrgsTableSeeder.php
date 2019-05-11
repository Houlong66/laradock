<?php

use Illuminate\Database\Seeder;

class OrgsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('orgs')->delete();
        
        \DB::table('orgs')->insert(array (
            0 => 
            array (
                'id' => 1,
                'code' => '20190101ZXCVBN',
                'name' => '华南理工大学',
                'password' => '$2y$10$67.34GjIalmraccsJj118OUZ7nYU9BmC9v1jqWeb8cQcWlPif7Yea',
                'parent_id' => 0,
                'region' => NULL,
                'status' => 1,
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            1 =>
            array (
                'id' => 2,
                'code' => '20190202QWERTY',
                'name' => '华工计算机学院',
                'password' => '$2y$10$JeiXxyfXgNtvT9J6UEp6kOd79jHh5dCDMEhGzZQcbGbFiiSUad4mG',
                'parent_id' => 1,
                'region' => NULL,
                'status' => 1,
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            2 =>
            array (
                'id' => 3,
                'code' => '20190303ASDFGH',
                'name' => '华工人工智能实验室',
                'password' => '$2y$10$SWMJT5DSoxqkf8vhY4OjU.Dh24PospnmrfOSzsea42ZRJLODVlx6.',
                'parent_id' => 2,
                'region' => NULL,
                'status' => 1,
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
        ));
        
        
    }
}