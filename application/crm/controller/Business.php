<?php
// +----------------------------------------------------------------------
// | Description: 商机
// +----------------------------------------------------------------------
// | Author: Michael_xu | gengxiaoxu@5kcrm.com 
// +----------------------------------------------------------------------

namespace app\crm\controller;

use app\admin\controller\ApiCommon;
use think\Hook;
use think\Request;
use think\Db;

class Business extends ApiCommon
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
            'permission' => [''],
            'allow' => ['statuslist', 'advance', 'product']
        ];
        Hook::listen('check_auth', $action);
        $request = Request::instance();
        $a = strtolower($request->action());
        if (!in_array($a, $action['permission'])) {
            parent::_initialize();
        }
    }

    /**
     * 商机列表
     * @return
     * @author Michael_xu
     */
    public function index()
    {
        $businessModel = model('Business');
        $param = $this->param;
        $userInfo = $this->userInfo;
        $param['user_id'] = $userInfo['id'];
        $data = $businessModel->getDataList($param);
        return resultArray(['data' => $data]);
    }

    /**
     * 添加商机
     * @param
     * @return
     * @author Michael_xu
     */
    public function save()
    {
        $businessModel = model('Business');
        $param = $this->param;
        $userInfo = $this->userInfo;
        $param['create_user_id'] = $userInfo['id'];
        $param['owner_user_id'] = $userInfo['id'];

        if ($businessModel->createData($param)) {
            return resultArray(['data' => '添加成功']);
        } else {
            return resultArray(['error' => $businessModel->getError()]);
        }
    }

    /**
     * 商机详情
     * @param
     * @return
     * @author Michael_xu
     */
    public function read()
    {
        $businessModel = model('Business');
        $businessStatusModel = model('BusinessStatus');
        $userModel = new \app\admin\model\User();
        $param = $this->param;
        $userInfo = $this->userInfo;
        $data = $businessModel->getDataById($param['id']);
        //判断权限
        $auth_user_ids = $userModel->getUserByPer('crm', 'business', 'read');
        //读权限
        $roPre = $userModel->rwPre($userInfo['id'], $data['ro_user_id'], $data['rw_user_id'], 'read');
        $rwPre = $userModel->rwPre($userInfo['id'], $data['ro_user_id'], $data['rw_user_id'], 'update');
        if (!in_array($data['owner_user_id'], $auth_user_ids) && !$rwPre && !$roPre) {
            header('Content-Type:application/json; charset=utf-8');
            exit(json_encode(['code' => 102, 'error' => '无权操作']));
        }
        //商机状态组
        $data['status_list'] = $businessStatusModel->getDataById($data['type_id']);
        if (!$data) {
            return resultArray(['error' => $businessModel->getError()]);
        }
        return resultArray(['data' => $data]);
    }

    /**
     * 编辑商机
     * @param
     * @return
     * @author Michael_xu
     */
    public function update()
    {
        $businessModel = model('Business');
        $userModel = new \app\admin\model\User();
        $param = $this->param;
        $userInfo = $this->userInfo;
        $param['user_id'] = $userInfo['id'];
        //判断权限
        $data = $businessModel->getDataById($param['id']);
        $auth_user_ids = $userModel->getUserByPer('crm', 'business', 'update');
        //读写权限
        $rwPre = $userModel->rwPre($userInfo['id'], $data['ro_user_id'], $data['rw_user_id'], 'update');
        if (!in_array($data['owner_user_id'], $auth_user_ids) && !$rwPre) {
            header('Content-Type:application/json; charset=utf-8');
            exit(json_encode(['code' => 102, 'error' => '无权操作']));
        }
        if ($businessModel->updateDataById($param, $param['id'])) {
            return resultArray(['data' => '编辑成功']);
        } else {
            return resultArray(['error' => $businessModel->getError()]);
        }
    }

    /**
     * 删除商机（逻辑删）
     * @param
     * @return
     * @author Michael_xu
     */
    public function delete()
    {
        $businessModel = model('Business');
        $param = $this->param;

        if (!is_array($param['id'])) {
            $business_id[] = $param['id'];
        } else {
            $business_id = $param['id'];
        }
        $delIds = [];
        $errorMessage = [];

        //数据权限判断
        $userModel = new \app\admin\model\User();
        $auth_user_ids = $userModel->getUserByPer('crm', 'business', 'delete');
        foreach ($business_id as $k => $v) {
            $isDel = true;
            //数据详情
            $data = $businessModel->getDataById($v);
            if (!$data) {
                $isDel = false;
                $errorMessage[] = 'id为' . $v . '的商机删除失败,错误原因：' . $businessModel->getError();
                continue;
            }
            if (!in_array($data['owner_user_id'], $auth_user_ids)) {
                $isDel = false;
                $errorMessage[] = '名称为' . $data['name'] . '的商机删除失败,错误原因：无权操作';
                continue;
            }
            $delIds[] = $v;
        }
        if ($delIds) {
            $data = $businessModel->delDatas($delIds);
            if (!$data) {
                return resultArray(['error' => $businessModel->getError()]);
            }
            //删除操作记录
            $actionRecordModel = new \app\admin\model\ActionRecord();
            $res = $actionRecordModel->delDataById(['types' => 'crm_business', 'action_id' => $delIds]);
        }
        if ($errorMessage) {
            return resultArray(['error' => $errorMessage]);
        } else {
            return resultArray(['data' => '删除成功']);
        }
    }

    /**
     * 符合条件的商机状态组
     * @param
     * @return
     * @author Michael_xu
     */
    public function statusList()
    {
        $businessStatusModel = model('BusinessStatus');
        $userInfo = $this->userInfo;
        $list = db('crm_business_type')
            ->where(['structure_id' => ['like', '%,' . $userInfo['structure_id'] . ',%'], 'status' => 1])
            ->whereOr('structure_id', '')
            ->select();
        foreach ($list as $k => $v) {
            $list[$k]['statusList'] = $businessStatusModel->getDataList($v['type_id']);
        }
        return resultArray(['data' => $list]);
    }

    /**
     * 商机转移
     * @param owner_user_id 变更负责人
     * @param is_remove 1移出，2转为团队成员
     * @param type 权限 1只读2读写
     * @return
     * @author Michael_xu
     */
    public function transfer()
    {
        $param = $this->param;
        $userInfo = $this->userInfo;
        $businessModel = model('Business');
        $settingModel = model('Setting');
        $userModel = new \app\admin\model\User();
        $authIds = $userModel->getUserByPer(); //权限范围的user_id

        if (!$param['owner_user_id']) {
            return resultArray(['error' => '变更负责人不能为空']);
        }
        if (!$param['business_id'] || !is_array($param['business_id'])) {
            return resultArray(['error' => '请选择需要转移的商机']);
        }

        $is_remove = $param['is_remove'] == 2 ?: 1;
        $type = $param['type'] == 2 ?: 1;

        $data = [];
        $data['owner_user_id'] = $param['owner_user_id'];
        $data['update_time'] = time();

        $ownerUserName = $userModel->getUserNameById($param['owner_user_id']);
        $errorMessage = [];
        foreach ($param['business_id'] as $business_id) {
            $businessInfo = $businessModel->getDataById($business_id);

            if (!$businessInfo) {
                $errorMessage[] = 'id:为' . $business_id . '的商机转移失败，错误原因：数据不存在；';
                continue;
            }
            //权限判断
            if (!in_array($businessInfo['owner_user_id'], $authIds)) {
                $errorMessage[] = $businessInfo['name'] . '"转移失败，错误原因：无权限；';
                continue;
            }
            $resBusiness = db('crm_business')->where(['business_id' => $business_id])->update($data);
            if (!$resBusiness) {
                $errorMessage[] = $businessInfo['name'] . '"转移失败，错误原因：数据出错；';
                continue;
            }
            //修改记录
            updateActionLog($userInfo['id'], 'crm_business', $business_id, '', '', '将商机转移给：' . $ownerUserName);
        }
        if (!$errorMessage) {
            return resultArray(['data' => '转移成功']);
        } else {
            return resultArray(['error' => $errorMessage]);
        }
    }

    /**
     * 相关产品
     * @param
     * @return
     * @author Michael_xu
     */
    public function product()
    {
        $productModel = model('Product');
        $userModel = new \app\admin\model\User();
        $param = $this->param;
        $userInfo = $this->userInfo;
        if (!$param['business_id']) {
            return resultArray(['error' => '参数错误']);
        }
        $businessInfo = db('crm_business')->where(['business_id' => $param['business_id']])->find();
        //判断权限
        $auth_user_ids = $userModel->getUserByPer('crm', 'business', 'read');
        //读写权限
        $roPre = $userModel->rwPre($userInfo['id'], $businessInfo['ro_user_id'], $businessInfo['rw_user_id'], 'read');
        $rwPre = $userModel->rwPre($userInfo['id'], $businessInfo['ro_user_id'], $businessInfo['rw_user_id'], 'update');
        if (!in_array($businessInfo['owner_user_id'], $auth_user_ids) && !$roPre && !$rwPre) {
            header('Content-Type:application/json; charset=utf-8');
            exit(json_encode(['code' => 102, 'error' => '无权操作']));
        }
        $dataList = db('crm_business_product')->where(['business_id' => $param['business_id']])->select();
        foreach ($dataList as $k => $v) {
            $where = [];
            $where['product_id'] = $v['product_id'];
            $productInfo = db('crm_product')->where($where)->field('name,category_id')->find();
            $category_name = db('crm_product_category')->where(['category_id' => $productInfo['category_id']])->value('name');
            $dataList[$k]['name'] = $productInfo['name'] ?: '';
            $dataList[$k]['category_id_info'] = $category_name ?: '';
        }
        $list['list'] = $dataList ?: [];
        $list['total_price'] = $businessInfo['total_price'] ?: '0.00';
        $list['discount_rate'] = $businessInfo['discount_rate'] ?: '0.00';
        return resultArray(['data' => $list]);
    }

    /**
     * 商机状态推进
     * @param business_id 商机ID
     * @param status_id 推进商机状态ID
     * @return
     * @author Michael_xu
     */
    public function advance()
    {
        $param = $this->param;
        $userInfo = $this->userInfo;
        $userModel = new \app\admin\model\User();
        $is_end = $param['is_end'] ?: 0; //1赢单2输单3无效
        if (!$param['business_id']) {
            return resultArray(['error' => '参数错误']);
        }
        $businessInfo = db('crm_business')->where(['business_id' => $param['business_id']])->find();
        if ($businessInfo['is_end']) {
            return resultArray(['error' => '已结束，不能推进']);
        }
        //判断权限
        $auth_user_ids = $userModel->getUserByPer('crm', 'business', 'update');
        //读写权限
        $rwPre = $userModel->rwPre($userInfo['id'], $businessInfo['ro_user_id'], $businessInfo['rw_user_id'], 'update');
        if (!in_array($businessInfo['owner_user_id'], $auth_user_ids) && !$rwPre) {
            header('Content-Type:application/json; charset=utf-8');
            exit(json_encode(['code' => 102, 'error' => '无权操作']));
        }

        $status_id = $param['status_id'];
        $statusInfo = db('crm_business_status')->where(['type_id' => $businessInfo['type_id'], 'status_id' => $status_id])->find();
        if (!$statusInfo && !$is_end) {
            return resultArray(['error' => '参数错误']);
        }
        $data = [];
        $data['update_time'] = time();
        $data['is_end'] = $is_end;
        if ($is_end) $status_id = $is_end;
        if ($status_id) {
            $data['status_id'] = $status_id;
            $data['status_time'] = time();
        }
        if ($is_end == 1) {
            // 赢单后，自动生成编号，编号规则是：签约公司字母简称+年月（201909）+自动流水号（4位）
            // 例如：壹墨签订的合同：YM2019090001
            $companyShort = array(
                '上海壹墨图文设计制作有限公司' => 'YM',
                '上海宝谊图片有限公司' => 'BY',
                '上海动智广告有限公司' => 'DZ',
                '上海宝高文化传播有限公司' => 'BG',
                '上海颖闪企划咨询有限公司' => 'YS',
                '上海威佳广告有限公司' => 'JW',
                '上海迪帕智能科技有限公司' => 'DP',
                '上海天墨品牌发展有限公司' => 'TM',
                '上海圆觉供应链管理（上海）有限公司' => 'YJ',
                '上海艾盛建筑设计中心' => 'AS',
                '上海创威安装服务中心' => 'CW',
                '上海染三文化传播有限公司' => 'RS',
                '其他' => 'QT',
            );
            // localhost 'crm_fsfojh';
            // 生产环境 'crm_axgllm'
            $short = $companyShort[$businessInfo['crm_axgllm']];
            $businessCode = db('crm_business')
                ->where('crm_ihutnj', 'not null')
                ->where('crm_ihutnj', 'like', "{$short}%")
                ->order('business_id desc')
                ->limit(1)
                ->value('crm_ihutnj');
            // 当前年月日
            $ymd = date("Ymd");
            if (null != $businessCode) {
                //检查当前编号
                $tmpPrefix = $short . $ymd;
                if ($tmpPrefix == substr($businessCode, 0, 10)) {
                    // 截取后四位流水号
                    $serialStr = substr($businessCode, -4);
                    // +1
                    $serialInt = intval($serialStr) + 1;
                    // 不足4位前导补0
                    $newSerial = sprintf("%04d", $serialInt);
                    $newBusinessCode = $short . $ymd . $newSerial;
                } else {
                    $newBusinessCode = $short . $ymd . '0001';
                }
            } else {
                $newBusinessCode = $short . $ymd . '0001';
            }
            $data['crm_ihutnj'] = $newBusinessCode;
        }
        $res = db('crm_business')->where(['business_id' => $param['business_id']])->update($data);
        if (!$res) {
            return resultArray(['error' => '推进失败，请重试']);
        } else {
            //推进记录添加
            $temp['status_id'] = $status_id ?: 0;
            $temp['is_end'] = $is_end ?: 0;
            $temp['business_id'] = $param['business_id'];
            $temp['create_time'] = time();
            $temp['owner_user_id'] = $userInfo['id'];
            $temp['remark'] = $param['remark'] ?: '';
            Db::name('CrmBusinessLog')->insert($temp);
            return resultArray(['data' => '推进成功']);
        }
    }
}
