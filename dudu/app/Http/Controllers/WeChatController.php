<?php

namespace App\Http\Controllers;

use Log;
use Cache;
use App\Models\User;
use Illuminate\Http\Request;
use App\Libraries\ApplicationHelper;
use App\Http\Controllers\ApprovalController;

class WeChatController extends Controller
{
    /**
     * 处理微信的请求消息
     *
     * @return string
     */
    public function serve($account)
    {
        $app_conf = 'wechat.official_account.dudu';
        $app = app($app_conf);
        $app->server->push(function ($message) use ($account) {
            $result = '';
            $openid = $message['FromUserName'];
            if ($message['EventKey']) {
                $event_key = explode("_", $message['EventKey'])[1];
                if (strpos($event_key,'@')) {
                    $ids = [];
                    $key = explode("@", $event_key);
                    $ids['org_id'] = $key[0];
                    $ids['dept_id'] = $key[1];
                    $ids['role'] = $key[2];
                    $ids['openid'] = $openid;
                    $approval = new ApprovalController;
                    $approval->grantJoinOrgByInviteQrcode($ids);
                }
            }
            switch ($message['MsgType']) {
                case 'event':
                    switch ($message['Event']) {
                        case 'subscribe':
                            $user_info = ApplicationHelper::getWxUserInfo($account, $openid);
                            $user = User::where('openid',$openid)->first();
                            $time = date('Y-m-d H:i:s');
                            if(!$user){
                                 $user_info_values = $user_info == [] ? [] : [
                                     'name' => $user_info['nickname'],
                                     'avatar' => $user_info['headimgurl'],
                                     'sex' => $user_info['sex'],
                                     'follow_time' => $time,
                                     'first_follow_time' => $time,
                                     'is_followed' => 1,
                                 ];
                                 User::updateOrCreate([
                                     'openid' => $openid
                                 ], $user_info_values);
                            }else{
                                if (!($user->name || $user->sex || $user_info == [])) {
                                    // 跟新用户微信信息（姓名，头像，性别）
                                    $user->name = $user_info['nickname'];
                                    $user->avatar = $user_info['headimgurl'];
                                    $user->sex = $user_info['sex'];
                                }
                                // 更新关注时间
                                $user->follow_time = $time;
                                if (!$user->unfollow_time) {
                                    $user->first_follow_time = $user->follow_time;
                                }
                                $user->is_followed = 1;
                                $user->avatar = $user_info['headimgurl'];
                                $user->save();
                            }
                            $result = "欢迎使用“都督微办公”，您可以叫我“嘟嘟”\n" .
                                "如果您还不了解“嘟嘟”，请查阅【关于都督】\n" .
                                "如果您想熟悉有关操作使用，请查阅【使用帮助】\n" .
                                "点击【都督办公】体验“嘟嘟”魅力！";
                            break;

                        case 'unsubscribe':
                            $user = user::where('openid',$openid)->first();
                            $user->unfollow_time = date('Y-m-d H:i:s');
                            $user->is_followed = 0;
                            $user->save();
                            
                            $result = '期待你再次回来';
                            break;
                        default:
                            $result = '你好~';
                            break;
                    }
                    break;
                case 'text':
                    $result = '您好~进入系统请点击【下方菜单栏】';
                    break;
                default:
                    $result = '已收到您的消息，更多信息请点击下方菜单栏';
                    break;
            }
            return $result;
        });
        return $app->server->serve();
    }

    // 保存微信图片并返回图片路径
    public function getImageUrl(Request $request)
    {
        $appConf = 'wechat.official_account.dudu';
        $app = app($appConf);
        $mediaId = $request->input('mediaId');
        $stream = $app->media->get($mediaId);

        $file_path_temp = 'avatar/' . date('Y') . '/' . date('m');
        $file_path = config('filesystems.disks.images.pic_url') . $file_path_temp;
        $imageName = $stream->save($file_path);

        // 获取当前域名
        $scheme = empty($_SERVER['HTTPS']) ? 'http://' : 'https://';
        $url = $scheme . $_SERVER['HTTP_HOST'];
        $imageUrl = $url . '/' . $file_path_temp . '/' . $imageName;

        return ResponseJson($imageUrl);
    }

    public function jsSdkConfig(Request $request)
    {
        $appConf = 'wechat.official_account.dudu';
        $app = app($appConf);
        $url = $request->page_uri;
        $app->jssdk->setUrl($url);
        $config_data = $app->jssdk->buildConfig([
            'onMenuShareTimeline',
            'onMenuShareAppMessage',
        ],env('APP_ENV')==="local"?true:false);
        return $config_data;
    }
}
