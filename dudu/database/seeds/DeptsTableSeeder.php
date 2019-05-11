<?php

use Illuminate\Database\Seeder;

class DeptsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('depts')->delete();
        
        \DB::table('depts')->insert(array (
            array (
                'org_id' => 1,
                'name' => '华南理工大学',
                'level' => 0,
                'status' => 1,
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            array (
                'org_id' => 1,
                'name' => '教务处',
                'level' => 0,
                'status' => 1,
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            array (
                'org_id' => 1,
                'name' => '后勤处',
                'level' => 0,
                'status' => 1,
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            array (
                'org_id' => 1,
                'name' => '计算机学院',
                'level' => 1,
                'status' => 1,
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            array (
                'org_id' => 1,
                'name' => '软件学院',
                'level' => 1,
                'status' => 1,
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            array (
                'org_id' => 1,
                'name' => '新传学院',
                'level' => 1,
                'status' => 1,
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            array (
                'org_id' => 1,
                'name' => '国际学院',
                'level' => 1,
                'status' => 1,
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            array (
                'org_id' => 1,
                'name' => '医学院',
                'level' => 0,
                'status' => 1,
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            array (
                'org_id' => 2,
                'name' => '华工计算机学院',
                'level' => 0,
                'status' => 1,
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            array (
                'org_id' => 2,
                'name' => '区块链实验室',
                'level' => 0,
                'status' => 1,
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            array (
                'org_id' => 2,
                'name' => '人工智能实验室',
                'level' => 0,
                'status' => 1,
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            array (
                'org_id' => 3,
                'name' => '华工人工智能实验室',
                'level' => 0,
                'status' => 1,
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            array (
                'org_id' => 3,
                'name' => '机器学习',
                'level' => 0,
                'status' => 1,
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
        ));
    }
}