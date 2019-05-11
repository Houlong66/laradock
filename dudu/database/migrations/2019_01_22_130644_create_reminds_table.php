<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRemindsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reminds', function (Blueprint $table) {
            $table->increments('id');
            $table->dateTime('remind_at')->comment("提醒的时间");
            $table->boolean('is_solar')->default(1);
            $table->integer('schedule_id');
            $table->integer('defalut_id')->default(0)->comment("其他类型id");
            $table->string("type")->default("")->comment("类别");
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
        Schema::dropIfExists('reminds');
    }
}
