<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/7/11
 * Time: 14:47
 */

namespace app\crm\model;

use think\Db;
use app\admin\model\Common;
use think\Validate;
class Complaint extends Common
{
    /**
     * 为了数据库的整洁，同时又不影响Model和Controller的名称
     * 我们约定每个模块的数据表都加上相同的前缀，比如CRM模块用crm作为数据表前缀
     */
    protected $name = 'crm_complaint';
    protected $autoWriteTimestamp = true;

    public function getDataList($request)
    {
        $param['check_user_id'] = $request['check_user_id'];
        $list =$this->where($param)->limit(($request['page']-1)*$request['limit'], $request['limit'])
            ->order("create_time desc")->select();
        $dataCount = $this->where($param)->count();
        $data['list'] = $list;
        $data['dataCount'] = $dataCount ? : 0;
        return $data;
    }

    public function createData($param)
    {
        $complaint = new Complaint();
        $param['create_time'] = time();
        $complaint->data($param);
        $data = $complaint->save();
        if (!$data) {
            return resultArray(['error' => $complaint->getError()]);
        }
        return resultArray(['data' => '添加成功']);
    }

    public function getDataById($id = '')
    {
        $data = $this->find($id);
        if (!$data) {
            $this->error = '暂无此数据';
            return false;
        }
        return $data;
    }

    public function updateDataById($param)
    {
        $flag = $this->where(['id' => $param['id']])->update($param);
        if ($flag) {
            return true;
        } else {
            $this->error = '保存失败';
            return false;
        }
    }


    public function getComplaintTypeList(){
        $list = db('admin_complaint_type')->select();
        return $list;
    }

    public function saveComplaintType($data){
        $types = [];
        foreach ($data as $k=>$v) {
            $types[$k]['type'] = $v['type'];
            $types[$k]['depart'] = $v['depart']? arrayToString($v['depart']) : '';
            $types[$k]['create_time'] = time();
        }

        try {
            db('admin_complaint_type')->where('1=1')->delete();
            db('admin_complaint_type')->insertAll($types);
            return true;
        }catch(\Exception $e) {
            return false;
        }
    }
}