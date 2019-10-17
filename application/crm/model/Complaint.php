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
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';
    protected $autoWriteTimestamp = true;

    public function getDataList($request)
    {
        $list =db('crm_complaint')
            ->alias('complaint')
            ->join('__ADMIN_EXAMINE_FLOW__ examine','complaint.flow_id=examine.flow_id')
            ->where(function ($query)use ($request){
                $query->where('examine.user_ids', array('like','%,'.$request['user_id'].',%'))
                    ->whereor('examine.structure_ids', array('like','%,'.$request['structure_id'].',%'));
            })
            ->where(function ($query)use ($request){
                $query->where('complaint.create_user_id', $request['user_id'])
                    ->whereor('complaint.check_user_id', array('like','%,'.$request['user_id'].',%'));
            })
            ->field('complaint.*')
            ->limit(($request['page']-1)*$request['limit'], $request['limit'])
            ->order("complaint.create_time desc")
            ->select();
        //Db::table('crm_complaint')->getLastSql();
        $dataCount =db('crm_complaint')
            ->alias('complaint')
            ->join('admin_examine_flow examine','complaint.flow_id=examine.flow_id')
            ->where(function ($query)use ($request){
                $query->where('examine.user_ids', array('like','%,'.$request['user_id'].',%'))
                    ->whereor('examine.structure_ids', array('like','%,'.$request['structure_id'].',%'));
            })
            ->where(function ($query)use ($request){
                $query->where('complaint.create_user_id', $request['user_id'])
                    ->whereor('complaint.check_user_id', array('like','%,'.$request['user_id'].',%'));
            })
            ->count();
        $data['list'] = $list;
        $data['dataCount'] = $dataCount ? : 0;
        return $data;
    }

    public function createData($param)
    {
        $complaint = new Complaint();
        $param['create_time'] = time();

        // 接收文件数据
        $fileArr = $param['file'];
        unset($param['file']);

        $complaint->data($param);
        $data = $complaint->save();
        $complaintId = $complaint->id;
        if (!$data) {
            return resultArray(['error' => $complaint->getError()]);
        }
        //处理附件关系
        if ($fileArr) {
            $fileModel = new \app\admin\model\File();
            $resData = $fileModel->createDataById($fileArr, 'crm_complaint', $complaintId);
            if ($resData == false) {
                $this->error = '附件上传失败';
                return false;
            }
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



}