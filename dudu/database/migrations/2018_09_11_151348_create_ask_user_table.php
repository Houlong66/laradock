<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAskUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ask_user', function (Blueprint $table) {
            $table->increments('id');
            $table->tinyInteger('item_type')->comment('关联数据项类型，0我接收的，1我发送的');
            $table->integer('work_send_id')->nullable()->comment('申请转发人');
            $table->text('note_text', 65535)->nullable()->comment('申请备注内容');
            $table->integer('ask_id')->comment('所属工作id');
            $table->integer('user_id')->nullable()->comment('关联用户的id');
            $table->text('audit_text', 65535)->nullable()->comment('批复内容');
            $table->dateTime('audit_time')->nullable()->comment('批复时间');
            $table->tinyInteger('status')->nullable()->comment('请示状态， 0未处理，1已处理，2废弃项（请示已有其他人审批）');
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
        Schema::dropIfExists('ask_user');
    }
}
