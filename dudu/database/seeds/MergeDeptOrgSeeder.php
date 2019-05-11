<?php

use Illuminate\Database\Seeder;

class MergeDeptOrgSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \DB::table('merge_dept_org')->delete();
        
        \DB::table('merge_dept_org')->insert(array (
            array(
                'dept_id' => 1,
                'org_id' => 2,
                'dept_org_id' => 1,
                'is_active' => 1,
                'deleted_at' => NULL,
                'created_at' => NULL,
                'updated_at' => NULL
            ),
            array(
                'dept_id' => 9,
                'org_id' => 3,
                'dept_org_id' => 2,
                'is_active' => 1,
                'deleted_at' => NULL,
                'created_at' => NULL,
                'updated_at' => NULL
            ),
        ));
    }
}
