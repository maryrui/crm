<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/7/10
 * Time: 11:34
 */
return [
    '__rest__' => [],
    'extend/crontab/index' => ['extend/crontab/index', ['method' => 'GET']],
//    'extend/crontab/leads' =>['extend/crontab/leads', ['method' => 'GET']],
//    'extend/crontab/receivablesPlan' =>['extend/crontab/receivablesPlan', ['method' => 'GET']],
//    'extend/crontab/customer' =>['extend/crontab/customer', ['method' => 'GET']],
//    'extend/crontab/contract' =>['extend/crontab/contract', ['method' => 'GET']],
//    'extend/crontab/token' =>['extend/crontab/token', ['method' => 'GET']],

    // 【客诉】
    'extend/wechat/complaint/save' => ['extend/wechat/complaintSave', ['method' => 'POST']],
    // 【绑定微信】
    'extend/wechat/bind' => ['extend/wechat/bindWecaht', ['method' => 'POST']],
    'extend/wechat/web/token' => ['extend/wechat/webAccessToken', ['method' => 'POST']],

    // MISS路由
    '__miss__' => 'admin/base/miss',
];