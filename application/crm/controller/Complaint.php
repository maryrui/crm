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
        $userInfo = $this->userInfo;
        $examineStepModel = new \app\admin\model\ExamineStep();
        $param = $this->param;
        //审核判断（是否有符合条件的审批流）
        $examineFlowModel = new \app\admin\model\ExamineFlow();
        if (!$examineFlowModel->checkExamine($param['create_user_id'], 'crm_complaint')) {
            return resultArray(['error' => '暂无审批人，无法创建']);
        }
        //添加审批相关信息
        $examineFlowData = $examineFlowModel->getFlowByTypes($param['create_user_id'], 'crm_contract');
        if (!$examineFlowData) {
            return resultArray(['error' => '无可用审批流，请联系管理员']);
        }
        $param['flow_id'] = $examineFlowData['flow_id'];
        //获取审批人信息
        if ($examineFlowData['config'] == 1) {
            //固定审批流
            $nextStepData = $examineStepModel->nextStepUser($userInfo['id'], $examineFlowData['flow_id'], 'crm_contract', 0, 0, 0);
            $next_user_ids = arrayToString($nextStepData['next_user_ids']) ? : '';
            $check_user_id = $next_user_ids ? : [];
            $param['order_id'] = 1;
        } else {
            $check_user_id = $param['check_user_id'] ? ','.$param['check_user_id'].',' : '';
        }
        if (!$check_user_id) {
            return resultArray(['error' => '无可用审批人，请联系管理员']);
        }
        $param['check_user_id'] = is_array($check_user_id) ? ','.implode(',',$check_user_id).',' : $check_user_id;

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