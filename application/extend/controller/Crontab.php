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
        $this->leads();
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
                $data = array('name'=>$item['name'],'return_date'=>$item['return_date']);
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
                $data = array('name'=>$item['name'],'next_time'=>$item['next_time']);
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
                $data = array('name'=>$item['name'],'next_time'=>$item['next_time']);
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
                $data = array('name'=>$item['name'],'next_time'=>$item['next_time']);
                $templateId ="";
                $message->template($openId,$templateId,$data);
            }
        }
    }
}