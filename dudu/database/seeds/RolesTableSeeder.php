<?php

use Illuminate\Database\Seeder;

class RolesTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('roles')->delete();
        
        \DB::table('roles')->insert(array (
            array (
                'name' => '超级管理员',
                'type' => 1,
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            array (
                'name' => '系统管理员',
                'type' => 1,
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            array (
                'name' => '任务管理员',
                'type' => 1,
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            array (
                'name' => '内部成员',
                'type' => 1,
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            array (
                'name' => '群组创建人',
                'type' => 2,
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            array (
                'name' => '任务发放人',
                'type' => 2,
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            array (
                'name' => '任务签收人',
                'type' => 2,
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            array (
                'name' => '部门/单位领导',
                'type' => 2,
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            array (
                'name' => '日程共享人',
                'type' => 3,
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            array (
                'name' => '日程查看人',
                'type' => 3,
                'created_at' => NULL,
                'updated_at' => NULL,
            ),

        ));
        
        
    }
}