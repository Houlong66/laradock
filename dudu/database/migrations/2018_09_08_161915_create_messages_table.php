<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateMessagesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('messages', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('title', 30)->comment('标题');
			$table->text('content', 65535)->comment('内容');
			$table->string('status', 30)->default('0')->comment('
				状态，0为未读，1为已读，2为待办
				默认值为0
			');
			$table->integer('type')->comment('类型，
				工作（任务）为1，
				工作（通知）为2，
				工作（请示）为3，
                日程为4，
                组织为5，
                用户为6，
                系统为7
            ');
            $table->integer('send_id')->comment('发送人的ID')->default(0);
			$table->integer('subtype')->default(0)->comment('子类型，
                type1 工作（任务）
                【新任务提醒为1，任务待审核为2，任务审核结果为3，任务流转审批待审核为4，任务流转审批审核结果为5，任务到期提醒为6】，
                type2 工作（通知）
				【新通知提醒为1，流转审批待审核为2，流转审批审核结果为3】，
                type3 工作（请示）
                【待处理请示为1，已处理请示为2】，
                type4 日程
                【新增日程提醒为1，日程到期提醒为2】，
                type5 组织
                【机构申请结果消息为1，机构对接申请消息为2，机构对接结果消息为3，机构转移申请消息为4，机构转移结果消息为5】，
                type6 用户
                【加入机构申请为1，加入机构结果为2，加入群组申请为3，加入群组结果为4，交换名片申请为5，名片交换结果为6,讨论板消息@为7,主动退群为8,主动申请加群为9,主动同意人家加群为10】，
                type7 系统
                【默认值为0，1为反馈消息】
            ');
			$table->text('params', 65535)->nullable()->comment('相关参数，如消息对应的工作ID等，用于页面的定位，在需要的时候以JSON的形式传输，默认为NULL不用处理');
			$table->string('url', 255)->default('')->comment('对应微信消息模板详情跳转url');
			$table->integer('user_id')->comment('消息对应的用户ID');
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
		Schema::drop('messages');
	}

}
