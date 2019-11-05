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
    private $statusArr = ['0'=>'待审核','1'=>'审核中','2'=>'审核通过','3'=>'已拒绝','4'=>'已撤回'];

    public function getDataList($request)
    {
        if ($request['order_type'] && $request['order_field']) {
            $order = trim($request['order_field']) . ' ' . trim($request['order_type']);
        } else {
            $order = 'complaint.update_time desc';
        }

        $list =db('crm_complaint')
            ->alias('complaint')
            ->join('__ADMIN_EXAMINE_FLOW__ flow','complaint.flow_id=flow.flow_id')
            ->join('__ADMIN_EXAMINE_STEP__ step','step.flow_id=flow.flow_id')
            ->where(function ($query)use ($request){
                $query->where('complaint.create_user_id', $request['user_id'])
                    ->whereor('step.user_id', array('like','%,'.$request['user_id'].',%'));
            })
            ->field('complaint.*')
            ->Distinct(true)
            ->limit(($request['page']-1)*$request['limit'], $request['limit'])
            ->order($order)
            ->select();
        foreach ($list as $k => $v) {
            $list[$k]['check_status_info'] = $this->statusArr[$v['check_status']];
        }

        $dataCount =db('crm_complaint')
            ->alias('complaint')
            ->join('__ADMIN_EXAMINE_FLOW__ flow','complaint.flow_id=flow.flow_id')
            ->join('__ADMIN_EXAMINE_STEP__ step','step.flow_id=flow.flow_id')
            ->where(function ($query)use ($request){
                $query->where('complaint.create_user_id', $request['user_id'])
                    ->whereor('step.user_id', array('like','%,'.$request['user_id'].',%'));
            })
            ->count('DISTINCT complaint.id');
        $data['list'] = $list;
        $data['dataCount'] = $dataCount ? : 0;
        return $data;
    }

    public function getMessageList($request){
        $request = $this->fmtRequest($request);
        $requestMap = $request['map'] ?: [];
        $map = where_arr($requestMap, 'crm', 'complaint', 'index');
        if ($request['order_type'] && $request['order_field']) {
            $order = trim($request['order_field']) . ' ' . trim($request['order_type']);
        } else {
            $order = 'complaint.update_time desc';
        }

        $list =db('crm_complaint')
            ->alias('complaint')
            ->join('__ADMIN_EXAMINE_FLOW__ flow','complaint.flow_id=flow.flow_id')
            ->join('__ADMIN_EXAMINE_STEP__ step','step.flow_id=flow.flow_id')
            ->where($map)
            ->field('complaint.*')
            ->Distinct(true)
            ->limit(($request['page']-1)*$request['limit'], $request['limit'])
            ->order($order)
            ->select();

        $dataCount =db('crm_complaint')
            ->alias('complaint')
            ->join('__ADMIN_EXAMINE_FLOW__ flow','complaint.flow_id=flow.flow_id')
            ->join('__ADMIN_EXAMINE_STEP__ step','step.flow_id=flow.flow_id')
            ->where($map)
            ->count('DISTINCT complaint.id');
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