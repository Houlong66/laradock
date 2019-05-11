<?php

use Illuminate\Database\Seeder;

class ProdUsersTableSeeder extends Seeder
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
            array (
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
            array (
                'openid' => 'o0NnIt3e-5ynPIPN88GSlBirf2us',
                'sex' => 1,
                'avatar' => 'http://thirdwx.qlogo.cn/mmopen/Af4uODI10sA9vO51DgEibgSTEjpZ9TxMery3rJmHic985ERDfsOlRE5fXoOAyctGZuXZMpPcqvwjwKpgnbqicuwG0WUAXltt8YP/132',
                'name' => '阿兵哥',
                'real_name' => NULL,
                'tel' => 13570063061,
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
            array (
                'openid' => 'o0NnItztB0P2wIjDjzjdRlwnzxm8',
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
            array (
                'openid' => 'o3oiw55fh5921toUMQAUQeAeFTYw',
                'sex' => 0,
                'avatar' => 'http://thirdwx.qlogo.cn/mmopen/Srqg995icfNnKZ8iauuu0xic8dfXBElnibuT98jdNgxfcaaPP2LCibDZuCZn6S8ZOXoyEiaPJyoY4SLKC7wiaO0FzibyhVKgicHvTP1MJ/132',
                'name' => '123',
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
            array (
                'openid' => 'o3oiw56OCzMTuFIylIDdrsk-v8FE',
                'sex' => 0,
                'avatar' => 'http://thirdwx.qlogo.cn/mmopen/XlQDRa2JCkncdx5G726WjbUEw6DhEIo2icrk41oIwjibk2iaTpytsFYI30bKS3n086vYsOq75AuJYdgpMV224r4jJ0SL8YNMCuy/132',
                'name' => '小都',
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
            array (
                'openid' => 'o3oiw54YEcPFijWUGGiWJFWHy45M',
                'sex' => 0,
                'avatar' => 'http://thirdwx.qlogo.cn/mmopen/Srqg995icfNlYibUY7jbicQvHS57oFKNUDK328yJq8kTrouj9nY30ek2fBvqjziaswIu24DReFdTcWQ7Rdw2hMibnExicmfnwGubWQ/132',
                'name' => '林姿欣',
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