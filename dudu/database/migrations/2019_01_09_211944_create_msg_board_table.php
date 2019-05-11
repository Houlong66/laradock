<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMsgBoardTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('msg_boards', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('foreign_id')->comment('任务,请示,通知的id');
            $table->string('content', 300)->comment('讨论内容');
            $table->integer('user_id')->comment('发表的用户id');
            $table->string('at_sign')->nullable()->comment('@用户');
            $table->integer('type')->comment('类型, 任务/通知/请示 分别为1,2,3');
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
        Schema::dropIfExists('msg_boards');
    }
}
