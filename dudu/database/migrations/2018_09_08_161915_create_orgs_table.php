<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateOrgsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('orgs', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('code', 14)->nullable();
			$table->string('name', 40);
			$table->string('password', 60);
			$table->string('region', 40)->nullable()->comment('地区');
			$table->boolean('status')->default(0)->comment('状态, 0 表示停用, 1 为启用，2 为待审核');
			$table->integer('parent_id')->default(0)->comment('父级机构');
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
		Schema::drop('orgs');
	}

}
