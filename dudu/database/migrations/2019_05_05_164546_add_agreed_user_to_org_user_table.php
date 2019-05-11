<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAgreedUserToOrgUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('org_user', function (Blueprint $table) {
            $table->integer('agreed_user')->default(1)->comment('同意进入的用户');
            $table->dateTime('agreed_time')->default(date("Y-m-d H:i:s"));
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('org_user', function (Blueprint $table) {
               $table->dropColumn(['agreed_user', 'agreed_time']);
        });
    }
}
