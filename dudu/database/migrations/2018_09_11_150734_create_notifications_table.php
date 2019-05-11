<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('org_id')->comment('工作所属机构的id');
            $table->integer('group_id')->nullable()->comment('所属工作组id');
            $table->string('title', 30)->comment('标题');
            $table->text('desc', 65535)->nullable()->comment('内容描述');
            $table->tinyInteger('significance')->default(2)->nullable()->comment('工作重要度， 0普通，1重要，2非常重要');
            $table->dateTime('attachment_last_modified')->default(DB::raw('CURRENT_TIMESTAMP'))->comment('通知附件最后一次更新时间');
            $table->dateTime('send_time')->nullable()->comment('发送时间');
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
        Schema::dropIfExists('notifications');
    }
}
