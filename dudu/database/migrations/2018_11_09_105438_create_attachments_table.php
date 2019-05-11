<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAttachmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attachments', function (Blueprint $table) {
            $table->increments('id');
            $table->string('original_name')->comment('附件原始文件名');
            $table->string('total_path')->comment('附件存储路径');
            $table->integer('works_id')->nullable()->comment('附件所属工作项id');
            $table->string('works_type')->comment('附件所属模型类型,1任务，2通知，3请示，4反馈');
            $table->integer('works_item_id')->comment('工作上报项的id,0为任务附件');
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
        Schema::dropIfExists('attachments');
    }
}
