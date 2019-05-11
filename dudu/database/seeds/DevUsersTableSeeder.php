<?php

use Illuminate\Database\Seeder;

class DevUsersTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('users')->delete();
        
        \DB::table('users')->insert(array (
            0 => 
            array (
                'id' => 1,
                'openid' => 'o0NnIt8_sXOdX3mCaDWkS8vCid3s',
                'sex' => 1,
                'avatar' => 'http://thirdwx.qlogo.cn/mmopen/wJibWkqN1bUN07Wm82u2nqNic5dbqfsZiaf1j6atGYx3wybEUoEzqnEbgWPwt1Tzl1LHRQ11xTB2dqkAX88maZMYrme6We5qVx2/132',
                'name' => '徐曹植',
                'real_name' => NULL,
                'tel' => 15814597072,
                'address' => NULL,
                'uniqueid' => NULL,
                'fixed_tel' => NULL,
                'email' => NULL,
                'qq' => NULL,
                'wechat' => NULL,
                'wechat_qrcode' => NULL,
                'identity' => NULL,
                'is_followed' => 1,
                'created_at' => '2018-09-22 12:22:18',
                'updated_at' => '2018-09-22 12:22:18',
            ),
            1 => 
            array (
                'id' => 2,
                'openid' => 'o0NnIt9kHJVmoLRRGQT0rz5T-0t4',
                'sex' => 1,
                'avatar' => 'http://thirdwx.qlogo.cn/mmopen/Af4uODI10sA9vO51DgEibgSTEjpZ9TxMery3rJmHic985ERDfsOlRE5fXoOAyctGZuXZMpPcqvwjwKpgnbqicuwG0WUAXltt8YP/132',
                'name' => '阿兵哥',
                'real_name' => NULL,
                'tel' => 13302291681,
                'address' => NULL,
                'uniqueid' => NULL,
                'fixed_tel' => NULL,
                'email' => NULL,
                'qq' => NULL,
                'wechat' => NULL,
                'wechat_qrcode' => NULL,
                'identity' => NULL,
                'is_followed' => 1,
                'created_at' => '2018-09-29 10:31:48',
                'updated_at' => '2018-09-29 10:31:48',
            ),
            2 =>
            array (
                'id' => 3,
                'openid' => 'o3oiw58tpMJEw7iOEIJdmB6J-qLU',
                'sex' => 1,
                'avatar' => 'http://thirdwx.qlogo.cn/mmopen/Srqg995icfNkC7AMwKGkkFxn8TyeYiazKPeXH2WJ8ibaOCE8a2CA29RDJjJLw20o2LxJtJvBFqlr5eOe4hLic7wuYd6rSLHj0uzF/132',
                'name' => '林镜亮',
                'real_name' => NULL,
                'tel' => NULL,
                'address' => NULL,
                'uniqueid' => NULL,
                'fixed_tel' => NULL,
                'email' => NULL,
                'qq' => NULL,
                'wechat' => NULL,
                'wechat_qrcode' => NULL,
                'identity' => NULL,
                'is_followed' => 1,
                'created_at' => '2018-09-29 10:31:48',
                'updated_at' => '2018-09-29 10:31:48',
            ),
        ));
    }
}