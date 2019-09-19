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
        $this->receivablesPlan();
        $this->customer();
        $this->business();
        $this->leads();
        $this->contacts();
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
                    'keyword1'=>"计划回款",
                    'keyword2'=>$item['return_date'],
                    'keyword3'=>"合同名称：".$item['name']."，回款金额：".$item['money']."，回款方式：".$item['return_type']
                );
                print_r($data);
                $templateId ="";
                $message->template($openId,$templateId,$data);
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
                    'keyword1'=>"客户跟进",
                    'keyword2'=>date('Y-m-d H:i:s', $item['next_time']),
                    'keyword3'=>"客户名称：".$item['name']."，跟进内容：".$item['content'].",跟进方式：".$item['category']
                );
                print_r($data);
                $templateId ="";
                $message->template($openId,$templateId,$data);
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
                    'keyword1'=>"商机跟进",
                    'keyword2'=>date('Y-m-d H:i:s', $item['next_time']),
                    'keyword3'=>"商机名称：".$item['name']."，跟进内容：".$item['content'].",跟进方式：".$item['category']
                );
                print_r($data);
                $templateId ="";
                $message->template($openId,$templateId,$data);
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
                    'keyword1'=>"线索跟进",
                    'keyword2'=>date('Y-m-d H:i:s', $item['next_time']),
                    'keyword3'=>"线索名称：".$item['name']."，跟进内容：".$item['content'].",跟进方式：".$item['category']
                );
                print_r($data);
                $templateId ="";
                $message->template($openId,$templateId,$data);
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
                    'keyword1'=>"联系人跟进",
                    'keyword2'=>date('Y-m-d H:i:s', $item['next_time']),
                    'keyword3'=>"联系人名称：".$item['name']."，跟进内容：".$item['content'].",跟进方式：".$item['category']
                );
                print_r($data);
                $templateId ="";
                $message->template($openId,$templateId,$data);
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
                    'keyword1'=>"合同跟进",
                    'keyword2'=>date('Y-m-d H:i:s', $item['next_time']),
                    'keyword3'=>"合同名称：".$item['name']."，跟进内容：".$item['content'].",跟进方式：".$item['category']
                );
                print_r($data);
                $templateId ="";
                $message->template($openId,$templateId,$data);
            }
        }
    }
}