<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Admin\Model;
use Think\Model;

/**
 * 分类模型
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
class ProductCatalogModel extends Model{

    protected $_validate = array(
        array('name', 'require', '标识不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
    );

    protected $_auto = array(
        array('status', '1', self::MODEL_BOTH),
    );


    /**
     * 获取分类详细信息
     * @param  milit   $id 分类ID或标识
     * @param  boolean $field 查询字段
     * @return array     分类信息
     * @author 麦当苗儿 <zuojiazi@vip.qq.com>
     */
    public function info($id, $field = true){
        /* 获取分类信息 */
        $map = array();
        if(is_numeric($id)){ //通过ID查询
            $map['id'] = $id;
        } else { //通过标识查询
            $map['name'] = $id;
        }
        return $this->field($field)->where($map)->find();
    }

    /**
     * 获取分类树，指定分类则返回指定分类极其子分类，不指定则返回所有分类树
     * @param  integer $id    分类ID
     * @param  boolean $field 查询字段
     * @return array          分类树
     * @author 麦当苗儿 <zuojiazi@vip.qq.com>
     */
    public function getTree($id = 0, $field = true){
        /* 获取当前分类信息 */
        if($id){
            $info = $this->info($id);
            $id   = $info['id'];
        }

        /* 获取所有分类 */
        $map  = array('status' => array('gt', -1));
        $list = $this->field($field)->where($map)->order('sort')->select();
        $list = list_to_tree($list, $pk = 'id', $pid = 'parent_id', $child = '_', $root = $id);

        /* 获取返回数据 */
        if(isset($info)){ //指定分类则返回当前分类极其子分类
            $info['_'] = $list;
        } else { //否则返回所有分类
            $info = $list;
        }

        return $info;
    }

    /**
     * 获取指定分类子分类ID
     * @param  string $cate 分类ID
     * @return string       id列表
     * @author 麦当苗儿 <zuojiazi@vip.qq.com>
     */
    public function getChildrenId($cate) {
        $field    = 'id,name,pid,title,link_id';
        $category = $this->getTree($cate, $field);
        $ids[]    = $cate;
        foreach ($category['_'] as $key => $value) {
            $ids[] = $value['id'];
        }
        return implode(',', $ids);
    }

    /**
     * 获取指定分类的同级分类
     * @param  integer $id    分类ID
     * @param  boolean $field 查询字段
     * @return array
     * @author 麦当苗儿 <zuojiazi@vip.qq.com>
     */
    public function getSameLevel($id, $field = true){
        $info = $this->info($id, 'pid');
        $map = array('pid' => $info['pid'], 'status' => 1);
        return $this->field($field)->where($map)->order('sort')->select();
    }

    /**
     * 获取指定分类的级别
     */
    public function getCatalogLevel($id){
        $info = $this->info($id, 'parent_id');
        if($info['parent_id']==0){
            return 1;
        }else{
            $info = $this->info($info['parent_id'], 'parent_id');
            if($info['parent_id']==0){
                return 2;
            }else{
                $info = $this->info($info['parent_id'], 'parent_id');
                if($info['parent_id']==0){
                    return 3;
                }
            }
        }
        return 0;
    }

    /**
     * 获取指定级别的所有分类
     */
    public function getCatalogByLevel($level){
        if($level==1){
            $map = array('parent_id'=>0, 'status' => 1);
            return $this->field('id,name')->where($map)->order('sort')->select();
        }elseif($level==2){
            $cateall = $this->field('id,name,parent_id')->where(array('status'=>1))->order('sort')->select();
            $map = array('parent_id'=>0, 'status' => 1);
            $cate1 = $this->field('id')->where($map)->order('sort')->select();
            $cate2=array();
            foreach ($cateall as $key => $value) {
                foreach ($cate1 as $key1 => $value1) {
                    if($value['parent_id']==$value1['id']){
                        $tmp['id']=$value['id'];
                        $tmp['name']=$value['name'];
                        array_push($cate2, $tmp);
                    }
                }
            }
            return $cate2;
        }elseif($level==3){
            $cateall = $this->field('id,name,parent_id')->where(array('status'=>1))->order('sort')->select();
            
            $map = array('parent_id'=>0, 'status' => 1);
            $cate1 = $this->field('id')->where($map)->order('sort')->select();
            $cate2=array();
            foreach ($cateall as $key => $value) {
                foreach ($cate1 as $key1 => $value1) {
                    if($value['parent_id']==$value1['id']){
                        $tmp['id']=$value['id'];
                        $tmp['name']=$value['name'];
                        array_push($cate2, $tmp);
                    }
                }
            }
            $cate3=array();
            foreach ($cateall as $key => $value) {
                foreach ($cate2 as $key1 => $value1) {
                    if($value['parent_id']==$value1['id']){
                        $tmp['id']=$value['id'];
                        $tmp['name']=$value['name'];
                        array_push($cate3, $tmp);
                    }
                }
            }
            return $cate3;
        }
        return 0;
    }

    /**
     * 更新分类信息
     * @return boolean 更新状态
     * @author 麦当苗儿 <zuojiazi@vip.qq.com>
     */
    public function update(){
        $data = $this->create();
        if(!$data){ //数据对象创建错误
            return false;
        }

        if($data['parent_id']==0){
            if(((strlen($data['name']) + mb_strlen($data['name'],'UTF8'))/2)>8){
                $this->error = '分类名称不能大于4个汉字！';
                return false;
            }
        }else{
            $parentlevel=$this->getCatalogLevel($data['parent_id']);
            if($parentlevel==1){
                if(((strlen($data['name']) + mb_strlen($data['name'],'UTF8'))/2)>8){
                    $this->error = '分类名称不能大于4个汉字！';
                    return false;
                }
            }elseif($parentlevel==2){
                if(((strlen($data['name']) + mb_strlen($data['name'],'UTF8'))/2)>12){
                    $this->error = '分类名称不能大于6个汉字！';
                    return false;
                }
            }else{
                if(((strlen($data['name']) + mb_strlen($data['name'],'UTF8'))/2)>12){
                    $this->error = '分类名称不能大于6个汉字！';
                    return false;
                }
            }
        }

        /* 添加或更新数据 */
        if(empty($data['id'])){
            $res = $this->add($data);
        }else{
            $res = $this->save($data);
        }

        //更新分类缓存
        S('sys_category_list', null);

        //记录行为
        action_log('update_productcatalog', 'productcatalog', $data['id'] ? $data['id'] : $res, UID);

        return $res;
    }

    /*
     *获取三级分类的所属分类
     */
    public function getCatalogByID($id = 0){
        if($id){
            $lev3 = $this->where(array('id'=>$id))->find();
            $lev2 = $this->where(array('id'=>$lev3['parent_id']))->find();
            $lev1 = $this->where(array('id'=>$lev2['parent_id']))->find();
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
    
    /**
     * 查询后解析扩展信息
     * @param  array $data 分类数据
     * @author 麦当苗儿 <zuojiazi@vip.qq.com>
     */
    protected function _after_find(&$data, $options){
        /* 分割模型 */
        if(!empty($data['model'])){
            $data['model'] = explode(',', $data['model']);
        }

        if(!empty($data['model_sub'])){
            $data['model_sub'] = explode(',', $data['model_sub']);
        }

        /* 分割文档类型 */
        if(!empty($data['type'])){
            $data['type'] = explode(',', $data['type']);
        }

        /* 分割模型 */
        if(!empty($data['reply_model'])){
            $data['reply_model'] = explode(',', $data['reply_model']);
        }

        /* 分割文档类型 */
        if(!empty($data['reply_type'])){
            $data['reply_type'] = explode(',', $data['reply_type']);
        }

        /* 还原扩展数据 */
        if(!empty($data['extend'])){
            $data['extend'] = json_decode($data['extend'], true);
        }
    }

}
