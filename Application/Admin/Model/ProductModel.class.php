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
class ProductModel extends Model{

    // protected $_validate = array(
    //     array('name', 'require', '标识不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
    //     array('name', '', '标识已经存在', self::VALUE_VALIDATE, 'unique', self::MODEL_BOTH),
    // );

    // protected $_auto = array(
    //     array('status', '1', self::MODEL_BOTH),
    // );

    public function getProductList($sel1id,$sel2id,$sel3id,$name){
        if($sel3id){
            $map['catalog_id']=$sel3id;
            if($name){
                $map['name']=array('like','%'.$name.'%');
            }
            $prodlist=$this->where($map)->select();
        }elseif($sel2id){
            $strlev3=D('product_catalog')->where(array('parent_id'=>$sel2id))->getField('id',true);
            $map['catalog_id']=array('in',$strlev3);
            if($name){
                $map['name']=array('like','%'.$name.'%');
            }
            $prodlist=$this->where($map)->select();
        }elseif($sel1id){
            $strlev2=D('product_catalog')->where(array('parent_id'=>$sel1id))->getField('id',true);
            $strlev3=D('product_catalog')->where(array('parent_id'=>array('in',$strlev2)))->getField('id',true);
            $map['catalog_id']=array('in',$strlev3);
            if($name){
                $map['name']=array('like','%'.$name.'%');
            }
            $prodlist=$this->where($map)->select();
        }else{
            if($name){
                $prodlist=$this->where(array('name'=>array('like','%'.$name.'%')))->select();
            }else{
                $prodlist=$this->select();
            }
        }
        return $prodlist;
    }

    public function getProductInfo($id){
        $prod=$this->where(array('product_id'=>$id))->find();
        $prodspec=D('product_spec')->where(array('product_id'=>$id,'status'=>'1'))->select();
        $info['product']=$prod;
        $info['prodspec']=$prodspec;
        return $info;
    }

    public function updateProductInfo($pricemode,$data){
        if($pricemode==0){
            $res=$this->where(array('product_id'=>$data['product_id']))->save($data);
        }else{
            $proddata=$data['product'];
            $res=$this->where(array('product_id'=>$proddata['product_id']))->save($proddata);
            $prodspec=D('product_spec');
            foreach($data['prodspec'] as $key => $value) { 
                $res+=$prodspec->where(array('group_id'=>$value['group_id']))->save($value);
            }
        }
        return $res;
    }
}
