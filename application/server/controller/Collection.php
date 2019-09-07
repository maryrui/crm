<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/7/11
 * Time: 8:55
 */

namespace app\server\controller;

use think\Controller;
use think\Request;
use Workerman\Lib\Timer;
use think\console\Output;

class Collection extends Controller
{
    public function __construct(Request $request = null)
    {
        parent::__construct($request);
    }

    public function addTimer()
    {
        $timeInterval = 10;
        Timer::add($timeInterval,array($this,'index'),array(),true);
    }
    public function index(){
        $total=$this->get_jinse();
        $output = new Output();
        $output->writeln(['msg'=>"此次采集数据共 $total 条。",'total'=>$total]);
        return json(['msg'=>"此次采集数据共 $total 条。",'total'=>$total]);
    }


    /**
     * 获取金色财经资讯
     */
    public function get_jinse(){
        $url="https://api.jinse.com/v4/live/list?limit=20";
        $data=$this->get_curl($url);
        $data=json_decode($data);
        $data=$data->list[0]->lives;

        return count($data);
    }
    public function get_curl($url){
        $ch=curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_HEADER,0);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        $output = curl_exec($ch);

        if($output === FALSE ){
            echo "CURL Error:".curl_error($ch);
        }
        curl_close($ch);
        // 4. 释放curl句柄

        return $output;

    }

}