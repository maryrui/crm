<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/7/10
 * Time: 11:39
 */
namespace app\extend\controller;

use wechat\Message;
class Crontab
{

    public function index()
    {
        $this->customer();
        $this->contacts();
        $this->leads();
        $this->receivablesPlan();
        $this->business();
        $this->contract();
    }

    public function receivablesPlan()
    {
        $model = new \app\crm\model\ReceivablesPlan();
        $list=$model->crontabList();
        $message = new Message();
        foreach ($list as $item){
            $openId = $item["openid"];
            if($openId){
                $data = array(
                    'first'=>["value"=>"发票跟进提醒"],
                    'keyword1'=>["value"=>$item['invoice_code']],
                    'keyword2'=>["value"=>date('Y-m-s h:i:s', $item['update_time'])],
                    'remark'=>["value"=>"合同名称：".$item['name']."，回款金额：".$item['money']."，回款方式：".$item['return_type']]
                );
                $message->template($openId,$data);
            }
        }

    }

    public function customer()
    {
        $model = new \app\crm\model\Customer();
        $list=$model->crontabList();
        $message = new Message();
        foreach ($list as $item){
            $openId = $item["openid"];
            if($openId){
                $data = array(
                    'first'=>["value"=>"客户跟进提醒"],
                    'keyword1'=>["value"=>'无'],
                    'keyword2'=>["value"=>date('Y-m-s h:i:s', $item['update_time'])],
                    'remark'=>["value"=>"客户名称：".$item['name']."，跟进内容：".$item['content'].",跟进方式：".$item['category']]
                );
                $message->template($openId,$data);
            }
        }
    }

    public function business()
    {
        $model = new \app\crm\model\Business();
        $list=$model->crontabList();
        $message = new Message();
        foreach ($list as $item){
            $openId = $item["openid"];
            if($openId){
                $data = array(
                    'first'=>["value"=>"合同跟进提醒"],
                    'keyword1'=>["value"=>$item['crm_ihutnj']],
                    'keyword2'=>["value"=>date('Y-m-s h:i:s', $item['update_time'])],
                    'remark'=>["value"=>"合同名称：".$item['name']."，跟进内容：".$item['content'].",跟进方式：".$item['category']]
                );
                $message->template($openId,$data);
            }
        }
    }

    public function leads()
    {
        $model = new \app\crm\model\Leads();
        $list=$model->crontabList();
        $message = new Message();
        foreach ($list as $item){
            $openId = $item["openid"];
            if($openId){
                $data = array(
                    'first'=>["value"=>"线索跟进提醒"],
                    'keyword1'=>["value"=>'无'],
                    'keyword2'=>["value"=>date('Y-m-s h:i:s', $item['update_time'])],
                    'remark'=>["value"=>"线索名称：".$item['name']."，跟进内容：".$item['content'].",跟进方式：".$item['category']]
                );
                $message->template($openId,$data);
            }
        }
    }

    public function contacts()
    {
        $model = new \app\crm\model\Contacts();
        $list=$model->crontabList();
        $message = new Message();
        foreach ($list as $item){
            $openId = $item["openid"];
            if($openId){
                $data = array(
                    'first'=>["value"=>"联系人跟进提醒"],
                    'keyword1'=>["value"=>'无'],
                    'keyword2'=>["value"=>date('Y-m-s h:i:s', $item['update_time'])],
                    'remark'=>["value"=>"联系人名称：".$item['name']."，跟进内容：".$item['content'].",跟进方式：".$item['category']]
                );
                $message->template($openId,$data);
            }
        }
    }

    public function contract()
    {
        $model = new \app\crm\model\Contract();
        $list=$model->crontabList();
        $message = new Message();
        foreach ($list as $item){
            $openId = $item["openid"];
            if($openId){
                $data = array(
                    'first'=>["value"=>"订单跟进提醒"],
                    'keyword1'=>["value"=>$item['num']],
                    'keyword2'=>["value"=>date('Y-m-s h:i:s', $item['update_time'])],
                    'remark'=>["value"=>"订单名称：".$item['name']."，跟进内容：".$item['content'].",跟进方式：".$item['category']]
                );
                $message->template($openId,$data);
            }
        }
    }
}