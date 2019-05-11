<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('files', function (Blueprint $table) {
            $table->increments('id');
            $table->string('token', 64)->unique()->comment('文件下载 token（实际上是一个 SHA256 哈希值）');
            $table->integer('attachment_id')->unique()->nullable()->comment('单文件关联的附件表 ID');
            $table->string('zip_file_path', 300)->unique()->nullable()->comment('压缩文件的路径');
            $table->string('zip_display_name', 300)->nullable()->comment('压缩文件下载后显示的文件名');
            $table->dateTime('expire_at')->comment('token 过期时间');
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
        Schema::dropIfExists('files');
    }
}
