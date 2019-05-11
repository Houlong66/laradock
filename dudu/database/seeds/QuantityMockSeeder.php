<?php

use Illuminate\Database\Seeder;

class QuantityMockSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // insert users
        for($j = 1; $j < 4; $j++){
            $users = [];
            for($i=1; $i < 51; $i++){
                $users[] = [
                    'openid' => 'oQzNL0wQCOA3qT_niUORE_aqNtNk',
                    'sex' => 1,
                    'avatar' => 'http://thirdwx.qlogo.cn/mmopen/fmXEzsIMVTicicas1shzokGyYgd7H1cxKBIblPObGXNzedMuddZ3jPFAXUEOD55bHK3vwvSxsOy8goPRfibb4O2Jaibibu0GbIgQ6/132',
                    'name' => 'org_'.$j.'_user'.$i,
                    'real_name' => NULL,
                    'tel' => NULL,
                    'address' => NULL,
                    'uniqueid' => NULL,
                    'fixed_tel' => NULL,
                    'email' => NULL,
                    'qq' => NULL,
                    'wechat' => NULL,
                    'wechat_qrcode' => NULL,
                    'identity' => NULL,
                    'is_followed' => 1,
                    'created_at' => '2018-09-29 10:31:48',
                    'updated_at' => '2018-09-29 10:31:48',
                ];
            }
            \DB::table('users')->insert($users);
        }

        // insert org
        $org_users = [];
        for($i = 9; $i < 59; $i++){
            $org_users[] = [
                'org_id' => 1,
                'user_id' => $i,
                'role_id' => 4,
                'is_default' => 1
            ];
        }
        \DB::table('org_user')->insert($org_users);
        $org_users = [];
        for($i = 59; $i < 109; $i++){
            $org_users[] = [
                'org_id' => 2,
                'user_id' => $i,
                'role_id' => 4,
                'is_default' => 1
            ];
        }
        \DB::table('org_user')->insert($org_users);
        $org_users = [];
        for($i = 109; $i < 159; $i++){
            $org_users[] = [
                'org_id' => 3,
                'user_id' => $i,
                'role_id' => 4,
                'is_default' => 1
            ];
        }
        \DB::table('org_user')->insert($org_users);

        // insert dept
        $dept_users = [];
        for($i=9; $i < 59; $i++){
            $dept_users[] = [
                'dept_id' => random_int(1, 3),
                'user_id' => $i
            ];
        }
        \DB::table('dept_user')->insert($dept_users);
        $dept_users = [];
        for($i=59; $i < 109; $i++){
            $dept_users[] = [
                'dept_id' => random_int(9, 10),
                'user_id' => $i
            ];
        }
        \DB::table('dept_user')->insert($dept_users);
        $dept_users = [];
        for($i=109; $i < 159; $i++){
            $dept_users[] = [
                'dept_id' => random_int(12, 13),
                'user_id' => $i
            ];
        }
        \DB::table('dept_user')->insert($dept_users);

        //insert group
        $group_users = [];
        for($i=9; $i < 59; $i++){
            $group_users[] = [
                'group_id' => 1,
                'user_id' => $i,
                'role_id' => 7
            ];
        }
        \DB::table('group_user')->insert($group_users);
        $group_users = [];
        for($i=59; $i < 109; $i++){
            $group_users[] = [
                'group_id' => 8,
                'user_id' => $i,
                'role_id' => 7
            ];
        }
        \DB::table('group_user')->insert($group_users);
        $group_users = [];
        for($i=109; $i < 159; $i++){
            $group_users[] = [
                'group_id' => 11,
                'user_id' => $i,
                'role_id' => 7
            ];
        }
        \DB::table('group_user')->insert($group_users);
        // 子孙机构加入父级群组
        $group_users = [];
        for($i = 59; $i < 69; $i++){
            $group_users[] = [
                'group_id' => 1,
                'user_id' => $i,
                'role_id' => 7
            ];
        }
        for($i = 109; $i < 119; $i++){
            $group_users[] = [
                'group_id' => 1,
                'user_id' => $i,
                'role_id' => 7
            ];
        }
        \DB::table('group_user')->insert($group_users);
        
    }
}
