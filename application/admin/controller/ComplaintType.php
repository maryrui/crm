<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/8/28
 * Time: 14:19
 */

namespace app\admin\controller;


class ComplaintType extends ApiCommon
{
    public function index(){
        $model = model('ComplaintType');
        $list = $model->getDataList();
        return resultArray(['data' => $list]);
    }

    public function save(){
        $param = $this->param;
        $model = model('ComplaintType');
        $res = $model->createData($param);
        if (!$res) {
            return resultArray(['error' =>"数据提交失败"]);
        }
        return resultArray(['data' => "数据提交成功"]);
    }
}