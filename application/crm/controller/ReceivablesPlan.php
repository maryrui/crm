<?php
// +----------------------------------------------------------------------
// | Description: 回款计划
// +----------------------------------------------------------------------
// | Author: Michael_xu | gengxiaoxu@5kcrm.com 
// +----------------------------------------------------------------------

namespace app\crm\controller;

use app\admin\controller\ApiCommon;
use think\Hook;
use think\Request;

class ReceivablesPlan extends ApiCommon
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
            'allow'=>['index','save','read','update','delete']            
        ];
        Hook::listen('check_auth',$action);
        $request = Request::instance();
        $a = strtolower($request->action());        
        if (!in_array($a, $action['permission'])) {
            parent::_initialize();
        }
    } 

    /**
     * 回款计划列表
     * @author Michael_xu
     * @return 
     */
    public function index()
    {
        $receivablesPlanModel = model('ReceivablesPlan');
        $param = $this->param;
        $userInfo = $this->userInfo;
        $param['user_id'] = $userInfo['id'];
        $data = $receivablesPlanModel->getDataList($param);       
        return resultArray(['data' => $data]);
    }

    /**
     * 添加回款计划
     * @author Michael_xu
     * @param 
     * @return 
     */
    public function save()
    {
        $receivablesPlanModel = model('ReceivablesPlan');
        $examineStepModel = new \app\admin\model\ExamineStep();
        $param = $this->param;
        $userInfo = $this->userInfo;
        $param['create_user_id'] = $userInfo['id'];
        $param['owner_user_id'] = $userInfo['id'];
        $examineFlowModel = new \app\admin\model\ExamineFlow();
        if (!$examineFlowModel->checkExamine($param['create_user_id'], 'crm_receivables_plan')) {
            return resultArray(['error' => '暂无审批人，无法创建']);
        }
        //添加审批相关信息
        $examineFlowData = $examineFlowModel->getFlowByTypes($param['create_user_id'], 'crm_receivables_plan');
        if (!$examineFlowData) {
            return resultArray(['error' => '无可用审批流，请联系管理员']);
        }
        $param['flow_id'] = $examineFlowData['flow_id'];
        //获取审批人信息
        if ($examineFlowData['config'] == 1) {
            //固定审批流
            $nextStepData = $examineStepModel->nextStepUser($userInfo['id'], $examineFlowData['flow_id'], 'crm_receivables_plan', 0, 0, 0);
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


        $res = $receivablesPlanModel->createData($param);
        if ($res) {
            return resultArray(['data' => '添加成功']);
        } else {
            return resultArray(['error' => $receivablesPlanModel->getError()]);
        }
    }

    /**
     * 回款计划详情
     * @author Michael_xu
     * @param  
     * @return 
     */
    public function read()
    {
        $receivablesPlanModel = model('ReceivablesPlan');
        $param = $this->param;
        $data = $receivablesPlanModel->getDataById($param['id']);
        if (!$data) {
            return resultArray(['error' => $receivablesPlanModel->getError()]);
        }
        return resultArray(['data' => $data]);
    }

    /**
     * 编辑回款计划
     * @author Michael_xu
     * @param 
     * @return 
     */
    public function update()
    {    
        $receivablesPlanModel = model('ReceivablesPlan');
        $userModel = new \app\admin\model\User();
        $param = $this->param;
        $userInfo = $this->userInfo;
        $plan_id = $param['id'];

        $dataInfo = db('crm_receivables_plan')->where(['plan_id' => $plan_id])->find();
        //根据合同权限判断
        $contractData = db('crm_contract')->where(['contract_id' => $dataInfo['contract_id']])->find();
        $auth_user_ids = $userModel->getUserByPer('crm', 'contract', 'update');
        //读写权限
        $rwPre = $userModel->rwPre($userInfo['id'], $contractData['ro_user_id'], $contractData['rw_user_id'], 'update');       
        if (!in_array($contractData['owner_user_id'],$auth_user_ids) && !$rwPre) {
            header('Content-Type:application/json; charset=utf-8');
            exit(json_encode(['code'=>102,'error'=>'无权操作']));
        }

        //已进行审批，不能编辑
        if ($dataInfo['check_status']!=0) {
            return resultArray(['error' => '当前状态为审批中或已审批通过，不可编辑']);
        }
        //将审批状态至为待审核，提交后重新进行审批
        //审核判断（是否有符合条件的审批流）
        $examineFlowModel = new \app\admin\model\ExamineFlow();
        $examineStepModel = new \app\admin\model\ExamineStep();
        if (!$examineFlowModel->checkExamine($dataInfo['owner_user_id'], 'crm_receivables_plan')) {
            return resultArray(['error' => '暂无审批人，无法创建']);
        }
        //添加审批相关信息
        $examineFlowData = $examineFlowModel->getFlowByTypes($dataInfo['owner_user_id'], 'crm_receivables_plan');
        if (!$examineFlowData) {
            return resultArray(['error' => '无可用审批流，请联系管理员']);
        }
        $param['flow_id'] = $examineFlowData['flow_id'];
        //获取审批人信息
        if ($examineFlowData['config'] == 1) {
            //固定审批流
            $nextStepData = $examineStepModel->nextStepUser($dataInfo['owner_user_id'], $examineFlowData['flow_id'], 'crm_receivables_plan', 0, 0, 0);
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
        $param['check_status'] = 0;
        $param['flow_user_id'] = '';

        $res = $receivablesPlanModel->updateDataById($param, $param['id']);
        if ($res) {
            return resultArray(['data' => '编辑成功']);
        } else {
            return resultArray(['error' => $receivablesPlanModel->getError()]);
        }       
    } 

    /**
     * 删除回款计划
     * @author Michael_xu
     * @param 
     * @return 
     */
    public function delete()
    {
        $userModel = new \app\admin\model\User();
        $param = $this->param;
        $userInfo = $this->userInfo;
        $plan_id = $param['id'];
        if ($plan_id) {
            $dataInfo = db('crm_receivables_plan')->where(['plan_id' => $plan_id])->find();
            if (!$dataInfo) {
                return resultArray(['error' => '数据不存在或已删除']);
            }
            $receivablesInfo = db('crm_receivables')->where(['receivables_id' => $dataInfo['receivables_id']])->find();
            if ($receivablesInfo) {
                return resultArray(['error' => '已关联回款《'.$receivablesInfo['number'].'》，不能删除']);
            }
            //根据合同权限判断
            $contractData = db('crm_contract')->where(['contract_id' => $dataInfo['contract_id']])->find();
            $auth_user_ids = $userModel->getUserByPer('crm', 'contract', 'delete');
            //读写权限
            $rwPre = $userModel->rwPre($userInfo['id'], $contractData['ro_user_id'], $contractData['rw_user_id'], 'update');       
            if (!in_array($contractData['owner_user_id'],$auth_user_ids) && !$rwPre) {
                header('Content-Type:application/json; charset=utf-8');
                exit(json_encode(['code'=>102,'error'=>'无权操作']));
            }
            $res = model('ReceivablesPlan')->delDataById($plan_id);
            if (!$res) {
                return resultArray(['error' => model('ReceivablesPlan')->getError()]);
            }
            return resultArray(['data' => '删除成功']);
        } else {
            return resultArray(['error'=>'参数错误']);
        }        
    }     
}
