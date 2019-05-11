<?php

use Illuminate\Database\Seeder;

class GroupsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('groups')->delete();
        
        \DB::table('groups')->insert(array (
            0 => 
            array (
                'id' => 1,
                'org_id' => 1,
                'name' => '工作群A-测试创建任务专用',
                'type' => 0,
                'status' => 1,
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            1 => 
            array (
                'id' => 2,
                'org_id' => 1,
                'name' => '公司3-工作群1-id2',
                'type' => 0,
                'status' => 1,
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            2 => 
            array (
                'id' => 3,
                'org_id' => 1,
                'name' => '公司2-工作群2-id3',
                'type' => 0,
                'status' => 1,
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            3 => 
            array (
                'id' => 4,
                'org_id' => 1,
                'name' => '公司2-工作群3-id4',
                'type' => 0,
                'status' => 1,
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            4 => 
            array (
                'id' => 5,
                'org_id' => 1,
                'name' => '公司4-工作群1-id5',
                'type' => 0,
                'status' => 1,
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            5 => 
            array (
                'id' => 6,
                'org_id' => 1,
                'name' => '公司3-工作群2-id6',
                'type' => 0,
                'status' => 1,
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            6 => 
            array (
                'id' => 7,
                'org_id' => 1,
                'name' => '群组321',
                'type' => 0,
                'status' => 1,
                'created_at' => '2018-09-28 19:20:21',
                'updated_at' => '2018-09-28 19:20:21',
            ),
            7 =>
            array (
                'id' => 8,
                'org_id' => 2,
                'name' => 'org2-group1',
                'type' => 0,
                'status' => 1,
                'created_at' => '2018-09-28 19:20:21',
                'updated_at' => '2018-09-28 19:20:21',
            ),
            8 =>
            array (
                'id' => 9,
                'org_id' => 2,
                'name' => 'org2-group2',
                'type' => 0,
                'status' => 1,
                'created_at' => '2018-09-28 19:20:21',
                'updated_at' => '2018-09-28 19:20:21',
            ),
            9 =>
            array (
                'id' => 10,
                'org_id' => 2,
                'name' => 'org2-group3',
                'type' => 0,
                'status' => 1,
                'created_at' => '2018-09-28 19:20:21',
                'updated_at' => '2018-09-28 19:20:21',
            ),
            10 =>
            array (
                'id' => 11,
                'org_id' => 3,
                'name' => 'org3-group1',
                'type' => 0,
                'status' => 1,
                'created_at' => '2018-09-28 19:20:21',
                'updated_at' => '2018-09-28 19:20:21',
            ),
            11 =>
            array (
                'id' => 12,
                'org_id' => 3,
                'name' => 'org3-group2',
                'type' => 0,
                'status' => 1,
                'created_at' => '2018-09-28 19:20:21',
                'updated_at' => '2018-09-28 19:20:21',
            ),
        ));
        
        
    }
}