<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAsksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('asks', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('org_id')->comment('请示所属机构的id');
            $table->string('title', 30)->comment('标题');
            $table->text('desc', 65535)->nullable()->comment('内容描述');
            $table->tinyInteger('ask_type')->comment('关联AskType模型');
            $table->dateTime('attachment_last_modified')->default(DB::raw('CURRENT_TIMESTAMP'))->comment('请示附件最后一次更新时间');
            $table->dateTime('send_time')->nullable()->comment('发送时间');
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
        Schema::dropIfExists('asks');
    }
}
