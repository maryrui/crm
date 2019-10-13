<?php
// +----------------------------------------------------------------------
// | Description: 合同
// +----------------------------------------------------------------------
// | Author:  Michael_xu | gengxiaoxu@5kcrm.com
// +----------------------------------------------------------------------
namespace app\bi\model;

use app\admin\model\Field;
use think\Db;
use app\admin\model\Common;
use think\Request;
use think\Validate;

class Contract extends Common
{
    /**
     * 为了数据库的整洁，同时又不影响Model和Controller的名称
     * 我们约定每个模块的数据表都加上相同的前缀，比如CRM模块用crm作为数据表前缀
     */
    protected $name = 'crm_contract';
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';
    protected $autoWriteTimestamp = true;
    private $statusArr = ['0' => '待审核', '1' => '审核中', '2' => '审核通过', '3' => '已拒绝', '4' => '已撤回'];

    /**
     * [getDataList 合同金额]
     * @param     [string]                   $map [查询条件]
     * @param     [number]                   $page     [当前页数]
     * @param     [number]                   $limit    [每页数量]
     * @return    [array]                    [description]
     * @author Michael_xu
     */
    function getWhereByMoney($whereArr)
    {
        $money = db('crm_contract')->where($whereArr)->sum('money');
        return $money;
    }

    /**
     * [getSortByMoney 根据合同金额排序]
     * @param  [type] $whereArr [description]
     * @return [type]           [description]
     */
    function getSortByMoney($whereArr)
    {
        $money = db('crm_contract')->group('owner_user_id')->field('owner_user_id,sum(money) as money')->order('money desc')->where($whereArr)->select();
        return $money;
    }

    /**
     * [getDataList 根据合同签约数排序]
     * @param     [string]                   $map [查询条件]
     * @param     [number]                   $page     [当前页数]
     * @param     [number]                   $limit    [每页数量]
     * @return    [array]                    [description]
     * @author Michael_xu
     */
    function getSortByCount($whereArr)
    {
        $money = db('crm_contract')->group('owner_user_id')->field('owner_user_id,count(contract_id) as count')->order('count desc')->where($whereArr)->select();
        return $money;
    }

    /**
     * 获取合同数量
     * @return [type] [description]
     */
    function getDataCount($whereArr)
    {
        $count = db('crm_contract')->where($whereArr)->count('contract_id');
        return $count;
    }

    /**
     * 获取合同金额
     * @return [type] [description]
     */
    function getDataMoney($whereArr)
    {
        $money = db('crm_contract')->where($whereArr)->sum('money');
        return $money;
    }

    function getAccounts($whereArr, $createTime, $balanceFlag)
    {
//        $contracts = Db::name("crm_contract")
//            ->field(['contract_id','name','money', 'contacts_id'])
//            ->where($whereArr)
//            ->select();
        // 查找时间范围内的已审批的发票
        $plansContract = DB::name('crm_receivables_plan')
            ->where('check_status', 'eq', 2)
            ->where('create_time', $createTime)
            ->column('contract_id');
        if (false == empty($plansContract)) {
            $contractIds = array_unique($plansContract);
            $whereArr['tract.contract_id'] = array('in', $contractIds);
        }

        $contracts = Db::name('crm_contract')->alias('tract')
            ->field('tract.contract_id,tract.name,tract.money,tract.owner_user_id,tract.num,user.realname,
            tract.customer_id,customer.name as customer_name')
            ->join('__CRM_CUSTOMER__ customer', 'tract.customer_id = customer.customer_id')
            ->join('__ADMIN_USER__ user', 'tract.owner_user_id = user.id', 'LEFT')
            ->where($whereArr)
            ->select();
        foreach ($contracts as $i => $v) {
            // 未合并相同发票下的回款
//            $balance = $v['money'];
//            $receivables = Db::name("crm_receivables")
//                ->field(['plan_id', 'money'])
//                ->where(['contract_id' => $v['contract_id']])
//                ->select();
//
//            $contracts[$i]['receivables'] = $receivables;
//            foreach ($receivables as $j => $v2) {
//                $plans = Db::name("crm_receivables_plan")
//                    ->field(['plan_id', 'return_date', 'money', 'status', 'invoice_code'])
//                    ->where(['invoice_code' => $v2['plan_id'], 'check_status' => 2])
//                    ->select();
//
//                $contracts[$i]['receivables'][$j]['plans'] = $plans;
//                $contracts[$i]['receivables'][$j]['balance'] = $plans[0]['money'] - $v2['money'];
//                foreach ($plans as $plan) {
//                    $balance = $balance - $plan['money'];
//                }
//            }
//            $contracts[$i]['balance'] = $balance;

            // 订单总金额，暂未使用
            $contractBalance = $v['money'];

            // 同一个订单的所有发票
            $receivablesPlan = Db::name("crm_receivables_plan")
                ->field(['invoice_code', 'money'])
                ->where(['contract_id' => $v['contract_id'], 'check_status' => 2])
                ->select();

            $contracts[$i]['receivables_plan'] = $receivablesPlan;

            // 已开发票的总金额
            $receivablesPlanTotalMoney = 0;
            // 一个订单下已回款的总金额
            $receivableTotalMoney = 0;

            foreach ($receivablesPlan as $j => $v2) {
                // 一张发票下的所有回款
                $planReceivableTotal = 0;
                $receivablesPlanTotalMoney += $v2['money'];
                $receivables = Db::name("crm_receivables")
                    ->field(['plan_id', 'return_time', 'money'])
                    ->where(['plan_id' => $v2['invoice_code'], 'check_status' => 2])
                    ->select();

                // 保存最后一次回款的日期
                $receivableReturnDate = '';
                foreach ($receivables as $receivable) {
                    $planReceivableTotal += $receivable['money'];
                    $receivableReturnDate = $receivable['return_time'];
                }
                // 一张发票下所有的回款金额
                $contracts[$i]['receivables_plan'][$j]['receivables_money'] = $planReceivableTotal;
                // 一张发票下欠款的金额
                $contracts[$i]['receivables_plan'][$j]['balance'] = $v2['money'] - $planReceivableTotal;
                $contracts[$i]['receivables_plan'][$j]['return_date'] = $receivableReturnDate;
                // 一个订单下已回款的总金额
                $receivableTotalMoney += $planReceivableTotal;
            }

            // 当前订单未回款金额 = 已开发票总金额 - 已回款总金额
            $contracts[$i]['balance'] = $receivablesPlanTotalMoney - $receivableTotalMoney;
        }

        // 已回款，删除balance > 0的元素
        if ($balanceFlag == 1) {
            foreach ($contracts as $key => $value) {
                if ($value['balance'] > 0) {
                    unset($contracts[$key]);
                }
            }
        }
        // 未回款，删除balance == 0的元素
        if ($balanceFlag == 2) {
            foreach ($contracts as $key => $value) {
                if ($value['balance'] == 0) {
                    unset($contracts[$key]);
                }
            }
        }
        return $contracts;
    }
}