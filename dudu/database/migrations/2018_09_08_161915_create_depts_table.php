<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateDeptsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('depts', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('org_id')->comment('机构id');
			$table->string('name', 40)->comment('组织单位名称');
			$table->boolean('level')->default(0)->comment('级别, 0 表示本部门, 1为下级单位');
			$table->boolean('status')->default(0)->comment('状态, 0 表示停用, 1为启用');
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
		Schema::drop('depts');
	}

}
