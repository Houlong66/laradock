<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateGroupsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('groups', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('org_id')->comment('机构id');
			$table->string('name', 40)->comment('工作群名称');
			$table->boolean('type')->default(0)->comment('类型, 0 表示工作通知群, 1 表示日历群');
			$table->boolean('status')->default(0)->comment('0表示关闭，1 表示启用');
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
		Schema::drop('groups');
	}

}
