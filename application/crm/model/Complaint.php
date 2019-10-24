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
        $userModel = new \app\admin\model\User();
        $search = $request['search'];
        $user_id = $request['user_id'];
        $scene_id = (int)$request['scene_id'];
        unset($request['scene_id']);
        unset($request['search']);
        unset($request['user_id']);

        $request = $this->fmtRequest($request);
        $requestMap = $request['map'] ?: [];
        $sceneModel = new \app\admin\model\Scene();
        if ($scene_id) {
            //自定义场景
            $sceneMap = $sceneModel->getDataById($scene_id, $user_id, 'complaint') ? : [];
        } else {
            //默认场景
            $sceneMap = $sceneModel->getDefaultData('complaint', $user_id) ? : [];
        }
        if ($search) {
            //普通筛选
            $sceneMap['invoice_code'] = ['condition' => 'contains','value' => $search,'form_type' => 'text','name' => '发票编号'];
        }
        //优先级：普通筛选>高级筛选>场景
        $map = $requestMap ? array_merge($sceneMap, $requestMap) : $sceneMap;
        //高级筛选
        $map = where_arr($map, 'crm', 'complaint', 'index');
        $authMap = [];
        $auth_user_ids = $userModel->getUserByPer('crm', 'complaint', 'index');
        if (isset($map['complaint.create_user_id'])) {
            if (!is_array($map['complaint.create_user_id'][1])) {
                $map['complaint.create_user_id'][1] = [$map['complaint.create_user_id'][1]];
            }
            if ($map['complaint.create_user_id'][0] == 'neq') {
                $auth_user_ids = array_diff($auth_user_ids, $map['complaint.create_user_id'][1]) ? : [];	//取差集
            } else {
                $auth_user_ids = array_intersect($map['complaint.create_user_id'][1], $auth_user_ids) ? : [];	//取交集
            }
            unset($map['complaint.create_user_id']);
            $auth_user_ids = array_merge(array_unique(array_filter($auth_user_ids))) ? : ['-1'];
            $authMap['complaint.create_user_id'] = array('in',$auth_user_ids);
        } else {
            $authMapData = [];
            $authMapData['auth_user_ids'] = $auth_user_ids;
            $authMapData['user_id'] = $user_id;
            $temp = [] ;
            foreach ($authMapData['auth_user_ids'] as $k=>$v){
                $temp[] = ['like','%'.$v.'%'];
            }
            $authMap = function($query) use ($authMapData,$temp){
                $query->where('complaint.create_user_id',array('in',$authMapData['auth_user_ids']))
                    ->whereor('step.user_id', $temp,'or');
            };
        }

        if ($map['complaint.create_user_id']) {
            $map['contract.create_user_id'] = $map['complaint.create_user_id'];
            unset($map['complaint.create_user_id']);
        }

        if ($request['order_type'] && $request['order_field']) {
            $order = trim($request['order_field']) . ' ' . trim($request['order_type']);
        } else {
            $order = 'complaint.update_time desc';
        }

        $list =db('crm_complaint')
            ->alias('complaint')
            ->join('__ADMIN_EXAMINE_FLOW__ flow','complaint.flow_id=flow.flow_id')
            ->join('__ADMIN_EXAMINE_STEP__ step','step.flow_id=flow.flow_id')
            ->where($map)->where($authMap)
//            ->where(function ($query)use ($request){
//                $query->where('flow.user_ids', array('like','%,'.$request['user_id'].',%'))
//                    ->whereor('flow.structure_ids', array('like','%,'.$request['structure_id'].',%'));
//            })
//            ->where(function ($query)use ($request){
//                $query->where('complaint.create_user_id', $request['user_id'])
//                    ->whereor('step.user_id', array('like','%,'.$request['user_id'].',%'));
//            })
            ->field('complaint.*')
            ->Distinct(true)
            ->limit(($request['page']-1)*$request['limit'], $request['limit'])
            ->order($order)
            ->select();

        $dataCount =db('crm_complaint')
            ->alias('complaint')
            ->join('__ADMIN_EXAMINE_FLOW__ flow','complaint.flow_id=flow.flow_id')
            ->join('__ADMIN_EXAMINE_STEP__ step','step.flow_id=flow.flow_id')
            ->where($map)->where($authMap)
//            ->where(function ($query)use ($request){
//                $query->where('flow.user_ids', array('like','%,'.$request['user_id'].',%'))
//                    ->whereor('flow.structure_ids', array('like','%,'.$request['structure_id'].',%'));
//            })
//            ->where(function ($query)use ($request){
//                $query->where('complaint.create_user_id', $request['user_id'])
//                    ->whereor('step.user_id', array('like','%,'.$request['user_id'].',%'));
//            })
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