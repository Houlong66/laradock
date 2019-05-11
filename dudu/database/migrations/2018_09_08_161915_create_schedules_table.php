<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateSchedulesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('schedules', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('creater_id')->comment('创建者ID');
			$table->integer('user_id')->comment('日程对应的用户ID（不一定是创建者）');
			$table->integer('type')->comment('日程类型，日程为1，提醒为2，纪念日为3');
			$table->string('name', 50)->comment('日程标题');
			$table->text('comment', 65535)->nullable()->comment('日程备注');
			$table->dateTime('start_at')->nullable()->comment('开始时间');
			$table->dateTime('end_at')->nullable()->comment('结束时间');
			// $table->dateTime('remind_at')->nullable()->comment('提醒时间');
			$table->boolean('is_solar')->default(1)->comment('阳历阴历 阳历1 阴历0');
			$table->boolean('public')->comment('是否公开');
			$table->boolean('fullday')->default(0)->comment('是否全天事件，0为否1为是，默认为0');
			$table->integer('notification_id')->nullable()->comment('是否关联通知，默认为null');
			$table->boolean('is_active')->default(1)->comment('是否启动日程，0为否，1为是');
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
		Schema::drop('schedules');
	}

}
