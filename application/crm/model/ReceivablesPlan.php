<?php
// +----------------------------------------------------------------------
// | Description: 回款计划计划
// +----------------------------------------------------------------------
// | Author:  Michael_xu | gengxiaoxu@5kcrm.com
// +----------------------------------------------------------------------
namespace app\crm\model;

use think\Db;
use app\admin\model\Common;
class ReceivablesPlan extends Common
{
    /**
     * 为了数据库的整洁，同时又不影响Model和Controller的名称
     * 我们约定每个模块的数据表都加上相同的前缀，比如CRM模块用crm作为数据表前缀
     */
    protected $name = 'crm_receivables_plan';
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';
    protected $autoWriteTimestamp = true;
    private $statusArr = ['0'=>'待审核','1'=>'审核中','2'=>'审核通过','3'=>'已拒绝','4'=>'已撤回'];

    /**
     * [getDataList 回款计划list]
     * @author Michael_xu
     * @param     [string]                   $map [查询条件]
     * @param     [number]                   $page     [当前页数]
     * @param     [number]                   $limit    [每页数量]
     * @return    [array]                    [description]
     */
    public function getDataList($request)
    {
        $userModel = new \app\admin\model\User();
        $search = $request['search'];
        $user_id = $request['user_id'];
        $scene_id = (int)$request['scene_id'];
        unset($request['scene_id']);
        unset($request['search']);
        unset($request['user_id']);

        $request = $this->fmtRequest($request);
        $requestMap = $request['map'] ?: [];
        $sceneModel = new \app\admin\model\Scene();
        if ($scene_id) {
            //自定义场景
            $sceneMap = $sceneModel->getDataById($scene_id, $user_id, 'receivables_plan') ? : [];
        } else {
            //默认场景
            $sceneMap = $sceneModel->getDefaultData('receivables_plan', $user_id) ? : [];
        }
        if ($search) {
            //普通筛选
            $sceneMap['invoice_code'] = ['condition' => 'contains','value' => $search,'form_type' => 'text','name' => '发票编号'];
        }
        //优先级：普通筛选>高级筛选>场景
        $map = $requestMap ? array_merge($sceneMap, $requestMap) : $sceneMap;
        //高级筛选
        $map = where_arr($map, 'crm', 'receivables_plan', 'index');
        $authMap = [];
        $auth_user_ids = $userModel->getUserByPer('crm', 'receivables_plan', 'index');
        if (isset($map['receivables_plan.owner_user_id'])) {
            if (!is_array($map['receivables_plan.owner_user_id'][1])) {
                $map['receivables_plan.owner_user_id'][1] = [$map['receivables_plan.owner_user_id'][1]];
            }
            if ($map['receivables_plan.owner_user_id'][0] == 'neq') {
                $auth_user_ids = array_diff($auth_user_ids, $map['receivables_plan.owner_user_id'][1]) ? : [];	//取差集
            } else {
                $auth_user_ids = array_intersect($map['receivables_plan.owner_user_id'][1], $auth_user_ids) ? : [];	//取交集
            }
            unset($map['receivables_plan.owner_user_id']);
            $auth_user_ids = array_merge(array_unique(array_filter($auth_user_ids))) ? : ['-1'];
            $authMap['receivables_plan.owner_user_id'] = array('in',$auth_user_ids);
        } else {
            $authMapData = [];
            $authMapData['auth_user_ids'] = $auth_user_ids;
            $authMapData['user_id'] = $user_id;
            $authMap = function($query) use ($authMapData){
                $query->where('receivables_plan.owner_user_id',array('in',$authMapData['auth_user_ids']));
            };
        }

        if ($map['receivables_plan.owner_user_id']) {
            $map['contract.owner_user_id'] = $map['receivables_plan.owner_user_id'];
            unset($map['receivables_plan.owner_user_id']);
        }

        if ($request['order_type'] && $request['order_field']) {
            $order = trim($request['order_field']) . ' ' . trim($request['order_type']);
        } else {
            $order = 'receivables_plan.update_time desc';
        }
        $list = db('crm_receivables_plan')
            ->alias('receivables_plan')
            ->join('__CRM_CONTRACT__ contract', 'receivables_plan.contract_id = contract.contract_id', 'LEFT')
            ->join('__CRM_CUSTOMER__ customer', 'receivables_plan.customer_id = customer.customer_id', 'LEFT')
            ->where($map)->where($authMap)
            ->limit(($request['page'] - 1) * $request['limit'], $request['limit'])
            ->field('receivables_plan.*,customer.name as customer_name,contract.name as contract_name, contract.num as contract_num')
            ->order($order)
            ->select();
        $dataCount = db('crm_receivables_plan')
            ->alias('receivables_plan')
            ->join('__CRM_CONTRACT__ contract', 'receivables_plan.contract_id = contract.contract_id', 'LEFT')
            ->join('__CRM_CUSTOMER__ customer', 'receivables_plan.customer_id = customer.customer_id', 'LEFT')
            ->where($map)->where($authMap)
            ->count('plan_id');
        foreach ($list as $k => $v) {
            $list[$k]['create_user_id_info'] = $userModel->getUserById($v['create_user_id']);
            $list[$k]['contract_id_info']['name'] = $v['contract_name'] ?: '';
            $list[$k]['contract_id_info']['contract_id'] = $v['contract_id'] ?: '';
            $list[$k]['contract_id_info']['contract_num'] = $v['contract_num'] ?: '';
            $list[$k]['customer_id_info']['name'] = $v['customer_name'] ?: '';
            $list[$k]['customer_id_info']['customer_id'] = $v['customer_id'] ?: '';
            $list[$k]['check_status_info'] = $this->statusArr[$v['check_status']];
            $files = db('crm_receivables_plan_file')->where(['plan_id'=>$v['plan_id']])->column('file_id');
            $list[$k]['file'] = arrayToString($files);
        }
        $data = [];
        $data['list'] = $list;
        $data['dataCount'] = $dataCount ?: 0;
        return $data ?: [];
    }

    /**
     * [crontabList 计划任务list]
     * @author Chen
     * @return    [array]
     */
    public function crontabList(){
        $param['plan.remind_date'] = array('elt',date('Y-m-d',time()));
        $param['plan.return_date'] = array('egt',date('Y-m-d',time()));
        $list = Db::name('crm_receivables_plan')->alias('plan')
            ->join('__CRM_CONTRACT__ contract', 'plan.contract_id=contract.contract_id')
            ->join('__ADMIN_USER__ user', 'user.id=plan.create_user_id')
            ->field('plan.return_date,plan.invoice_code,plan.update_time,plan.money,plan.return_type,contract.name,user.openid')
            ->where($param)
            ->select();
        return $list;
    }

    /**
     * 创建回款计划信息
     * @author Michael_xu
     * @param
     * @return
     */
    public function createData($param)
    {
        if (!$param['contract_id']) {
            $this->error = '请先选择合同';
        }
        // 自动验证
        $validate = validate($this->name);
        if (!$validate->check($param)) {
            $this->error = $validate->getError();
            return false;
        }
        if ($param['file']) $fileList = $param['file']; //附件
        unset($param['file']);
        //期数规则（1,2,3..）
        $maxNum = db('crm_receivables_plan')->where(['contract_id' => $param['contract_id']])->max('num');
        $param['num'] = $maxNum ? $maxNum + 1 : 1;
        //提醒日期
        $param['remind_date'] = $param['remind'] ? date('Y-m-d', strtotime($param['return_date']) - 86400 * $param['remind']) : $param['return_date'];
        if ($this->data($param)->allowField(true)->save()) {
            $data = [];
            $data['plan_id'] = $this->plan_id;
            $files = [];
            foreach ($fileList as $v){
                $files[]=["plan_id"=>$this->plan_id,'file_id'=>$v];
            }
            db('crm_receivables_plan_file')->insertAll($files);
            return $data;
        } else {
            $this->error = '添加失败';
            return false;
        }
    }

    /**
     * 编辑回款计划
     * @author Michael_xu
     * @param
     * @return
     */
    public function updateDataById($param, $plan_id = '')
    {
        $dataInfo = $this->getDataById($plan_id);
        if (!$dataInfo) {
            $this->error = '数据不存在或已删除';
            return false;
        }
        $param['plan_id'] = $plan_id;
        //过滤不能修改的字段
        $unUpdateField = ['num', 'create_user_id', 'is_deleted', 'delete_time', 'delete_user_id'];
        foreach ($unUpdateField as $v) {
            unset($param[$v]);
        }

        // 自动验证
        $validate = validate($this->name);
        if (!$validate->check($param)) {
            $this->error = $validate->getError();
            return false;
        }
        if ($param['file']) $fileList = $param['file']; //附件
        unset($param['file']);

        //提醒日期
        $param['remind_date'] = $param['remind'] ? date('Y-m-d', strtotime($param['return_date']) - 86400 * $param['remind']) : $param['return_date'];
        if ($this->allowField(true)->save($param, ['plan_id' => $plan_id])) {
            $data = [];
            $data['plan_id'] = $plan_id;
            $files = [];
            foreach ($fileList as $v){
                $files[]=["plan_id"=>$plan_id,'file_id'=>$v];
            }
            db('crm_receivables_plan_file')->where(['plan_id'=>$plan_id])->delete();
            db('crm_receivables_plan_file')->insertAll($files);
            return $data;
        } else {
            $this->error = '编辑失败';
            return false;
        }
    }

    /**
     * 回款计划数据
     * @param  $id 回款计划ID
     * @return
     */
    public function getDataById($id = '')
    {
        $map['plan_id'] = $id;
        $dataInfo = $this->where($map)->find();
        if (!$dataInfo) {
            $this->error = '暂无此数据';
            return false;
        }
        $userModel = new \app\admin\model\User();
        $dataInfo['create_user_info'] = $userModel->getUserById($dataInfo['create_user_id']);
        $dataInfo['plan_id'] = $id;
        $files = db('crm_receivables_plan_file')->where(['plan_id'=>$id])->column('file_id');
        $dataInfo['file'] = arrayToString($files);

        return $dataInfo;
    }


    /**
     * 回款计划数据
     * @param  $id 回款计划ID
     * @return
     */
    public function delDataById($id = '')
    {
        Db::startTrans();
        try {
            db('crm_receivables_plan')->where(['plan_id'=>$id])->delete();
            db('crm_receivables_plan_file')->where(['plan_id'=>$id])->delete();
            // 提交事务
            Db::commit();
            return true;
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            return false;
        }
    }

    //模拟自定义字段返回
    public function getField()
    {
        $field_arr = [
            '0' => [
                'field' => 'customer_id',
                'name' => '客户名称',
                'form_type' => 'customer',
                'setting' => []
            ],
            '1' => [
                'field' => 'contract_id',
                'name' => '合同编号',
                'form_type' => 'contract',
                'setting' => []
            ],
            '2' => [
                'field' => 'money',
                'name' => '计划回款金额',
                'form_type' => 'floatnumber',
                'setting' => []
            ],
            '3' => [
                'field' => 'return_date',
                'name' => '计划回款日期',
                'form_type' => 'date',
                'setting' => []
            ],
            '4' => [
                'field' => 'return_type',
                'name' => '计划回款方式',
                'form_type' => 'select',
                'setting' => '支付宝\n微信\n转账'
            ],
            '5' => [
                'field' => 'remind',
                'name' => '提前几日提醒',
                'form_type' => 'number',
                'setting' => []
            ],
            '6' => [
                'field' => 'remark',
                'name' => '备注',
                'form_type' => 'textarea',
                'setting' => []
            ],
            '7' => [
                'field' => 'file',
                'name' => '附件',
                'form_type' => 'file',
                'setting' => []
            ]
        ];
        return $field_arr;
    }
}