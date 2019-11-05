<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/11/5
 * Time: 15:01
 */

namespace app\crm\model;

use app\admin\model\Common;

class ReceivablesPlanFile extends Common
{
    protected $name = 'crm_receivables_plan_file';

    public function createData($param){
        return $this->data($param)->allowField(true)->save();
    }
}