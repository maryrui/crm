<?php

namespace app\admin\controller;

use app\admin\model\PhwPushData;

class Phw
{
    public function phwTest()
    {
        $request = request();
        $key = $request->param('key');
        if ($key != '69efa898b0650593f56b16722d5e23d0') {
            return 'illegal';

        }
        $phw = new PhwPushData;
        $phw->handlePhwData();
    }
}