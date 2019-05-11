<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call(OrgUserTableSeeder::class);
        $this->call(OrgsTableSeeder::class);
//        $this->call(AskTypeSeeder::class);
        $this->call(DeptsTableSeeder::class);
        $this->call(DeptUserTableSeeder::class);
        $this->call(GroupUserTableSeeder::class);
        $this->call(GroupsTableSeeder::class);
//        $this->call(MergeDeptOrgSeeder::class);



//       $this->call(TaskUserTableSeeder::class);
//       $this->call(TasksTableSeeder::class);
//        $this->call(NotificationUserTableSeeder::class);
//        $this->call(NotificationsTableSeeder::class);
//        $this->call(AskUserTableSeeder::class);
//        $this->call(AsksTableSeeder::class);
//      $this->call(SchedulesTableSeeder::class);
//       $this->call(RemindsTableSeeder::class);
//        $this->call(MessagesTableSeeder::class);
        $this->call(UsersTableSeeder::class);
//        $this->call(DevUsersTableSeeder::class);
//        $this->call(ProdUsersTableSeeder::class);
        $this->call(RolesTableSeeder::class);
        $this->call(AdminUsersTableSeeder::class);
//        $this->call(QuantityMockSeeder::class);
    }
}
