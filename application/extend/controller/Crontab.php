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
                    'first'=>"发票跟进提醒",
                    'keyword1'=>$item['invoice_code'],
                    'keyword2'=>$item['update_time'],
                    'remark'=>"合同名称：".$item['name']."，回款金额：".$item['money']."，回款方式：".$item['return_type']
                );
                print_r($data);
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
                    'first'=>"客户跟进提醒：",
                    'keyword1'=>'无',
                    'keyword2'=>$item['update_time'],
                    'remark'=>"客户名称：".$item['name']."，跟进内容：".$item['content'].",跟进方式：".$item['category']
                );
                print_r($data);
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
                    'first'=>"合同跟进提醒：",
                    'keyword1'=>$item['crm_ihutnj'],
                    'keyword2'=>$item['update_time'],
                    'remark'=>"合同名称：".$item['name']."，跟进内容：".$item['content'].",跟进方式：".$item['category']
                );
                print_r($data);
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
                    'first'=>"线索跟进提醒：",
                    'keyword1'=>'无',
                    'keyword2'=>$item['update_time'],
                    'remark'=>"线索名称：".$item['name']."，跟进内容：".$item['content'].",跟进方式：".$item['category']
                );
                print_r($data);
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
                    'first'=>"联系人跟进提醒：",
                    'keyword1'=>'无',
                    'keyword2'=>$item['update_time'],
                    'remark'=>"联系人名称：".$item['name']."，跟进内容：".$item['content'].",跟进方式：".$item['category']
                );
                print_r($data);
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
                    'first'=>"订单跟进提醒：",
                    'keyword1'=>$item['num'],
                    'keyword2'=>$item['update_time'],
                    'remark'=>"合同名称：".$item['name']."，跟进内容：".$item['content'].",跟进方式：".$item['category']
                );
                print_r($data);
                $message->template($openId,$data);
            }
        }
    }
}