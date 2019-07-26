<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/7/10
 * Time: 16:23
 */

namespace wechat;

use think\Cache;
use tools\Curl;

class Base
{
    //开发者ID	wx3b61175501c898f8
    //Appsecret	dd7a31a79cb27b8b44de85ce9e9510b3
    //令牌（Token）	1db277129a0bd139257b23e482c3df75
    //消息加密密钥	8PJ3CJEg4NjwQbjXYdyWM1SyC13kvnK5oxRZnFA08Fo

    protected  $appId = "wx3b61175501c898f8";
    protected  $secret = "dd7a31a79cb27b8b44de85ce9e9510b3";
    protected  $grantType = "client_credential";
    protected  $token = "1db277129a0bd139257b23e482c3df75";

    public function getToken()
    {
        $data = Cache::get('wechat_access_token');
        if(!$data){
           return self::requestToken();
        }
        return $data;
    }

    protected function requestToken()
    {
        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=wx3b61175501c898f8&secret=dd7a31a79cb27b8b44de85ce9e9510b3";

        $resp = Curl::get($url);
        $data = json_decode($resp,true);
        print_r($data);
        if(!isset($data['errcode'])){
            Cache::set("wechat_access_token",$data['access_token'],$data['expires_in']);
            return $data['access_token'];
        }
        return false;
    }


}