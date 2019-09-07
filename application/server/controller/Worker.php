<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/7/11
 * Time: 8:54
 */

namespace app\server\controller;
use think\worker\Server;

class Worker extends Server
{

    public function onWorkerStart($work)
    {
        $handle=new Collection();
        $handle->addTimer();
    }
}