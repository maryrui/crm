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
            'allow'=>['index','save','read','update','delete','check','revokeCheck']
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
        $contractModel = model('Contract');
        $contract = $contractModel->getDataById($param['contract_id']);
        $check_status = $contract['check_status'];
        if($check_status!=2){
            return resultArray(['error' => '订单流程审批未结束或未通过！']);
        }
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
        $todayTime = getTimeByType('today');
        $count = $receivablesPlanModel->where(['create_time'=>['between',[$todayTime[0],$todayTime[1]]]])->count();
        $num = substr(strval($count+10001),1,4);
        $param['invoice_code']='FP'.date("Ymd").$num;
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
        $receivablesPlanModel = model('ReceivablesPlan');
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
            $res =$receivablesPlanModel->delDataById($plan_id);
            if (!$res) {
                return resultArray(['error' => model('ReceivablesPlan')->getError()]);
            }
            return resultArray(['data' => '删除成功']);
        } else {
            return resultArray(['error'=>'参数错误']);
        }
    }


    /**
     * 回款计划审核
     * @author Michael_xu
     * @param
     * @return
     */
    public function check()
    {
        $param = $this->param;
        $userInfo = $this->userInfo;
        $user_id = $userInfo['id'];
        $planModel = model('ReceivablesPlan');
        $examineStepModel = new \app\admin\model\ExamineStep();
        $examineRecordModel = new \app\admin\model\ExamineRecord();
        $examineFlowModel = new \app\admin\model\ExamineFlow();

        $contractData = [];
        $contractData['update_time'] = time();
        $contractData['check_status'] = 1; //0待审核，1审核通中，2审核通过，3审核未通过
        //权限判断
        if (!$examineStepModel->checkExamine($user_id, 'crm_receivables_plan', $param['id'])) {
            return resultArray(['error' => $examineStepModel->getError()]);
        };
        //审批主体详情
        $dataInfo = $planModel->getDataById($param['id']);
        $flowInfo = $examineFlowModel->getDataById($dataInfo['flow_id']);
        $is_end = 0; // 1审批结束

        $status = $param['status'] ? 1 : 0; //1通过，0驳回
        $checkData = [];
        $checkData['check_user_id'] = $user_id;
        $checkData['types'] = 'crm_receivables_plan';
        $checkData['types_id'] = $param['id'];
        $checkData['check_time'] = time();
        $checkData['content'] = $param['content'];
        $checkData['flow_id'] = $dataInfo['flow_id'];
        $checkData['order_id'] = $dataInfo['order_id'] ? : 1;
        $checkData['status'] = $status;

        if ($status == 1) {
            if ($flowInfo['config'] == 1) {
                //固定流程
                //获取下一审批信息
                $nextStepData = $examineStepModel->nextStepUser($dataInfo['owner_user_id'], $dataInfo['flow_id'], 'crm_receivables_plan', $param['id'], $dataInfo['order_id'], $user_id);
                $next_user_ids = $nextStepData['next_user_ids'] ? : [];
                $contractData['order_id'] = $nextStepData['order_id'] ? : '';
                if (!$next_user_ids) {
                    $is_end = 1;
                    //审批结束
                    $checkData['check_status'] = !empty($status) ? 2 : 3;
                    $contractData['check_user_id'] = '';
                } else {
                    //修改主体相关审批信息
                    $contractData['check_user_id'] = arrayToString($next_user_ids);
                }
            } else {
                //自选流程
                $is_end = $param['is_end'] ? 1 : '';
                $check_user_id = $param['check_user_id'] ? : '';
                if ($is_end !== 1 && empty($check_user_id)) {
                    return resultArray(['error' => '请选择下一审批人']);
                }
                $contractData['check_user_id'] = arrayToString($param['check_user_id']);
            }
            if ($is_end == 1) {
                $checkData['check_status'] = !empty($status) ? 2 : 3;
                $contractData['check_user_id'] = '';
                $contractData['check_status'] = 2;
            }
        } else {
            //审批驳回
            $is_end = 1;
            $contractData['check_status'] = 3;
        }
        //已审批人ID
        $resContract['flow_user_id'] = stringToArray($dataInfo['flow_user_id']) ? arrayToString(array_merge(stringToArray($dataInfo['flow_user_id']),[$user_id])) : arrayToString([$user_id]);

        if(isset($param['real_invoice'])){
            $contractData['real_code'] = $param['real_invoice'];
        }
        $resContract = db('crm_receivables_plan')->where(['plan_id' => $param['id']])->update($contractData);
        if ($resContract) {
            //审批记录
            $resRecord = $examineRecordModel->createData($checkData);
            //审核通过，相关客户状态改为已成交
            if ($is_end == 1 && !empty($status)) {
                //发送站内信
                $sendContent = '您的发票申请【'.$dataInfo['invoice_code'].'】,'.$userInfo['realname'].'已审核通过,审批结束';
                $resMessage = sendMessage($dataInfo['owner_user_id'], $sendContent, $param['id'], 1);

                $customerData = [];
                $customerData['deal_status'] = '已成交';
                $customerData['deal_time'] = time();
                db('crm_customer')->where(['customer_id' => $dataInfo['customer_id']])->update($customerData);
            } else {
                if ($status) {
                    //发送站内信
                    $sendContent = '您的发票申请【'.$dataInfo['invoice_code'].'】,'.$userInfo['realname'].'已审核通过';
                    $resMessage = sendMessage($dataInfo['owner_user_id'], $sendContent, $param['id'], 1);
                } else {
                    $sendContent = '您的发票申请【'.$dataInfo['invoice_code'].'】,'.$userInfo['realname'].'已审核拒绝,审核意见：'.$param['content'];
                    $resMessage = sendMessage($dataInfo['owner_user_id'], $sendContent, $param['id'], 1);
                }
            }
            return resultArray(['data' => '审批成功']);
        } else {
            return resultArray(['error' => '审批失败，请重试！']);
        }
    }

    /**
     * 合同撤销审核
     * @author Michael_xu
     * @param
     * @return
     */
    public function revokeCheck()
    {
        $param = $this->param;
        $userInfo = $this->userInfo;
        $user_id = $userInfo['id'];
        $contractModel = model('Contract');
        $examineRecordModel = new \app\admin\model\ExamineRecord();
        $userModel = new \app\admin\model\User();

        $contractData = [];
        $contractData['update_time'] = time();
        $contractData['check_status'] = 0; //0待审核，1审核通中，2审核通过，3审核未通过
        //审批主体详情
        $dataInfo = $contractModel->getDataById($param['id']);
        //权限判断(负责人或管理员)
        if ($dataInfo['check_status'] == 2) {
            return resultArray(['error' => '已审批结束,不能撤销']);
        }
        if ($dataInfo['check_status'] == 4) {
            return resultArray(['error' => '无需撤销']);
        }
        $admin_user_ids = $userModel->getAdminId();
        if ($dataInfo['owner_user_id'] !== $user_id && !in_array($user_id, $admin_user_ids)) {
            return resultArray(['error' => '没有权限']);
        }

        $status = 2; //1通过，0驳回, 2撤销
        $checkData = [];
        $checkData['check_user_id'] = $user_id;
        $checkData['types'] = 'crm_receivables_plan';
        $checkData['types_id'] = $param['id'];
        $checkData['check_time'] = time();
        $checkData['content'] = $param['content'];
        $checkData['flow_id'] = $dataInfo['flow_id'];
        $checkData['order_id'] = $dataInfo['order_id'];
        $checkData['status'] = $status;

        $contractData['check_status'] = 4;
        $contractData['check_user_id'] = '';
        $examineData['flow_user_id'] = '';
        $resContract = db('crm_receivables_plan')->where(['contract_id' => $param['id']])->update($contractData);
        if ($resContract) {
            //审批记录
            $resRecord = $examineRecordModel->createData($checkData);
            return resultArray(['data' => '撤销成功']);
        } else {
            return resultArray(['error' => '撤销失败，请重试！']);
        }
    }
}
