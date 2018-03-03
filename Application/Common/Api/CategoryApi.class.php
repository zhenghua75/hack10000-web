<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

namespace Common\Api;
class CategoryApi {
    /**
     * 获取分类信息并缓存分类
     * @param  integer $id    分类ID
     * @param  string  $field 要获取的字段名
     * @return string         分类信息
     */
    public static function get_category($id, $field = null){
        static $list;

        /* 非法分类ID */
        if(empty($id) || !is_numeric($id)){
            return '';
        }

        /* 读取缓存数据 */
        if(empty($list)){
            $list = S('sys_category_list');
        }

        /* 获取分类名称 */
        if(!isset($list[$id])){
            $cate = M('Category')->find($id);
            if(!$cate || 1 != $cate['status']){ //不存在分类，或分类被禁用
                return '';
            }
            $list[$id] = $cate;
            S('sys_category_list', $list); //更新缓存
        }
        return is_null($field) ? $list[$id] : $list[$id][$field];
    }

    /* 根据ID获取分类标识 */
    public static function get_category_name($id){
        return get_category($id, 'name');
    }

    /* 根据ID获取分类名称 */
    public static function get_category_title($id){
        return get_category($id, 'title');
    }

    /**
     * 获取参数的所有父级分类
     * @param int $cid 分类id
     * @return array 参数分类和父类的信息集合
     * @author huajie <banhuajie@163.com>
     */
    public static function get_parent_category($cid){
        if(empty($cid)){
            return false;
        }
        $cates  =   M('Category')->where(array('status'=>1))->field('id,title,pid')->order('sort')->select();
        $child  =   get_category($cid);	//获取参数分类的信息
        $pid    =   $child['pid'];
        $temp   =   array();
        $res[]  =   $child;
        while(true){
            foreach ($cates as $key=>$cate){
                if($cate['id'] == $pid){
                    $pid = $cate['pid'];
                    array_unshift($res, $cate);	//将父分类插入到数组第一个元素前
                }
            }
            if($pid == 0){
                break;
            }
        }
        return $res;
    }

    /*
     *获取所有商品分类
     */
    public function getProductCatalogTree($id = 0, $field = true){
        /* 获取当前分类信息 */
        if($id){
            $info = M('product_catalog')->field($field)->where(array('id'=>$id))->find();
            $id   = $info['id'];
        }

        /* 获取所有分类 */
        $map  = array('status' => array('eq', 1));
        $list = M('product_catalog')->field($field)->where($map)->order('sort')->select();
        $list = list_to_tree($list, $pk = 'id', $pid = 'parent_id', $child = '_', $root = $id);

        /* 获取返回数据 */
        if(isset($info)){ //指定分类则返回当前分类极其子分类
            $info['_'] = $list;
        } else { //否则返回所有分类
            $info = $list;
        }

        return $info;
    }

    /*
     *获取三级分类的所属分类
     */
    public function getCatalogByID($id = 0){
        if($id){
            $lev3 = M('product_catalog')->where(array('id'=>$id))->find();
            $lev2 = M('product_catalog')->where(array('id'=>$lev3['parent_id']))->find();
            $lev1 = M('product_catalog')->where(array('id'=>$lev2['parent_id']))->find();
            $catalog=array();
            if(!$lev2){
                $catalog[0]=array('id'=>$lev3['id'],'name'=>$lev3['name']);
            }elseif(!$lev1){
                $catalog[0]=array('id'=>$lev2['id'],'name'=>$lev2['name']);
                $catalog[1]=array('id'=>$lev3['id'],'name'=>$lev3['name']);
                
            }else{
                $catalog[0]=array('id'=>$lev1['id'],'name'=>$lev1['name']);
                $catalog[1]=array('id'=>$lev2['id'],'name'=>$lev2['name']);
                $catalog[2]=array('id'=>$lev3['id'],'name'=>$lev3['name']);
            }
            return $catalog;
        }else{
            return 0;
        }
    }

    /*
     *获取三级分类对应的规格
     */
    public function getSpecByCatalogID($id = 0){
        if($id){
            $list = M('product_catalogspec')->where(array('cid'=>$id,'status'=>'1'))->select();
            return $list;
        }else{
            return 0;
        }
    }

    /*
     *获取所有三级分类
     */
    public function get3LevelCatalog(){
        $alllist = M('product_catalog')->where(array('status'=>'1'))->order('parent_id,sort')->select();
        $level1 = M('product_catalog')->where(array('parent_id'=>'0','status'=>'1'))->order('sort')->select();
        $level2=array();
        foreach ($level1 as $key => $value) {
            foreach ($alllist as $key1 => $value1) {
                if($value1['parent_id']==$value['id']){
                    array_push($level2, $value1);
                }
            }
        }
        $level3=array();
        foreach ($alllist as $key => $value) {
            foreach ($level2 as $key1 => $value1) {
                if($value['parent_id']==$value1['id']){
                    $level3[$value['id']]=$value['name'];
                }
            }
        }
        return $level3;
    }

    public function getParentID($cid){
        return M('product_catalog')->where(array('status'=>'1','id'=>$cid))->find();
    }

    /*
     *获取所有分类
     */
    public function getAllCatalog($cate=0){
        $is1level=false;
        if($cate>0){
            $leveltmp = $this->getParentID($cate);
            if($leveltmp['parent_id']==0){
                $is1level=true;
            }
        }
        
        $alllist = M('product_catalog')->where(array('status'=>'1'))->order('parent_id,sort')->select();
        $return=array();
        foreach ($alllist as $key => $value) {
            if($value['parent_id']==0){
                $tmp['id']=$value['id'];
                $tmp['name']=$value['name'];
                $tmp['subs']=array();
                array_push($return, $tmp);
            }
        }
        foreach ($return as $key => &$value) {
            if($is1level && $value['id']!=$cate){
                continue;
            }
            foreach ($alllist as $key1 => $value1) {
                if($value1['parent_id']==$value['id']){
                    $tmp['id']=$value1['id'];
                    $tmp['name']=$value1['name'];
                    $tmp['subs']=array();
                    array_push($value['subs'], $tmp);
                }
            }
        }
        foreach ($return as $key => &$value) {
            foreach ($value['subs'] as $key1 => $value1) {
                foreach ($alllist as $key2 => $value2) {
                    if($value2['parent_id']==$value1['id']){
                        $tmp['id']=$value2['id'];
                        $tmp['name']=$value2['name'];
                        array_push($value['subs'][$key1]['subs'], $tmp);
                    }
                }
            }
        }

        return $return;
    }

    /*
     *获取下一级所有分类
     */
    public function getNextLevelall($cate){
        return D('product_catalog')->where(array('status'=>'1','parent_id'=>$cate))->order('sort')->getField('id',true);
    }

    /*
     *获取所有分类列表，每一层级有一个下一级列表
     */
    public function getCatalogAllList(){
        $list=D('product_catalog')->where(array('status'=>'1'))->order('parent_id,sort')->getField('id,parent_id');
        $return=array();
        $return[0]=array();
        foreach ($list as $key => $value) {
            if($value==0){
                array_push($return[0], $key);
                $return[$key]=array();
                unset($list[$key]);
            }
        }
        foreach ($list as $key => $value) {
            if(in_array($value, $return[0])){
                array_push($return[$value], $key);
                $return[$key]=array();
                unset($list[$key]);
            }
        }
        foreach ($list as $key => $value) {
            array_push($return[$value], $key);
        }
        return $return;
    }
}