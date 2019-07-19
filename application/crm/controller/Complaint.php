<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/7/11
 * Time: 14:42
 */

namespace app\crm\controller;

use app\admin\controller\ApiCommon;
use think\Hook;
use think\Request;

class Complaint extends ApiCommon
{
    /**
     * 用于判断权限
     * @permission 无限制
     * @allow 登录用户可访问
     * @other 其他根据系统设置
     **/
    public function _initialize()
    {
        $action = [
            'permission'=>[''],
            'allow'=>['index','read','update']
        ];
        Hook::listen('check_auth',$action);
        $request = Request::instance();
        $a = strtolower($request->action());
        if (!in_array($a, $action['permission'])) {
            parent::_initialize();
        }
    }

    /**
     * 客诉列表
     * @author Chen
     * @return
     */
    function index()
    {
        $complaintModel = model("complaint");
        $param = $this->param;
        $data = $complaintModel->getDataList($param);

        return resultArray(['data' => $data]);
    }

    public function save()
    {
        $complaintModel = model("complaint");
        $param = $this->param;

        if ($complaintModel->createData($param)) {
            return resultArray(['data' => '提交成功']);
        } else {
            return resultArray(['error' => $complaintModel->getError()]);
        }
    }

    public function read()
    {
        $complaintModel = model("complaint");
        $param = $this->param;
        $data = $complaintModel->getDataById($param['id']);

        if (!$data) {
            return resultArray(['error' => $complaintModel->getError()]);
        }
        return resultArray(['data' => $data]);
    }

    public function update()
    {
        $complaintModel = model("complaint");
        $param = $this->param;
        $data = $complaintModel->updateDataById($param);
        if (!$data) {
            return resultArray(['error' => $complaintModel->getError()]);
        }
        return resultArray(['data' => $data]);
    }
}