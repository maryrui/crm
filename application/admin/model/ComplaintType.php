<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/8/28
 * Time: 14:37
 */

namespace app\admin\model;


class ComplaintType
{
    /**
     * [getDataList 获取列表]
     * @return    [array]
     */
    public function getDataList($type=''){
        $cat = new \com\Category('admin_complaint_type', array('id', 'pid', 'depart', 'type'));
        $data = $cat->getList('', 0, 'id');
        // 若type为tree，则返回树状结构
        if ($type == 'tree') {
            $tree = new \com\Tree();
            $data = $tree->list_to_tree($data, 'id', 'pid', 'child', 0, true, array(''));
        }
        return $data;
    }




    public function createData($data){
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

    public function getData($type){
        return db('admin_complaint_type')->where(['type'=>$type])->find();
    }
}