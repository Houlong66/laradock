<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWorkTransfersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('work_transfers', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('work_id')->comment('工作ID');
            $table->integer('work_item_id')->comment('工作项ID');
            $table->string('work_type')->comment('工作模型类型');
            $table->integer('from_user_id')->comment('转交发起用户ID');
            $table->integer('to_user_id')->comment('转交目标用户ID');
            $table->string('remark')->comment('备注/文字意见');
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
        Schema::dropIfExists('work_transfers');
    }
}
