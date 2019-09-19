<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/8/28
 * Time: 10:56
 */

namespace app\extend\controller;


class File
{
    public function save()
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: POST');
        header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
        $files = request()->file('file');
        $i = 0;
        $newFiles = array();
        if($files){
            foreach ($files as $v) {
                $newFiles[$i]['obj'] = $v;
                $newFiles[$i]['types'] = 'file';
                $i++;
            }
        }

        $imgs = request()->file('img');
        if ($imgs) {
            foreach ($imgs as $v) {
                $newFiles[$i]['obj'] = $v;
                $newFiles[$i]['types'] = 'img';
                $i++;
            }
        }
        $fileModel = model('File');
        $param = $this->param;
        $res = $fileModel->createData($newFiles, $param);
        if($res){
            return resultArray(['data' => $res]);
        } else {
            return resultArray(['error' => $fileModel->getError()]);
        }
    }
}