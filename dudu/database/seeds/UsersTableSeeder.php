<?php

use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
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

                'openid' => 'oNIu71R_WKzzFOAHGil9H_HBpKok',
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
                'identity' => '测试专家',
                'is_followed' => 1,
                'created_at' => '2018-09-22 12:22:18',
                'updated_at' => '2018-09-22 12:22:18',
            ),
            1 => 
            array (
                'id' => 2,

                'openid' => 'oNIu71R_WKzzFOAHGil9H_HBpKok',
                'sex' => 1,
                'avatar' => 'http://thirdwx.qlogo.cn/mmopen/fmXEzsIMVTicicas1shzokGyYgd7H1cxKBIblPObGXNzedMuddZ3jPFAXUEOD55bHK3vwvSxsOy8goPRfibb4O2Jaibibu0GbIgQ6/132',
                'name' => 'HouLong_',
                'real_name' => NULL,
                'tel' => 15814597072,
                'address' => NULL,
                'uniqueid' => NULL,
                'fixed_tel' => NULL,
                'email' => NULL,
                'qq' => NULL,
                'wechat' => NULL,
                'wechat_qrcode' => NULL,
                'identity' => '测试达人',
                'is_followed' => 1,
                'created_at' => '2018-09-29 10:31:48',
                'updated_at' => '2018-09-29 10:31:48',
            ),
            2 =>
            array (
                'id' => 3,
                'openid' => 'oNIu71R_WKzzFOAHGil9H_HBpKok',
                'sex' => 2,
                'avatar' => 'https://timgsa.baidu.com/timg?image&quality=80&size=b9999_10000&sec=1542189273690&di=6eb111172089466102fa2e2c7241eaf3&imgtype=0&src=http%3A%2F%2Fimg5.duitang.com%2Fuploads%2Fitem%2F201412%2F12%2F20141212014311_jwiC8.jpeg',
                'name' => '女生',
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
                'created_at' => '2018-09-29 10:31:48',
                'updated_at' => '2018-09-29 10:31:48',
            ),
            3 =>
            array (
                'id' => 4,
                'openid' => 'oNIu71R_WKzzFOAHGil9H_HBpKok',
                'sex' => 2,
                'avatar' => 'http://img.zcool.cn/community/0130d15acf6392a80121386742b344.gif@2o.png',
                'name' => '游民',
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
                'created_at' => '2018-09-29 10:31:48',
                'updated_at' => '2018-09-29 10:31:48',
            ),
            4 =>
            array (
                'id' => 5,
                'openid' => 'oNIu71R_WKzzFOAHGil9H_HBpKok',
                'sex' => 1,
                'avatar' => 'https://timgsa.baidu.com/timg?image&quality=80&size=b9999_10000&sec=1547964872072&di=4c637a2211a2e4aa688b6f8a268b5a45&imgtype=0&src=http%3A%2F%2Fimg5.duitang.com%2Fuploads%2Fitem%2F201410%2F29%2F20141029111101_YRuA4.thumb.700_0.jpeg',
                'name' => '路人甲',
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
                'created_at' => '2018-09-29 10:31:48',
                'updated_at' => '2018-09-29 10:31:48',
            ),
            5 =>
            array (
                'id' => 6,
                'openid' => 'oNIu71R_WKzzFOAHGil9H_HBpKok',
                'sex' => 2,
                'avatar' => 'https://ss1.bdstatic.com/70cFuXSh_Q1YnxGkpoWK1HF6hhy/it/u=2912940665,2898206146&fm=26&gp=0.jpg',
                'name' => '路人乙',
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
                'created_at' => '2018-09-29 10:31:48',
                'updated_at' => '2018-09-29 10:31:48',
            ),
            6 =>
            array (
                'id' => 7,
                'openid' => 'oNIu71R_WKzzFOAHGil9H_HBpKok',
                'sex' => 2,
                'avatar' => 'https://timgsa.baidu.com/timg?image&quality=80&size=b9999_10000&sec=1547964966830&di=4fd90579c019dc2d33363a6f463fbbdf&imgtype=0&src=http%3A%2F%2Fc.hiphotos.baidu.com%2Fzhidao%2Fpic%2Fitem%2Fc83d70cf3bc79f3d2a9a3d29b9a1cd11728b29b4.jpg',
                'name' => '士兵甲',
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
                'created_at' => '2018-09-29 10:31:48',
                'updated_at' => '2018-09-29 10:31:48',
            ),
            7 =>
            array (
                'id' => 8,
                'openid' => 'oNIu71R_WKzzFOAHGil9H_HBpKok',
                'sex' => 1,
                'avatar' => 'https://timgsa.baidu.com/timg?image&quality=80&size=b9999_10000&sec=1547965026375&di=3e27e62e951f7bacbdb4825000d74887&imgtype=0&src=http%3A%2F%2Fimg5.duitang.com%2Fuploads%2Fitem%2F201411%2F09%2F20141109121520_acdLj.jpeg',
                'name' => '士兵乙',
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
                'created_at' => '2018-09-29 10:31:48',
                'updated_at' => '2018-09-29 10:31:48',
            ),
        ));
    }
}