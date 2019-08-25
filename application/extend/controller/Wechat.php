<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/7/17
 * Time: 12:05
 */

namespace app\extend\controller;

use think\Controller;
use \app\crm\model\Complaint;
use \app\admin\model\User;
use think\Request;
use wechat\Web;
class Wechat extends Controller
{
    /**
     * auth Chen
     * 客诉 save
     * @param
     * @return
     */
    public function complaintSave()
    {
        $complaintModel = new Complaint();
        $examineStepModel = new \app\admin\model\ExamineStep();
        $param = Request::instance()->param();
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


        if ($complaintModel->createData($param)) {
            return resultArray(['data' => '提交成功']);
        } else {
            return resultArray(['error' => $complaintModel->getError()]);
        }
    }

    public function complaintTypes()
    {
        $model = new \app\crm\model\Complaint();
        $list = $model->getComplaintTypeList();
        return resultArray(['data' => $list]);
    }

    /**
     * auth Chen
     * 绑定用户微信的openid
     * @param mobile
     * @param openid
     * @return
     */
    public function bindWecaht()
    {
        $param = Request::instance()->param();
        if ($param['mobile'] && $param['openid']) {
            $userModel = new User();
            $ret = $userModel->updateOpenid($param);
            if ($ret) {
                return resultArray(['data'=>true]);
            } else {
                return resultArray(['error'=>$userModel->getError()]);
            }
        } else {
            return resultArray(['error'=>'参数错误']);
        }
    }

    public function getUserInfo()
    {
        $param = Request::instance()->param();
        $code = $param['code'];
        $weChat = new Web();
        $data =$weChat->getUser($code);
        if($data){
            return resultArray(['data'=>$data]);
        }else{
            return resultArray(['error'=>$data['errmsg']]);
        }

    }
}