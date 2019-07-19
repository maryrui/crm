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

    protected $appId = "wx3b61175501c898f8";
    protected $secret = "dd7a31a79cb27b8b44de85ce9e9510b3";
    protected $grantType = "client_credential";
    protected $token = "1db277129a0bd139257b23e482c3df75";

    public function getToken()
    {
        $data = Cache::get('wechat_access_token');
        if(!$data){
           return self::requestToken();
        }
        return $data;
    }

    static public function getWebToken($code)
    {
        $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=APPID&secret=SECRET&code={$code}&grant_type=authorization_code";
        $resp = Curl::get($url);
        return $resp;
    }

    protected function requestToken()
    {
        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=".$this->grantType."&appid=".$this->appId.
            "&secret=".$this->secret;
        $resp = Curl::get($url);
        $data = json_decode($resp,true);

        if(!isset($data['errcode'])){
            Cache::set("wechat_access_token",$data['access_token'],$data['expires_in']);
            return $data['access_token'];
        }
        return $data;
    }


}