<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNotificationUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notification_user', function (Blueprint $table) {
            $table->increments('id');
            $table->tinyInteger('item_type')->comment('关联数据项类型，0我接收的，1我发送的，2要让我审核的');
            $table->integer('notification_id')->comment('所属工作id');

            $table->integer('user_id')->nullable()->comment('关联用户的id');

            $table->integer('work_send_id')->nullable()->comment('申请转发人');
            $table->text('note_text', 65535)->nullable()->comment('申请备注内容');

            $table->dateTime('check_time')->nullable()->comment('查看时间');

            $table->text('audit_text', 65535)->nullable()->comment('审核内容');
            $table->dateTime('audit_time')->nullable()->comment('审核时间');
            $table->tinyInteger('status')->comment('工作状态(item_type为0)， 任务：0未发送，1未读，2已读;
            流转审批状态(item_type为1或2)，0无需审核，1待审核，2审核通过，3审核不通过');

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
        Schema::dropIfExists('notification_user');
    }
}
