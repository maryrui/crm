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
        $param = Request::instance()->param();

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