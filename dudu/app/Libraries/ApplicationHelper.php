<?php

namespace App\Libraries;

use EasyWeChat;

class ApplicationHelper
{
    /**
     * 获取微信用户信息
     *
     * @return array
     */
    public static function getWxUserInfo($account, $openid)
    {
        // $user = session('wechat.oauth_user'); // 拿到授权用户资料
        // $openid = $user[$account]['token']['openid'];  // 用户openid
        $officialAccount = EasyWeChat::officialAccount($account);
        $accessToken = $officialAccount->access_token->getToken();  // 基础sccess_token
        $token = $accessToken['access_token'];

        // $url = 'https://api.weixin.qq.com/cgi-bin/user/info?access_token=' . $token . '&openid=oQFyi0vKaddxw5ObUiivHPEISDyI&lang=zh_CN';
        $url = 'https://api.weixin.qq.com/cgi-bin/user/info?access_token=' . $token . '&openid=' . $openid  . '&lang=zh_CN';
        $content = file_get_contents($url);


        return json_decode($content, true);
    }

    /**
     * 发送外部请求
     *
     * @param  string  $url
     * @param  array  $data
     * @param  string  $method
     * @return array
     */
    public static function curlPost($url, $data, $method)
    {
        // 1.初始化
        $ch = curl_init();

        // 2.请求地址
        curl_setopt($ch, CURLOPT_URL, $url);

        // 3.请求方式
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

        // 4.参数如下
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);// https
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');// 模拟浏览器
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept-Encoding: gzip, deflate'));// gzip解压内容
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip,deflate');

        // 5.post方式的时候添加数据
        if ($method == 'POST') {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // 6.执行
        $tmpInfo = curl_exec($ch);

        // 7.判断是否出错
        if (curl_errno($ch)) {
            return curl_error($ch);
        }

        // 8.关闭
        curl_close($ch);
        return $tmpInfo;
    }
}
