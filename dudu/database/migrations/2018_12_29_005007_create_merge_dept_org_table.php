<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMergeDeptOrgTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('merge_dept_org', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('dept_id')->comment('部门id');
            $table->integer('org_id')->comment('下级对接机构');
            $table->integer('dept_org_id')->comment('部门所在机构id');
            $table->boolean('is_active')->default(0)->comment('是否生效, 0否， 1是');
            $table->softDeletes();
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
        Schema::dropIfExists('merge_dept_org');
    }
}
