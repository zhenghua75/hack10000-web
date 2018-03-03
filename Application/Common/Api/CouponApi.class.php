<?php

namespace Common\Api;
use User\Api\UserApi;
use Common\Api\ShopApi;

class CouponApi {
    public function getShopCouponList($shopid){
        $map['sourcetype']='shop';
        $map['sourceid']=$shopid;
        $map['status']=1;
        $list=D('coupon')->where($map)->field('id,couponvalue,begindate,enddate,tplid,status')->select();
        $shopapi=new ShopApi;
        $shop=$shopapi->getShopByID($shopid);
        foreach ($list as $key => &$value) {
            $value['total']=D('coupondetail')->where(array('coupon_id'=>$value['id']))->count();
            $value['unget']=D('coupondetail')->where(array('coupon_id'=>$value['id'],'uid'=>0))->count();
            $value['geted']=D('coupondetail')->where(array('coupon_id'=>$value['id'],'uid'=>array('neq',0)))->count();
            $value['shopname']=$shop['name'];
            $value['logopath']=$shop['logopath'];
            $value['couponvalue']=$value['couponvalue']/100;
        }
        return $list;
    }

    public function newShopCoupon($data,$cnt){
        $newcoupon=D('coupon')->add($data);
        if($newcoupon){
            $coupondetail=array();
            for($i=0;$i<$cnt;$i++){
                array_push($coupondetail, array('coupon_id'=>$newcoupon, 'status'=>'0'));
            }
            D('coupondetail')->addAll($coupondetail);
            return true;
        }else{
            return false;
        }
    }

    public function getShopCouponinfo($id){
        $info=D('coupon')->where(array('id'=>$id,'sourcetype'=>'shop'))->field('id,sourceid as shopid,couponvalue,begindate,enddate,tplid,status')->find();
        $shopapi=new ShopApi;
        $shop=$shopapi->getShopByID($info['shopid']);
        $info['total']=D('coupondetail')->where(array('coupon_id'=>$id))->count();
        $info['unget']=D('coupondetail')->where(array('coupon_id'=>$id,'uid'=>0))->count();
        $info['geted']=D('coupondetail')->where(array('coupon_id'=>$id,'uid'=>array('neq',0)))->count();
        $info['shopname']=$shop['name'];
        $info['logopath']=$shop['logopath'];
        $info['couponvalue']=$info['couponvalue']/100;
        return $info;
    }

    public function sendShopCouponToUser($couponid,$selfans){
        $cupdetail=D('coupondetail')->where(array('coupon_id'=>$couponid,'uid'=>0))->order('id')->getField('id',true);
        $index=0;
        foreach ($selfans as $key => $value) {
            D('coupondetail')->where(array('id'=>$cupdetail[$index]))->setField(array('uid'=>$value,'gettime'=>time()));
            $index=$index+1;
        }
        return true;
    }

    public function getUserCouponList($uid){
        $list=D('coupondetail')->where(array('uid'=>$uid,'status'=>0))->field('id,coupon_id')->order('gettime desc')->select();
        $couponlist=array();
        foreach ($list as $key => $value) {
            $coupon['id']=$value['id'];
            $info=D('coupon')->where(array('id'=>$value['coupon_id'],'sourcetype'=>'shop'))->field('id,sourceid as shopid,couponvalue,begindate,enddate,tplid,status')->find();
            $coupon['couponvalue']=$info['couponvalue']/100;
            $coupon['begindate']=$info['begindate'];
            $coupon['enddate']=$info['enddate'];
            $coupon['tplid']=$info['tplid'];
            $shopapi=new ShopApi;
            $shop=$shopapi->getShopByID($info['shopid']);
            $coupon['shopname']=$shop['name'];
            $coupon['logopath']=$shop['logopath'];

            array_push($couponlist, $coupon);
        }

        return $couponlist;
    }
}