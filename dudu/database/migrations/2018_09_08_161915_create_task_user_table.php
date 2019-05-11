<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateTaskUserTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('task_user', function(Blueprint $table)
		{
            $table->increments('id')->comment('自增id');
            $table->tinyInteger('item_type')->comment('关联数据项类型，0我接收的，1我发送的，2要让我审核的，3我监督的');
			$table->integer('task_id')->comment('所属工作id');
            $table->integer('dept_id')->comment('单位id，个人任务则为0,非个人任务时，item_type=0的，则值为部门id，item_type=1的，则值为1');
			$table->integer('user_id')->nullable()->comment('关联用户的id');
			$table->integer('receiver_id')->nullable()->comment('签收人id');

            $table->integer('work_send_id')->nullable()->comment('申请转发人');
            $table->text('note_text', 65535)->nullable()->comment('申请备注内容');

            $table->dateTime('receive_time')->nullable()->comment('签收时间');
            $table->dateTime('report_deadline')->nullable()->comment('上报截止时间');
            $table->text('report_text', 65535)->nullable()->comment('上报内容');
            $table->dateTime('report_time')->nullable()->comment('上报时间');
            $table->text('audit_text', 65535)->nullable()->comment('任务是否可发送的审批内容(item_type为2),任务的上报审核说明（item_type为0）');
            $table->dateTime('audit_time')->nullable()->comment('审核时间');
            $table->tinyInteger('status')->comment('任务状态(item_type为0或3)：0未发送，1未签收，2未上报，3已上报，4已办结；
            流转审批状态(item_type为1和2): 0无需审核，1待审核，2审核通过，3审核不通过');
			$table->timestamps();
			$table->softDeletes();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('task_user');
	}

}
