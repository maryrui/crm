<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/7/24
 * Time: 15:00
 */

namespace wechat;

use tools\Curl;
use think\Cache;
class Web extends Base
{
    public function getWebToken($code)
    {
        $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid={$this->appId}&secret={$this->secret}&code={$code}&grant_type=authorization_code";
        $resp = Curl::get($url);
        $data = json_decode($resp,true);
        if(!isset($data['errcode'])){
            Cache::set("wechat_ewb_access_token",$data['access_token'],$data['expires_in']);
            return $data;
        }
        return false;
    }

    public function getUser($code)
    {
        $data = $this->getWebToken($code);
        if($data){
            $token= $data['access_token'];
            $openid= $data['openid'];
            $url = "https://api.weixin.qq.com/sns/userinfo?access_token={$token}&openid={$openid}&lang=zh_CN";
            $resp = Curl::get($url);
            $data = json_decode($resp,true);
            if(!isset($data['errcode'])){
                return $data;
            }
        }

    }
}