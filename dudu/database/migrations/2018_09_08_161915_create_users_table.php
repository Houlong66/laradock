<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateUsersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('users', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->string('openid', 40);
			$table->integer('sex')->default(0)->comment('性别，1为男2为女，默认未设置为0');
			$table->string('avatar', 500)->default('/images/default_avatar.png')->comment('头像');
			$table->string('name', 16)->nullable()->comment('微信昵称');
			$table->string('real_name', 16)->nullable()->comment('真实姓名');
			$table->string('tel', 12)->nullable();
			$table->string('address', 50)->nullable()->comment('通信地址');
			$table->string('uniqueid', 50)->nullable()->comment('微信uniqueid，暂时不设置');
			$table->string('fixed_tel', 15)->nullable()->comment('固定电话');
			$table->string('email', 50)->nullable()->comment('邮箱');
			$table->string('qq', 15)->nullable()->comment('QQ');
			$table->string('wechat', 50)->nullable()->comment('微信号');
			$table->string('wechat_qrcode', 100)->nullable()->comment('微信二维码');
			$table->string('identity', 16)->nullable()->comment('单位职务');
			$table->integer('is_followed')->default(0)->comment('是否关注，0未关注，1为关注');
			$table->dateTime('first_follow_time')->comment('首次关注时间')->nullable();
			$table->dateTime('follow_time')->comment('关注时间')->nullable();
			$table->dateTime('unfollow_time')->comment('取关时间')->nullable();
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
		Schema::drop('users');
	}

}
