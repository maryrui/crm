<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/7/11
 * Time: 14:42
 */

namespace app\crm\controller;

use app\admin\controller\ApiCommon;
use think\Db;
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
            'allow'=>['index','read','update','check']
        ];
//        Hook::listen('check_auth',$action);
        $request = Request::instance();
        $a = strtolower($request->action());
        if (in_array($a, $action['permission'])) {
            parent::_initialize();
        }

    }

    /**
     * 客诉列表
     * @author Chen
     * @return
     */
    public function index()
    {
        $complaintModel = model("complaint");
        $header = Request::instance()->header();
        $param = Request::instance()->request();
        $authKey = $header['authkey'];
        $cache = cache('Auth_'.$authKey);
        $userInfo = $cache['userInfo'];
//        $param['user_ids'] = ['like','%,'.$userInfo['id'].',%'];
//        $param['structure_ids'] = ['like','%,'.$userInfo['structure_id'].',%'];

        $param['user_id'] = $userInfo['id'];
        unset($param['status']);
        $data = $complaintModel->getDataList($param);

        return resultArray(['data' => $data]);
    }

    public function save()
    {
        $complaintModel = model("complaint");
        $header = Request::instance()->header();
        $authKey = $header['authkey'];
        $cache = cache('Auth_'.$authKey);
        $userInfo = $cache['userInfo'];
        $examineStepModel = new \app\admin\model\ExamineStep();
        $param = $this->request->param();
        //审核判断（是否有符合条件的审批流）
        $examineFlowModel = new \app\admin\model\ExamineFlow();
        if (!$examineFlowModel->checkExamine('', 'crm_complaint')) {
            return resultArray(['error' => '暂无流程处理人，无法创建']);
        }
        //添加审批相关信息
        $examineFlowData = $examineFlowModel->getFlowByTypes('', 'crm_complaint');
        if (!$examineFlowData) {
            return resultArray(['error' => '无可用流程流，请联系管理员']);
        }
        $param['flow_id'] = $examineFlowData['flow_id'];
        //获取审批人信息
        if ($examineFlowData['config'] == 1) {
            //固定审批流
            $nextStepData = $examineStepModel->nextStepUser(0, $examineFlowData['flow_id'], 'crm_complaint', 0, 0, 0);
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
        $param['create_user_id'] = $userInfo['id'];
        // 创建新的流程
        $flowModel = new \app\admin\model\ExamineFlow();
        $flowData = $flowModel->where(['flow_id'=>$examineFlowData['flow_id']])->find();
        $flowNew = array(
            'name'=>$flowData['name'],
            'config'=>$flowData['config'],
            'types'=>$flowData['types'],
            'types_id'=>$flowData['types_id'],
            'structure_ids'=>$flowData['structure_ids'],
            'user_ids'=>$flowData['user_ids'],
            'remark'=>$flowData['remark'],
            'update_user_id'=>-1,
            'status'=>1,
            'is_deleted'=>0,
        );
        $flowModel->save($flowNew);
        $newFlowId = $flowModel->flow_id;
        // 将上一个流程设置为失效
        $flowId = $flowData['flow_id'];
        $flowModel->where('flow_id',$flowId)->update(['status'=>0,'is_deleted'=>1,'delete_time'=>time(),'delete_user_id'=>-1]);
        // 新建流程各个节点
        $examineStepModel = new \app\admin\model\ExamineStep();
        $steps = $examineStepModel->where(['flow_id'=>$flowId])->field(['status','user_id','order_id','relation'])->select();
        $stepsNew = [];
        foreach($steps as $k=>$v){
            $item = array();
            $item['flow_id'] = $newFlowId;
            $item['create_time'] = time();
            $item['order_id'] = $v['order_id'];
            $item['status'] = $v['status'];
            if($k ==0){
                $item['user_id'] =$v['user_id'];
            }
            $stepsNew[]=$item;
        }
        $examineStepModel->saveAll($stepsNew,true);
        $param['flow_id'] = $newFlowId;
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
        $param = Request::instance()->request();
        $dataInfo = $complaintModel->where(['id' => $param['id']])->find();
        if ($dataInfo['check_status']!=0) {
            return resultArray(['error' => '当前状态为审批中或已审批通过，不可编辑']);
        }

        $data = $complaintModel->updateDataById($param);
        if (!$data) {
            return resultArray(['error' => $complaintModel->getError()]);
        }
        return resultArray(['data' => $data]);
    }

    public function check(){
        $param = $this->request->param();
        $header = Request::instance()->header();
        $authKey = $header['authkey'];
        $cache = cache('Auth_'.$authKey);
        $userInfo = $cache['userInfo'];
        $user_id = $userInfo['id'];
        $complaintModel = model('Complaint');
        $examineStepModel = new \app\admin\model\ExamineStep();
        $examineRecordModel = new \app\admin\model\ExamineRecord();
        $examineFlowModel = new \app\admin\model\ExamineFlow();

        $complaintData = [];
        $complaintData['update_time'] = time();
        $complaintData['check_status'] = 1; //0待审核，1审核通中，2审核通过，3审核未通过
        //权限判断
        if (!$examineStepModel->checkExamine($user_id, 'crm_complaint', $param['id'])) {
            return resultArray(['error' => $examineStepModel->getError()]);
        };

        $dataInfo = $complaintModel->getDataById($param['id']);
        $flowInfo = $examineFlowModel->getDataById($dataInfo['flow_id']);
        $is_end = 0; // 1审批结束
        $status = $param['status'] ? 1 : 0; //1通过，0驳回

        if($param['order_id'] == 1){
            $examineStepModel = new \app\admin\model\ExamineStep();
            $examineStepModel->where(['flow_id'=>$param['flow_id'],'order_id'=>2])->update(['user_id'=>arrayToString($param['departmentVal'])]);
            $examineStepModel->where(['flow_id'=>$param['flow_id'],'order_id'=>3])->update(['user_id'=>arrayToString($param['visitorVal'])]);
            $complaintModel->where(['id'=>$param['id']])->update(['type'=>$param['type']]);
        }else if($param['order_id'] == 3){
            $complaintModel->where(['id'=>$param['id']])->update(['score'=>$param['rate']]);
        }
        $checkData = array(
            'check_user_id'=>$user_id,'types'=> 'crm_complaint','types_id' => $param['id'],
            'check_time' => time(),'content' => $param['content'],'flow_id' => $dataInfo['flow_id'],
            'order_id' => $dataInfo['order_id'] ? : 1,'status' => $status
            );

        if ($status == 1) {
            if ($flowInfo['config'] == 1) {
                //固定流程
                //获取下一审批信息
                $nextStepData = $examineStepModel->nextStepUser(0, $dataInfo['flow_id'], 'crm_complaint', $param['id'], $dataInfo['order_id'], $user_id);
                $next_user_ids = $nextStepData['next_user_ids'] ? : [];
                //
                $complaintData['order_id'] = $nextStepData['order_id'] ? : '';
                if (!$next_user_ids) {
                    $is_end = 1;
                    //审批结束
                    $checkData['check_status'] = !empty($status) ? 2 : 3;
                    $complaintData['check_user_id'] = '';
                } else {
                    //修改主体相关审批信息
                    $complaintData['check_user_id'] = arrayToString($next_user_ids);
                }
            } else {
                //自选流程
                $is_end = $param['is_end'] ? 1 : '';
                $check_user_id = $param['check_user_id'] ? : '';
                if ($is_end !== 1 && empty($check_user_id)) {
                    return resultArray(['error' => '请选择下一审批人']);
                }
                $complaintData['check_user_id'] = arrayToString($param['check_user_id']);
            }
            if ($is_end == 1) {
                $checkData['check_status'] = !empty($status) ? 2 : 3;
                $complaintData['check_user_id'] = '';
                $complaintData['check_status'] = 2;
            }
        }else{
            //审批驳回
            $is_end = 1;
            $complaintData['check_status'] = 3;
        }
        //已审批人ID
        $resUpdate = db('crm_complaint')->where(['id' => $param['id']])->update($complaintData);
        if ($resUpdate) {
            //审批记录
            $resRecord = $examineRecordModel->createData($checkData);
            return resultArray(['data' => '审批成功']);
        } else {
            return resultArray(['error' => '审批失败，请重试！']);
        }
    }
}