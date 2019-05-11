<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateOrgUserTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('org_user', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->integer('org_id')->comment('机构id');
			$table->integer('user_id')->comment('用户id');
			$table->integer('role_id')->default(5)->comment('角色id');
			$table->integer('is_default')->comment('是否为默认机构，0为否1为是');
			$table->timestamps();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('org_user');
	}

}
