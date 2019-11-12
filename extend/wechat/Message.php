<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/7/10
 * Time: 15:54
 */
namespace wechat;

use tools\Curl;
class Message extends Base {
    public function template($openid,$data)
    {
        $token = $this->getToken();
        $url = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token={$token}";
        $data = array(
            "touser"=>$openid,
            "template_id"=>"CjU-ltDA8tLoh8M0mUFOroRQQ2MBagNLNVQgqEJjQac",
            "data"=>$data,
        );
        Curl::post($url,json_encode($data));
    }
}