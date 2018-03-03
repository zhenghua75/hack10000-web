<?php

namespace Common\Api;
use Common\Model\ProductModel;
use Think\Model;

class OrderApi {

    public function insertCart($data,$type){
        $map['uid']=$data['uid'];
        if($type==2){
            $map['makeruid']=$data['makeruid'];
        }
        $map['product_id']=$data['product_id'];
        $map['group_id']=$data['group_id'];
        $map['type']=$data['type'];
        $cart=D('cart')->where($map)->find();
        if($cart){
            $res=D('cart')->where(array('id'=>$cart['id']))->setInc('quantity',$data['quantity']);
        }else{
            $data['created']=time();
            $res=D('cart')->add($data);
        }
        if($res){
            return array('status' => 1, 'info' => '加入购物车成功');
        }else{
            return array('status' => 0, 'info' => '加入购物车失败，请重试！');
        }
    }

    public function getStoreCart($uid){
        $map['uid']=$uid;
        $map['type']=1;
        $cart=D('cart')->where($map)->order('id')->select();
        $prodapi=new ProductApi;
        $cartlist=array();
        foreach ($cart as $key => $value) {
            $prod=$prodapi->getProductInfo($value['product_id'],1);
            foreach ($prod['specs'] as $key1 => &$value1) {
                if($value1['gid']==$value['group_id']){
                    $value1['selected']=true;
                    $value1['quantity']=intval($value['quantity']);
                    $value1['amount']=$value['quantity']*($value1['hackPrice']);
                    $value1['comment']=$value['comments'];
                    $value1['image']=str_replace('560x420', '96x72', $value1['image']);
                }else{
                    unset($prod['specs'][$key1]);
                }
            }
            $cartlist[$value['id']]=$prod;
        }
        return $cartlist;
    }

    public function getSettleStoreCart($uid){
        $map['uid']=$uid;
        $map['type']=1;
        $cart=D('cart')->where($map)->order('id')->select();
        $prodapi=new ProductApi;
        $cartlist=array();
        $cartlist['checkres']=array('status'=>1,'info'=>'');
        foreach ($cart as $key => $value) {
            $prod=$prodapi->getProductInfo($value['product_id'],1);
            foreach ($prod['specs'] as $key1 => &$value1) {
                if($value1['gid']==$value['group_id']){
                    if($value1['inventory']<$value['quantity']){
                        $cartlist['checkres']['status']=0;
                        $cartlist['checkres']['info']='商品：'.$prod['name'].'，库存不足';
                        return $cartlist;
                    }
                    $value1['selected']=true;
                    $value1['quantity']=intval($value['quantity']);
                    $value1['amount']=$value['quantity']*($value1['hackPrice']);
                    $value1['comment']=$value['comments'];
                    $value1['image']=str_replace('560x420', '96x72', $value1['image']);
                }else{
                    unset($prod['specs'][$key1]);
                }
            }
            $cartlist[$value['id']]=$prod;
        }
        return $cartlist;
    }

    public function updateAfterGetStoreCart($uid,$cartid){
        $map['uid']=$uid;
        $map['type']=1;
        $cart=D('cart')->where($map)->order('id')->select();
        foreach ($cart as $key => $value) {
            // if(array_key_exists($value['id'], $cartid)){
            //     if($value['quantity']!=$cartid[$value['id']]){
            //         D('cart')->where(array('uid'=>$uid,'id'=>$value['id']))->setField('quantity',$cartid[$value['id']]);
            //         $cart[$key]['quantity']=$cartid[$value['id']];
            //     }
            // }else{
            //     unset($cart[$key]);
            // }
            if(!array_key_exists($value['id'], $cartid)){
                unset($cart[$key]);
            }
        }
        $prodapi=new ProductApi;
        $cartlist=array();
        $total=array();
        $cartlist['checkres']=array('status'=>1,'info'=>'');
        foreach ($cart as $key => $value) {
            $prod=$prodapi->getProductInfo($value['product_id'],1);
            foreach ($prod['specs'] as $key1 => &$value1) {
                if($value1['gid']==$value['group_id']){
                    if($value1['inventory']<$value['quantity']){
                        $cartlist['checkres']['status']=0;
                        $cartlist['checkres']['info']='商品：'.$prod['name'].'，库存不足';
                        return $cartlist;
                    }
                    $value1['selected']=true;
                    $value1['quantity']=intval($value['quantity']);
                    $value1['amount']=$value['quantity']*($value1['hackPrice']);
                    $value1['comment']=$value['comments'];
                    $value1['image']=str_replace('560x420', '96x72', $value1['image']);
                    $total['quantity']=$total['quantity']+$value1['quantity'];
                    $total['amount']=$total['amount']+$value1['amount'];
                }else{
                    unset($prod['specs'][$key1]);
                }
            }
            $cartlist[$value['id']]=$prod;
        }
        $cartlist['total']=$total;
        return $cartlist;
    }

    public function updateAfterGetShopCart($uid,$cartid){
        $map['uid']=$uid;
        $map['type']=2;
        $cart=D('cart')->where($map)->order('id')->select();
        foreach ($cart as $key => $value) {
            // if(array_key_exists($value['id'], $cartid)){
            //     if($value['quantity']!=$cartid[$value['id']]){
            //         D('cart')->where(array('uid'=>$uid,'id'=>$value['id']))->setField('quantity',$cartid[$value['id']]);
            //         $cart[$key]['quantity']=$cartid[$value['id']];
            //     }
            // }else{
            //     unset($cart[$key]);
            // }
            if(!array_key_exists($value['id'], $cartid)){
                unset($cart[$key]);
            }
        }
        $prodapi=new ProductApi;
        $cartlist=array();
        $total=array();
        $cartlist['checkres']=array('status'=>1,'info'=>'');
        foreach ($cart as $key => $value) {
            $shop=D('maker_store')->where(array('uid'=>$value['makeruid']))->field('id,name')->find();
            $prod=$prodapi->getProductInfo($value['product_id'],2,$value['makeruid'],$shop['id']);
            foreach ($prod['specs'] as $key1 => &$value1) {
                if($value1['gid']==$value['group_id']){
                    if($value1['inventory']<$value['quantity']){
                        $cartlist['checkres']['status']=0;
                        $cartlist['checkres']['info']='店铺：'.$shop['name'].' 的 '.'商品：'.$prod['name'].'，库存不足';
                        return $cartlist;
                    }
                    $value1['selected']=true;
                    $value1['quantity']=intval($value['quantity']);
                    $value1['amount']=$value['quantity']*($value1['price']);
                    $value1['comment']=$value['comments'];
                    $value1['image']=str_replace('560x420', '96x72', $value1['image']);
                    $total['quantity']=$total['quantity']+$value1['quantity'];
                    $total['amount']=$total['amount']+$value1['amount'];
                }else{
                    unset($prod['specs'][$key1]);
                }
            }
            $prod['store']['coupon']=array();
            $mapcp['sourceid']=$prod['store']['id'];
            $mapcp['sourcetype']='shop';
            $mapcp['status']='1';
            $today=date('Y-m-d');
            $mapcp['_string']='\''.$today.'\' between begindate and enddate';
            $couponlist=D('coupon')->where($mapcp)->select();
            foreach ($couponlist as $key3 => $value3) {
                $coupon=D('coupondetail')->where(array('coupon_id'=>$value3['id'],'uid'=>$uid,'status'=>0))->select();
                if($coupon){
                    foreach ($coupon as $key4 => $value4) {
                        $tmpcoupon['id']=$value4['id'];
                        $tmpcoupon['name']=$value3['couponvalue'].'元优惠券';
                        array_push($prod['store']['coupon'], $tmpcoupon);
                    }
                }
            }
            $cartlist[$value['id']]=$prod;
        }
        $cartlist['total']=$total;
        return $cartlist;
    }

    public function getShopCart($uid){
        $map['uid']=$uid;
        $map['type']=2;
        $cart=D('cart')->where($map)->order('id')->select();
        $prodapi=new ProductApi;
        $cartlist=array();
        foreach ($cart as $key => $value) {
            $shop=D('maker_store')->where(array('uid'=>$value['makeruid']))->field('id,name')->find();
            $prod=$prodapi->getProductInfo($value['product_id'],2,$value['makeruid'],$shop['id']);
            foreach ($prod['specs'] as $key1 => &$value1) {
                if($value1['gid']==$value['group_id']){
                    $value1['selected']=true;
                    $value1['quantity']=intval($value['quantity']);
                    $value1['amount']=$value['quantity']*($value1['price']);
                    $value1['comment']=$value['comments'];
                    $value1['image']=str_replace('560x420', '96x72', $value1['image']);
                }else{
                    unset($prod['specs'][$key1]);
                }
            }
            $cartlist[$value['id']]=$prod;
        }
        return $cartlist;
    }

    public function getSettleShopCart($uid,$cartid,$shopcoupon,$cartcomment){
        $map['uid']=$uid;
        $map['type']=2;
        $cart=D('cart')->where($map)->order('id')->select();
        foreach ($cart as $key => $value) {
            if(!in_array($value['id'], $cartid)){
                unset($cart[$key]);
            }
        }
        $prodapi=new ProductApi;
        $cartlist=array();
        $cartlist['checkres']=array('status'=>1,'info'=>'');
        $shopmakeramount=array();
        foreach ($cart as $key => $value) {
            $tmpmakeramount=0;
            $shop=D('maker_store')->where(array('uid'=>$value['makeruid']))->field('id,name')->find();
            $prod=$prodapi->getProductInfo($value['product_id'],2,$value['makeruid'],$shop['id']);
            unset($prod['catalog_id']);
            unset($prod['catalog_name']);
            unset($prod['saleQuantity']);
            unset($prod['commentQuantity']);
            unset($prod['commentScore']);
            unset($prod['bookmarkQuantity']);
            unset($prod['bookmarked']);
            unset($prod['likeQunatity']);
            unset($prod['liked']);
            unset($prod['lastComment']);
            unset($prod['defaultdisplayprice']);
            unset($prod['detail']);
            $prod['cartid']=intval($value['id']);
            $prod['comment']=$cartcomment[$value['id']];
            $prod['quantity']=intval($value['quantity']);
            foreach ($prod['specs'] as $key1 => $value1) {
                if($value1['gid']==$value['group_id']){
                    if($value1['inventory']<$value['quantity']){
                        $cartlist['checkres']['status']=0;
                        $cartlist['checkres']['info']='店铺：'.$shop['name'].' 的 商品：'.$prod['name'].'，库存不足';
                        return $cartlist;
                    }
                    $selspecname='';
                    if($value1['gid']!=0){
                        foreach ($value1['spec'] as $key2 => $value2) {
                            $selspecname=$selspecname.$value2['name'].':'.$value2['value']['name'].' ';
                        }
                    }
                    $prod['group_id']=$value['group_id'];
                    $prod['specname']=$selspecname;
                    $prod['amount']=$value['quantity']*($value1['price']);
                    $tmpmakeramount=$value['quantity']*($value1['hackPrice']);
                    $prod['price']=$value1['price'];

                    if($value1['image']){
                        $prod['image']=str_replace('560x420', '96x72', $value1['image']);
                    }else{
                        $prod['image']=$prod['images'][0];
                    }
                }
            }
            unset($prod['specs']);
            unset($prod['images']);

            $cartlist['prodamount']=intval($cartlist['amount'])+intval($prod['amount']);
            $cartlist['amount']=intval($cartlist['amount'])+intval($prod['amount']);
            $cartlist['quantity']=intval($cartlist['quantity'])+intval($value['quantity']);

            if(array_key_exists($prod['store']['id'], $cartlist)){
                $shopmakeramount[$prod['store']['id']]['amount']=$shopmakeramount[$prod['store']['id']]['amount']+intval($tmpmakeramount);
                $cartlist[$prod['store']['id']]['amount']=$cartlist[$prod['store']['id']]['amount']+intval($prod['amount']);
                $cartlist[$prod['store']['id']]['products'][$value['id']]=$prod;
            }else{
                $shopmakeramount[$prod['store']['id']]['amount']=intval($tmpmakeramount);
                $cartlist[$prod['store']['id']]['id']=$prod['store']['id'];
                $cartlist[$prod['store']['id']]['name']=$prod['store']['name'];
                $cartlist[$prod['store']['id']]['guid']=$prod['store']['guid'];
                $cartlist[$prod['store']['id']]['amount']=intval($prod['amount']);

                $cartlist[$prod['store']['id']]['coupon']=array();
                if($shopcoupon){
                    if($shopcoupon[$prod['store']['id']]=='0'){
                        $coupon0=array('id'=>0,'name'=>'不使用优惠券','value'=>0, 'selected'=>true);
                    }else{
                        $coupon0=array('id'=>0,'name'=>'不使用优惠券','value'=>0, 'selected'=>false);
                    }
                }else{
                    $coupon0=array('id'=>0,'name'=>'不使用优惠券','value'=>0, 'selected'=>true);
                }

                $cartlist[$prod['store']['id']]['selcouponvalue']=0;
                array_push($cartlist[$prod['store']['id']]['coupon'], $coupon0);
                $cartlist[$prod['store']['id']]['products'][$value['id']]=$prod;
            }
        }
        foreach ($cartlist as $key => $value) {
            if(!in_array($key, array('checkres','prodamount','amount','quantity'))){
                $mapcp['sourceid']=$key;
                $mapcp['sourcetype']='shop';
                $mapcp['status']='1';
                $today=date('Y-m-d');
                $mapcp['_string']='\''.$today.'\' between begindate and enddate';
                $couponlist=D('coupon')->where($mapcp)->select();
                foreach ($couponlist as $key3 => $value3) {
                    $coupon=D('coupondetail')->where(array('coupon_id'=>$value3['id'],'uid'=>$uid,'status'=>0))->select();
                    if($coupon){
                        foreach ($coupon as $key4 => $value4) {
                            if(($value3['couponvalue']/100)<=($value['amount']-$shopmakeramount[$key]['amount'])){
                                $tmpcoupon['id']=$value4['id'];
                                $tmpcoupon['value']=$value3['couponvalue']/100;
                                $tmpcoupon['name']=$tmpcoupon['value'].'元优惠券';
                                if($shopcoupon && $shopcoupon[$key]==$tmpcoupon['id']){
                                    $tmpcoupon['selected']=true;
                                    $cartlist[$key]['selcouponvalue']=$tmpcoupon['value'];
                                    // foreach ($cartlist[$key]['coupon'] as $key5 => $value5) {
                                    //     if($value5['id']==='0'){
                                    //         $cartlist[$key]['coupon'][$key5]['selected']=false;
                                    //     }
                                    // }
                                }else{
                                    $tmpcoupon['selected']=false;
                                }
                                array_push($cartlist[$key]['coupon'], $tmpcoupon);
                            }
                        }
                    }
                }
            }
        }

        $sumcoupon=0;
        foreach ($cartlist as $key => &$value) {
            if(!in_array($key, array('checkres','prodamount','amount','quantity'))){
                $value['amount']=$value['amount']-$value['selcouponvalue'];
                $sumcoupon=$sumcoupon+$value['selcouponvalue'];
            }
        }
        $cartlist['amount']=$cartlist['amount']-$sumcoupon;
        return $cartlist;
    }

    public function storeCartCommand($command,$id){
        $map['id']=$id;
        $cart=D('cart')->where($map)->find();
        if($command=='minus'){
            $res=D('cart')->where($map)->setDec('quantity');
        }elseif($command=='add'){
            $prodapi=new ProductApi;
            $prod=$prodapi->getProductInfo($cart['product_id'],2);
            $flag=true;
            foreach ($prod['specs'] as $key1 => &$value1) {
                if(!$flag){
                    break;
                }
                if($value1['gid']==$cart['group_id']){
                    if($value1['inventory']<$cart['quantity']){
                        $flag=false;
                    }
                }
            }
            if(!$flag){
                return array('status'=>0,'info'=>'库存不足，您来晚了');
            }
            $res=D('cart')->where($map)->setInc('quantity');
        }elseif($command=='del'){
            $res=D('cart')->where($map)->delete();
        }
        if($res){
            if($command=='del'){
                return array('status'=>1, 'info'=>'成功');
            }else{
                $cart=D('cart')->where($map)->find();
                if($cart['quantity']==0){
                    D('cart')->where($map)->delete();
                    return array('status'=>1, 'amount'=>0, 'quantity'=>0, 'info'=>'成功');
                }else{
                    $prodapi=new ProductApi;
                    $price=$prodapi->getProductDetailPrice($cart['product_id'],$cart['group_id']);
                    $amount=$cart['quantity']*$price['hackprice'];
                    return array('status'=>1, 'amount'=>$amount, 'quantity'=>$cart['quantity'], 'info'=>'成功');
                }
            }
        }else{
            return array('status'=>0,'info'=>'失败');
        }
    }

    public function shopCartCommand($command,$id){
        $map['id']=$id;
        $cart=D('cart')->where($map)->find();
        if($command=='minus'){
            $res=D('cart')->where($map)->setDec('quantity');
        }elseif($command=='add'){
            $shopid=D('maker_store')->where(array('uid'=>$cart['makeruid']))->getField('id');
            $prodapi=new ProductApi;
            $prod=$prodapi->getProductInfo($cart['product_id'],2,$cart['makeruid'],$shopid);
            $flag=true;
            foreach ($prod['specs'] as $key1 => &$value1) {
                if(!$flag){
                    break;
                }
                if($value1['gid']==$cart['group_id']){
                    if($value1['inventory']<$cart['quantity']){
                        $flag=false;
                    }
                }
            }
            if(!$flag){
                return array('status'=>0,'info'=>'库存不足，您来晚了');
            }
            $res=D('cart')->where($map)->setInc('quantity');
        }elseif($command=='del'){
            $res=D('cart')->where($map)->delete();
        }
        if($res){
            if($command=='del'){
                return array('status'=>1, 'info'=>'成功');
            }else{
                $cart=D('cart')->where($map)->find();
                if($cart['quantity']==0){
                    D('cart')->where($map)->delete();
                    return array('status'=>1, 'amount'=>0, 'quantity'=>0, 'info'=>'成功');
                }else{
                    $prodapi=new ProductApi;
                    $price=$prodapi->getProductDetailPrice($cart['product_id'],$cart['group_id']);
                    $amount=$cart['quantity']*$price['price'];
                    return array('status'=>1, 'amount'=>$amount, 'quantity'=>$cart['quantity'], 'info'=>'成功');
                }
            }
        }else{
            return array('status'=>0,'info'=>'失败');
        }
    }

    public function checkStorePrice($cartid,$realamount){
        if(is_array($cartid)){
            $arrayid='';
            foreach ($cartid as $key => $value) {
                $arrayid=','.$arrayid.','.$value;
            }
            $arrayid=substr($arrayid, 1);
            $map['id']=array('in',$arrayid);
        }else{
            $map['id']=$cartid;
        }
        $cart=D('cart')->where($map)->order('id')->select();
        $prodapi=new ProductApi;
        $cartlist=array();
        $calamount=0;
        $return['hcoin']=array('status'=>0,'info'=>'商品价格已经变动，请重新确认');
        $return['invent']=array('status'=>0,'info'=>'库存不足，您来晚了');
        $flag=true;
        foreach ($cart as $key => $value) {
            if(!$flag){
                break;
            }
            $prod=$prodapi->getProductInfo($value['product_id'],1);
            foreach ($prod['specs'] as $key1 => &$value1) {
                if(!$flag){
                    break;
                }
                if($value1['gid']==$value['group_id']){
                    $tmp=$value['quantity']*($value1['hackPrice']);
                    $calamount=$calamount+$tmp;
                    if($value1['inventory']<$value['quantity']){
                        $flag=false;
                    }
                }
            }
        }
        if($flag){
            $return['invent']['status']=1;
            $return['invent']['info']='';
        }
        if($realamount==$calamount){
            $return['hcoin']['status']=1;
            $return['hcoin']['info']='';
        }

        return $return;
    }

    public function checkShopPrice($cartid,$realamount){
        if(is_array($cartid)){
            $arrayid='';
            foreach ($cartid as $key => $value) {
                $arrayid=','.$arrayid.','.$value;
            }
            $arrayid=substr($arrayid, 1);
            $map['id']=array('in',$arrayid);
        }else{
            $map['id']=$cartid;
        }
        $cart=D('cart')->where($map)->order('id')->select();
        $prodapi=new ProductApi;
        $cartlist=array();
        $calamount=0;
        $return['cash']=array('status'=>0,'info'=>'商品价格已经变动，请重新确认');
        $return['invent']=array('status'=>0,'info'=>'库存不足，您来晚了');
        $flag=true;
        foreach ($cart as $key => $value) {
            if(!$flag){
                break;
            }
            $shopid=D('maker_store')->where(array('uid'=>$value['makeruid']))->getField('id');
            $prod=$prodapi->getProductInfo($value['product_id'],2,$value['makeruid'],$shopid);
            foreach ($prod['specs'] as $key1 => &$value1) {
                if(!$flag){
                    break;
                }
                if($value1['gid']==$value['group_id']){
                    $tmp=$value['quantity']*($value1['price']);
                    $calamount=$calamount+$tmp;
                    if($value1['inventory']<$value['quantity']){
                        $flag=false;
                    }
                }
            }
        }
        if($flag){
            $return['invent']['status']=1;
            $return['invent']['info']='';
        }
        if($realamount==$calamount){
            $return['cash']['status']=1;
            $return['cash']['info']='';
        }

        return $return;
    }

    public function newStoreOrder($uid,$cartid,$comment,$source){
        $seq=D('orderseq')->add(array('value'=>'1'));
        $orderid=$seq.$uid;
        if(is_array($cartid)){
            $arrayid='';
            foreach ($cartid as $key => $value) {
                $arrayid=','.$arrayid.','.$value;
            }
            $arrayid=substr($arrayid, 1);
            $map['id']=array('in',$arrayid);
        }else{
            $map['id']=$cartid;
        }
        $cart=D('cart')->where($map)->order('id')->select();
        $prodapi=new ProductApi;
        $cartlist=array();
        $calamount=0;
        $calquantity=0;
        $supplierorderdata=array();
        $orderdetail=array();
        $inventupdate=array();
        $storeproduct=array();
        $storeproductspec=array();
        $index=0;
        foreach ($cart as $key => $value) {
            $prod=$prodapi->getProductInfo($value['product_id'],1);
            $tmphackPrice=0;
            $selspecname='';
            $selimage=0;
            foreach ($prod['specs'] as $key1 => &$value1) {
                if($value1['gid']==$value['group_id']){
                    $tmp=$value['quantity']*$value1['hackPrice'];
                    $calamount=$calamount+$tmp;
                    $tmphackPrice=$value1['hackPrice'];
                    if($value1['gid']!=0){
                        $selimage=D('product_spec')->where(array('group_id'=>$value1['gid']))->getField('linkimg');
                        foreach ($value1['spec'] as $key2 => $value2) {
                            $selspecname=$selspecname.$value2['name'].':'.$value2['value']['name'].' ';
                        }
                    }
                }
            }
            $calquantity=$calquantity+$value['quantity'];
            $supplierid=$prod['vendor']['id'];
            if(array_key_exists($supplierid, $supplierorderdata)){
                $tmpquantity=$supplierorderdata[$supplierid]['quantity']+$value['quantity'];
                $supplierorderdata[$supplierid]['quantity']=$tmpquantity;
                $tmpamount=$supplierorderdata[$supplierid]['amount']+($value['quantity']*$tmphackPrice*100);
                $supplierorderdata[$supplierid]['amount']=$tmpamount;
            }else{
                $supplierorderdata[$supplierid]['id']=$orderid;
                $supplierorderdata[$supplierid]['uid']=$supplierid;
                $supplierorderdata[$supplierid]['quantity']=$value['quantity'];
                $supplierorderdata[$supplierid]['amount']=$tmphackPrice*$value['quantity']*100;
                $supplierorderdata[$supplierid]['comments']='';
                $supplierorderdata[$supplierid]['created']=time();
                $supplierorderdata[$supplierid]['settletype']='hcoin';
                $supplierorderdata[$supplierid]['source']=1;
            }

            $tmporderdetail['orderid']=$orderid;
            $tmporderdetail['product_id']=$value['product_id'];
            $tmporderdetail['group_id']=$value['group_id'];
            $tmporderdetail['name']=$prod['name'];
            $tmporderdetail['catename']=$prod['catalog_name'];
            $tmporderdetail['groupname']=$selspecname;
            if($selimage==0 || $selimage==''){
                $tmporderdetail['linkimg']=D('product_picture')->where(array('product_id'=>$value['product_id']))->order('sort')->getField('linkimg');
            }else{
                $tmporderdetail['linkimg']=$selimage;
            }
            $tmporderdetail['price']=$tmphackPrice*100;
            $tmporderdetail['quantity']=$value['quantity'];
            $tmporderdetail['amount']=$value['quantity']*$tmphackPrice*100;
            $tmporderdetail['memberid']=0;
            $tmporderdetail['makerid']=$uid;
            $tmporderdetail['supplierid']=$supplierid;
            $tmporderdetail['comments']=$comment[$index];
            $tmporderdetail['created']=time();
            array_push($orderdetail, $tmporderdetail);
            $index=$index+1;

            $inventupdate[$value['group_id']]=array('product_id'=>$value['product_id'],'quantity'=>$value['quantity']);

            $isexist=false;
            foreach ($storeproduct as $keyspd => $valuespd) {
                if($valuespd['product_id']==$value['product_id']){
                    $isexist=true;
                }
            }
            if(!$isexist){
                $tmpstoreprod['uid']=$uid;
                $tmpstoreprod['product_id']=$value['product_id'];
                $tmpstoreprod['created']=time();
                $tmpstoreprod['status']=1;
                array_push($storeproduct, $tmpstoreprod);
            }

            $tmpstoreprodspec['product_id']=$value['product_id'];
            $tmpstoreprodspec['group_id']=$value['group_id'];
            $tmpstoreprodspec['inventory']=$value['quantity'];
            $tmpstoreprodspec['created']=time();
            array_push($storeproductspec, $tmpstoreprodspec);
        }

        $makerorderdata=array();
        $makerorderdata['id']=$supplierorderdata[$supplierid]['id'];
        $makerorderdata['uid']=$uid;
        $makerorderdata['quantity']=$calquantity;
        $makerorderdata['amount']=$calamount*100;
        $makerorderdata['orgamount']=$makerorderdata['amount'];
        $makerorderdata['comments']='';
        $makerorderdata['created']=time();
        $makerorderdata['settletype']='hcoin';
        $makerorderdata['source']=1;

        $orderstatus=array();
        $orderstatus['orderid']=$orderid;
        $orderstatus['status']=3;
        $orderstatus['uid']=$uid;
        $orderstatus['created']=time();

        $ordersettle=array();
        $ordersettle['orderid']=$orderid;
        $ordersettle['uid']=$uid;
        $ordersettle['quantity']=$calquantity;
        $ordersettle['amount']=$calamount*100;
        $ordersettle['settletype']='hcoin';
        $ordersettle['thirdchannel']=$source;
        $ordersettle['paid']=1;
        $ordersettle['comments']='';
        $ordersettle['created']=time();
        $ordersettle['paytime']=time();

        $hcoinrecord=array();
        $hcoinrecord['orderid']=$orderid;
        $hcoinrecord['uid']=$uid;
        $hcoinrecord['type']='CONS';
        if($calamount==0){
            $hcoinrecord['charge']=0;
        }else{
            $hcoinrecord['charge']=-($calamount*100);
        }
        $hcoinrecord['comments']='购买商品';
        $hcoinrecord['created']=time();

        $emptymodel=new Model();
        $emptymodel->startTrans();
        $flag=false;
        $debug='';
        $res1=$emptymodel->table(C('DB_PREFIX').'maker_order')->add($makerorderdata);
        //$debug=$debug.',1-'.$res1;
        if($res1){
            $res2=0;
            foreach ($supplierorderdata as $key => $value) {
                $res2=$emptymodel->table(C('DB_PREFIX').'supplier_order')->add($value);
                if(!$res2){
                    break;
                }
            }
            //$debug=$debug.',2-'.$res2;
            $res3=0;
            if($res2){
                foreach ($orderdetail as $key => $value) {
                    $res3=$emptymodel->table(C('DB_PREFIX').'orderdetail')->add($value);
                    if(!$res3){
                        break;
                    }
                }
            }
            //$debug=$debug.',3-'.$res3;
            $res4=0;
            if($res3){
                $res4=$emptymodel->table(C('DB_PREFIX').'order_status')->add($orderstatus);
            }
            //$debug=$debug.',4-'.$res4;
            $res5=0;
            if($res4){
                $res5=$emptymodel->table(C('DB_PREFIX').'order_settle')->add($ordersettle);
                $ordersettlelog['pid']=$res5;
                $ordersettlelog['orderid']=$ordersettle['orderid'];
                $ordersettlelog['uid']=$ordersettle['uid'];
                $ordersettlelog['quantity']=$ordersettle['quantity'];
                $ordersettlelog['amount']=$ordersettle['amount'];
                $ordersettlelog['settletype']=$ordersettle['settletype'];
                $ordersettlelog['thirdchannel']=$ordersettle['thirdchannel'];
                $ordersettlelog['paid']=$ordersettle['paid'];
                $ordersettlelog['comments']=$ordersettle['comments'];
                $ordersettlelog['created']=$ordersettle['created'];
                $ordersettlelog['paytime']=$ordersettle['paytime'];
                $emptymodel->table(C('DB_PREFIX').'order_settle_log')->add($ordersettlelog);
            }
            //$debug=$debug.',5-'.$res5;
            $res6=0;
            if($res5){
                $res6=$emptymodel->table(C('DB_PREFIX').'hcoin_record')->add($hcoinrecord);
            }
            //$debug=$debug.',6-'.$res6;
            $res7=0;
            if($res6){
                if($hcoinrecord['charge']==0){
                    $res7=1;
                }else{
                    $res7=$emptymodel->table(C('DB_PREFIX').'member')->where(array('uid'=>$uid))->setInc('hcoin',$hcoinrecord['charge']);
                }
                
            }
            //$debug=$debug.',7-'.$res7;
            $res8=0;
            if($res7){
                $res8=$emptymodel->table(C('DB_PREFIX').'cart')->where($map)->delete();
            }
            //$debug=$debug.',8-'.$res8;
            $res9=0;
            if($res8){
                foreach ($inventupdate as $key => $value) {
                    if($key==0 || $key=='0'){
                        $res9=$emptymodel->table(C('DB_PREFIX').'product')->where(array('product_id'=>$value['product_id']))->setDec('inventory',$value['quantity']);
                    }else{
                        $res9=$emptymodel->table(C('DB_PREFIX').'product_spec')->where(array('group_id'=>$key))->setDec('inventory',$value['quantity']);
                    }
                    if(!$res9){
                        break;
                    }
                }
            }
            //$debug=$debug.',9-'.$res9;
            $res10=array();
            if($res9){
                foreach ($storeproduct as $key => $value) {
                    $batch=$emptymodel->table(C('DB_PREFIX').'maker_store_product')->where(array('uid'=>$value['uid'],'product_id'=>$value['product_id'],'status'=>'1'))->find();
                    if($batch){
                        $res10[$value['product_id']]=$batch['id'];
                    }else{
                        $res10[$value['product_id']]=$emptymodel->table(C('DB_PREFIX').'maker_store_product')->add($value);
                    }
                    if(!$res10){
                        break;
                    }
                }
            }
            //$debug=$debug.',10-'.$res10;
            $res11=0;
            if($res10){
                foreach ($storeproductspec as $key => $value) {
                    if(array_key_exists($value['product_id'], $res10)){
                        $batch=$emptymodel->table(C('DB_PREFIX').'maker_store_productspec')->where(array('id'=>$res10[$value['product_id']],'group_id'=>$value['group_id']))->find();
                        if($batch){
                            $res11=$emptymodel->table(C('DB_PREFIX').'maker_store_productspec')->where(array('id'=>$res10[$value['product_id']],'group_id'=>$value['group_id']))->setInc('inventory',$value['inventory']);
                        }else{
                            $datatp['id']=$res10[$value['product_id']];
                            $datatp['group_id']=$value['group_id'];
                            $datatp['inventory']=$value['inventory'];
                            $datatp['created']=$value['created'];
                            $res11=$emptymodel->table(C('DB_PREFIX').'maker_store_productspec')->add($datatp);
                        }
                    }
                    if(!$res11){
                        break;
                    }
                }
            }
            //$debug=$debug.',11-'.$res11;
            if($res11){
                $emptymodel->commit();
                $flag=true;
            }
        }

        if(!$flag){
            $emptymodel->rollback();
        }

        return $flag;
        //return array('status'=>false,'info'=>$debug);
    }

    public function newShopOrder($uid,$cartid,$comment,$source,$shipping,$shopcoupon,$selshipaddr){
        $arrayid='';
        foreach ($cartid as $key => $value) {
            $arrayid=$arrayid.','.$key;
        }
        $arrayid=substr($arrayid, 1);
        $map['id']=array('in',$arrayid);
        $cart=D('cart')->where($map)->order('id')->select();

        $cartlist=array();
        $calamount=0;
        $calquantity=0;
        $supplierorderdata=array();
        $makerorderdata=array();
        $orderdetail=array();
        $inventupdate=array();
        $storeproduct=array();
        $storeproductspec=array();
        $index=0;

        $seq=D('orderseq')->add(array('value'=>'1'));
        $orderid=$seq.$uid;
        $prodapi=new ProductApi;
        foreach ($cart as $key => $value) {
            $shopid=D('maker_store')->where(array('uid'=>$value['makeruid']))->getField('id');
            $prod=$prodapi->getProductInfo($value['product_id'],2,$value['makeruid'],$shopid);
            $tmpprice=0;
            $selspecname='';
            $selimage=0;
            foreach ($prod['specs'] as $key1 => &$value1) {
                if($value1['gid']==$value['group_id']){
                    $tmp=$value['quantity']*$value1['price'];
                    $calamount=$calamount+$tmp;
                    $tmpprice=$value1['price'];
                    if($value1['gid']!=0){
                        $selimage=D('product_spec')->where(array('group_id'=>$value1['gid']))->getField('linkimg');
                        foreach ($value1['spec'] as $key2 => $value2) {
                            $selspecname=$selspecname.$value2['name'].':'.$value2['value']['name'].' ';
                        }
                    }
                }
            }
            $calquantity=$calquantity+$value['quantity'];
            $supplierid=$prod['vendor']['id'];
            if(array_key_exists($supplierid, $supplierorderdata)){
                $tmpquantity=$supplierorderdata[$supplierid]['quantity']+$value['quantity'];
                $supplierorderdata[$supplierid]['quantity']=$tmpquantity;
                $tmpamount=$supplierorderdata[$supplierid]['amount']+($value['quantity']*$tmpprice*100);
                $supplierorderdata[$supplierid]['amount']=$tmpamount;
            }else{
                $supplierorderdata[$supplierid]['id']=$orderid;
                $supplierorderdata[$supplierid]['uid']=$supplierid;
                $supplierorderdata[$supplierid]['quantity']=$value['quantity'];
                $supplierorderdata[$supplierid]['amount']=$tmpprice*$value['quantity']*100;
                $supplierorderdata[$supplierid]['comments']='';
                $supplierorderdata[$supplierid]['created']=time();
                $supplierorderdata[$supplierid]['settletype']='cash';
                $supplierorderdata[$supplierid]['source']=2;
            }

            if(array_key_exists($value['makeruid'], $makerorderdata)){
                $tmpquantity=$makerorderdata[$value['makeruid']]['quantity']+$value['quantity'];
                $makerorderdata[$value['makeruid']]['quantity']=$tmpquantity;
                $tmpamount=$makerorderdata[$value['makeruid']]['amount']+($value['quantity']*$tmpprice*100);
                $makerorderdata[$value['makeruid']]['amount']=$tmpamount;
            }else{
                $makerorderdata[$value['makeruid']]['id']=$orderid;
                $makerorderdata[$value['makeruid']]['uid']=$value['makeruid'];
                $makerorderdata[$value['makeruid']]['quantity']=$value['quantity'];
                $makerorderdata[$value['makeruid']]['amount']=$value['quantity']*$tmpprice*100;
                $makerorderdata[$value['makeruid']]['comments']='';
                $makerorderdata[$value['makeruid']]['created']=time();
                $makerorderdata[$value['makeruid']]['settletype']='cash';
                $makerorderdata[$value['makeruid']]['source']=2;
                $makerorderdata[$value['makeruid']]['cpid']=$shopcoupon[$shopid];
                if($shopcoupon[$shopid]=='0'){
                    $couponvalue=0;
                }else{
                    $coupontplid=D('coupondetail')->where(array('id'=>$shopcoupon[$shopid]))->getField('coupon_id');
                    $couponvalue=D('coupon')->where(array('id'=>$coupontplid))->getField('couponvalue');
                }
                $makerorderdata[$value['makeruid']]['cpvalue']=$couponvalue;
            }

            $tmporderdetail['orderid']=$orderid;
            $tmporderdetail['product_id']=$value['product_id'];
            $tmporderdetail['group_id']=$value['group_id'];
            $tmporderdetail['name']=$prod['name'];
            $tmporderdetail['catename']=$prod['catalog_name'];
            $tmporderdetail['groupname']=$selspecname;
            if($selimage==0 || $selimage==''){
                $tmporderdetail['linkimg']=D('product_picture')->where(array('product_id'=>$value['product_id']))->order('sort')->getField('linkimg');
            }else{
                $tmporderdetail['linkimg']=$selimage;
            }
            $tmporderdetail['price']=$tmpprice*100;
            $tmporderdetail['quantity']=$value['quantity'];
            $tmporderdetail['amount']=$value['quantity']*$tmpprice*100;
            $tmporderdetail['memberid']=$uid;
            $tmporderdetail['makerid']=$value['makeruid'];
            $tmporderdetail['supplierid']=$supplierid;
            $tmporderdetail['comments']=$comment[$index];
            $shiptplname=D('shippingtpl')->where(array('id'=>$cartid[$value['id']]))->getfield('name');
            $tmporderdetail['shipname']=$shiptplname;
            $tmporderdetail['created']=time();
            array_push($orderdetail, $tmporderdetail);
            $index=$index+1;

            $shopprodid=D('maker_store_product')->where(array('uid'=>$value['makeruid'],'product_id'=>$value['product_id']))->getField('id');
            $inventupdate[$value['group_id']]=array('shopprodid'=>$shopprodid,'quantity'=>$value['quantity']);

            // $tmpstoreprod['uid']=$uid;
            // $tmpstoreprod['product_id']=$value['product_id'];
            // $tmpstoreprod['created']=time();
            // $tmpstoreprod['status']=1;
            // array_push($storeproduct, $tmpstoreprod);

            // $tmpstoreprodspec['product_id']=$value['product_id'];
            // $tmpstoreprodspec['group_id']=$value['group_id'];
            // $tmpstoreprodspec['inventory']=$value['quantity'];
            // $tmpstoreprodspec['created']=time();
            // array_push($storeproductspec, $tmpstoreprodspec);
        }
        foreach ($makerorderdata as $key => $value) {
            $makerorderdata[$key]['orgamount']=$value['amount'];
            $makerorderdata[$key]['amount']=$makerorderdata[$key]['orgamount']-$value['cpvalue'];
        }
        
        $supsumshipping=array();
        $ordershipping=array();
        foreach ($shipping as $key => $value) {
            $supid=D('shippingtpl')->where(array('id'=>$key))->getField('uid');
            $tmpordershipping['id']=$orderid;
            $tmpordershipping['uid']=$supid;
            $tmpordershipping['shiptplid']=$key;
            $tmpordershipping['amount']=$value['shipping']*100;
            array_push($ordershipping, $tmpordershipping);
            if(array_key_exists($supid, $supsumshipping)){
                $supsumshipping[$supid]['shipping']=intval($supsumshipping[$supid]['shipping'])+intval($value['shipping']*100);
            }else{
                $supsumshipping[$supid]['shipping']=$value['shipping']*100;
            }
        }

        $sumshipping=0;
        foreach ($supplierorderdata as $key => $value) {
            $supplierorderdata[$key]['orgamount']=$value['amount'];
            $supplierorderdata[$key]['shipping']=$supsumshipping[$value['uid']]['shipping'];
            $supplierorderdata[$key]['amount']=$supplierorderdata[$key]['orgamount']+$supplierorderdata[$key]['shipping'];
            $sumshipping=intval($sumshipping)+intval($supsumshipping[$value['uid']]['shipping']);
        }

        $memaddress=D('member_shippingaddr')->where(array('id'=>$selshipaddr))->find();
        $orderaddress['orderid']=$orderid;
        $orderaddress['chinacity']=$memaddress['provincename'].$memaddress['cityname'].$memaddress['districtname'];
        $orderaddress['detailaddr']=$memaddress['detailaddr'];
        $orderaddress['receivename']=$memaddress['receivename'];
        $orderaddress['linkphone']=$memaddress['linkphone'];
        $orderaddress['status']=1;

        $sumcoupon=0;
        foreach ($makerorderdata as $key => $value) {
            $sumcoupon=intval($sumcoupon)+intval($value['cpvalue']);
        }
        $memberorderdata=array();
        $memberorderdata['id']=$supplierorderdata[$supplierid]['id'];
        $memberorderdata['uid']=$uid;
        $memberorderdata['quantity']=$calquantity;
        $memberorderdata['amount']=$calamount*100-$sumcoupon+$sumshipping;
        $memberorderdata['comments']='';
        $memberorderdata['created']=time();
        $memberorderdata['settletype']='cash';

        $orderstatus=array();
        $orderstatus['orderid']=$orderid;
        $orderstatus['status']=0;
        $orderstatus['uid']=$uid;
        $orderstatus['created']=time();

        $ordersettle=array();
        $ordersettle['orderid']=$orderid;
        $ordersettle['uid']=$uid;
        $ordersettle['quantity']=$calquantity;
        $ordersettle['amount']=$calamount*100-$sumcoupon+$sumshipping;
        $ordersettle['settletype']='cash';
        $ordersettle['paid']=0;
        $ordersettle['thirdparty']='unionpay';
        $ordersettle['thirdchannel']=$source;
        $ordersettle['thirdseq']='';
        $ordersettle['thirdfrontpaid']=0;
        $ordersettle['thirdbackpaid']=0;
        $ordersettle['comments']='';
        $ordersettle['created']=time();
        $ordersettle['orgamount']=$calamount*100;
        $ordersettle['cpvalue']=$sumcoupon;
        $ordersettle['shipping']=$sumshipping;

        $emptymodel=new Model();
        $emptymodel->startTrans();
        $flag=false;
        $debug='';
        $res1=$emptymodel->table(C('DB_PREFIX').'member_order')->add($memberorderdata);
        //$debug=$debug.',1-'.$res1;
        if($res1){
            $res2=0;
            foreach ($makerorderdata as $key => $value) {
                $res2=$emptymodel->table(C('DB_PREFIX').'maker_order')->add($value);
                if(!$res2){
                    break;
                }
            }
            //$debug=$debug.',2-'.$res2;
            $res3=0;
            if($res2){
                foreach ($supplierorderdata as $key => $value) {
                    $res3=$emptymodel->table(C('DB_PREFIX').'supplier_order')->add($value);
                    if(!$res3){
                        break;
                    }
                }
            }
            //$debug=$debug.',3-'.$res3;
            $res4=0;
            if($res3){
                foreach ($orderdetail as $key => $value) {
                    $res4=$emptymodel->table(C('DB_PREFIX').'orderdetail')->add($value);
                    if(!$res4){
                        break;
                    }
                }
            }
            //$debug=$debug.',4-'.$res4;
            $res5=0;
            if($res4){
                $res5=$emptymodel->table(C('DB_PREFIX').'order_status')->add($orderstatus);
            }
            //$debug=$debug.',5-'.$res5;
            $res6=0;
            if($res5){
                $res6=$emptymodel->table(C('DB_PREFIX').'order_settle')->add($ordersettle);
                $ordersettlelog['pid']=$res6;
                $ordersettlelog['orderid']=$ordersettle['orderid'];
                $ordersettlelog['uid']=$ordersettle['uid'];
                $ordersettlelog['quantity']=$ordersettle['quantity'];
                $ordersettlelog['amount']=$ordersettle['amount'];
                $ordersettlelog['settletype']=$ordersettle['settletype'];
                $ordersettlelog['paid']=$ordersettle['paid'];
                $ordersettlelog['thirdparty']=$ordersettle['thirdparty'];
                $ordersettlelog['thirdchannel']=$ordersettle['thirdchannel'];
                $ordersettlelog['thirdseq']=$ordersettle['thirdseq'];
                $ordersettlelog['thirdfrontpaid']=$ordersettle['thirdfrontpaid'];
                $ordersettlelog['thirdbackpaid']=$ordersettle['thirdbackpaid'];
                $ordersettlelog['comments']=$ordersettle['comments'];
                $ordersettlelog['created']=$ordersettle['created'];
                $ordersettlelog['orgamount']=$ordersettle['orgamount'];
                $ordersettlelog['cpvalue']=$ordersettle['cpvalue'];
                $ordersettlelog['shipping']=$ordersettle['shipping'];
                $emptymodel->table(C('DB_PREFIX').'order_settle_log')->add($ordersettlelog);
            }
            //$debug=$debug.',6-'.$res6;
            $res7=0;
            if($res6){
                $res7=$emptymodel->table(C('DB_PREFIX').'cart')->where($map)->delete();
            }
            //$debug=$debug.',7-'.$res7;
            $res8=0;
            if($res7){
                foreach ($inventupdate as $key => $value) {
                    $res8=$emptymodel->table(C('DB_PREFIX').'maker_store_productspec')->where(array('id'=>$value['shopprodid'],'group_id'=>$key))->setDec('inventory',$value['quantity']);
                    if(!$res8){
                        break;
                    }
                }
            }
            //$debug=$debug.',8-'.$res8;
            $res9=0;
            if($res8){
                $res9=$emptymodel->table(C('DB_PREFIX').'order_shipaddress')->add($orderaddress);
            }
            //$debug=$debug.',9-'.$res9;
            $res10=0;
            if($res9){
                foreach ($ordershipping as $key => $value) {
                    $res10=$emptymodel->table(C('DB_PREFIX').'order_shipping')->add($value);
                    if(!$res10){
                        break;
                    }
                }
            }
            //$debug=$debug.',10-'.$res10;
            $res11=0;
            if($res10){
                foreach ($shopcoupon as $key => $value) {
                    if($value=='0'){
                        $res11=1;
                    }else{
                        $res11=$emptymodel->table(C('DB_PREFIX').'coupondetail')->where(array('id'=>$value))->save(array('status'=>1,'usetime'=>time()));
                    }
                    if(!$res11){
                        break;
                    }
                }
            }
            //$debug=$debug.',11-'.$res11;
            if($res11){
                $emptymodel->commit();
                $flag=true;
            }
        }

        if(!$flag){
            $emptymodel->rollback();
        }

        return array('status'=>$flag,'orderid'=>$orderid,'amount'=>$ordersettle['amount'], 'info'=>$debug);
    }

    public function getMyOrder($uid,$item){
        $map['uid']=$uid;
        $memorder=D('member_order')->where($map)->getField('id',true);
        if(empty($memorder)){
            $map['source']=1;
        }else{
            $strorderid=implode(',',$memorder);
            $map['_string']='((source=1 and uid='.$uid.') OR (source=2 and id in('.$strorderid.')))';
            unset($map['uid']);
        }
        $order=D('maker_order')->where($map)->order('id desc')->select();
        $orderlist=array();
        $prodapi=new ProductApi;
        foreach ($order as $key => $value) {
            $status=D('order_status')->where(array('orderid'=>$value['id']))->find();
            $ordersettle=D('order_settle')->where(array('orderid'=>$value['id']))->find();
            if($item!='-1'){
                if($item!='99' && $status['status']!=$item){
                    continue;
                }
            }
            if($status['isdel']==1){
                continue;
            }
            $orderlist[$value['id']]['id']=$value['id'];
            $orderlist[$value['id']]['created']=date('Y-m-d',$value['created']);
            $orderlist[$value['id']]['quantity']=intval($value['quantity']);
            $orderlist[$value['id']]['amount']=$ordersettle['amount']/100;
            $orderlist[$value['id']]['payamount']=$ordersettle['amount'];
            $orderlist[$value['id']]['comment']=$value['comments'];
            $orderlist[$value['id']]['settle']=$value['settletype'];
            $orderlist[$value['id']]['status']=intval($status['status']);
            $orderlist[$value['id']]['after']=intval($status['after']);
            $orderlist[$value['id']]['source']=intval($value['source']);
            switch ($orderlist[$value['id']]['status']) {
                case '0':
                    $orderlist[$value['id']]['statusname']='待付款';
                    break;
                case '1':
                    $orderlist[$value['id']]['statusname']='待发货';
                    break;
                case '2':
                    $orderlist[$value['id']]['statusname']='待收货';
                    break;
                case '3':
                    $orderlist[$value['id']]['statusname']='交易完成';
                    break;
                case '4':
                    $orderlist[$value['id']]['statusname']='交易关闭';
                    break;
                case '98':
                    $orderlist[$value['id']]['statusname']='退款/售后';
                    switch ($orderlist[$value['id']]['after']) {
                        case '0':
                            $orderlist[$value['id']]['aftername']='';
                            break;
                        case '10':
                            $orderlist[$value['id']]['aftername']='申请退款';
                            break;
                        case '11':
                            $orderlist[$value['id']]['aftername']='已退款';
                            break;
                        case '12':
                            $orderlist[$value['id']]['aftername']='拒绝退款';
                            break;
                        case '13':
                            $orderlist[$value['id']]['aftername']='退款失败';
                            break;
                        case '20':
                            $orderlist[$value['id']]['aftername']='申请退货';
                            break;
                        case '21':
                            $orderlist[$value['id']]['aftername']='已退货';
                            break;
                        case '22':
                            $orderlist[$value['id']]['aftername']='拒绝退货';
                            break;
                        case '23':
                            $orderlist[$value['id']]['aftername']='退款失败';
                            break;
                    }
                    break;
            }
            if($value['source']=='2'){
                $shiporders=D('order_shipping')->where(array('id'=>$value['id']))->field('shiptplid,shiporderid')->select();
                $orderlist[$value['id']]['orderships']=array();
                foreach ($shiporders as $keyshod => $valueshod) {
                    $code=D('shippingtpl')->where(array('id'=>$valueshod['shiptplid']))->getField('shipcorpcode');
                    if(stripos($valueshod['shiporderid'], ',')){
                        $shiporderid=explode(',', $valueshod['shiporderid']);
                    }else{
                        $shiporderid=array();
                        array_push($shiporderid, $valueshod['shiporderid']);
                    }
                    $tmpship['id']=$shiporderid;
                    $tmpship['name']=C('SHIPPINGTYPE')[$code];
                    $tmpship['code']=$code;
                    array_push($orderlist[$value['id']]['orderships'], $tmpship);
                }
            }
        }
        foreach ($orderlist as $key => $value) {
            $orderdet=D('orderdetail')->where(array('orderid'=>$key))->order('id')->select();
            $orderlist[$key]['products']=array();
            foreach ($orderdet as $key1 => $value1) {
                $map=array();
                $map['orderid']=$value['id'];
                $map['product_id']=$value1['product_id'];
                $map['uid']=$uid;
                $commentcnt=D('product_comment')->where($map)->count();
                if($item=='99' && ($value['status']!='3' || $commentcnt)){
                    continue;
                }
                $prod['id']=$value1['product_id'];
                $prod['orderdetailid']=$value1['id'];
                $prod['image']=get_picture_path($value1['linkimg'],'product','96x72');
                $prod['name']=$value1['name'];
                $prod['catename']=$value1['catename'];
                $prod['groupname']=$value1['groupname'];
                $prod['price']=intval($value1['price']/100);
                $prod['defaultdisplayprice']=$prod['price'];
                $prod['quantity']=intval($value1['quantity']);
                $prod['amount']=$value1['amount']/100;
                $shop=D('maker_store')->where(array('uid'=>$value1['makerid']))->field('id,name')->find();
                $prod['store']['id']=$shop['id'];
                $prod['store']['name']=$shop['name'];
                if($commentcnt){
                    $prod['commented']=true;
                }else{
                    $prod['commented']=false;
                }
                array_push($orderlist[$key]['products'], $prod);
            }
        }
        foreach ($orderlist as $key => $value) {
            if(empty($value['products'])){
                unset($orderlist[$key]);
            }
        }
        return $orderlist;
    }

    public function getStoreOrder($uid,$item){
        $map['uid']=$uid;
        $map['source']='2';
        $order=D('maker_order')->where($map)->order('id desc')->select();
        $orderlist=array();
        $prodapi=new ProductApi;
        foreach ($order as $key => $value) {
            $status=D('order_status')->where(array('orderid'=>$value['id']))->find();
            if($item!='-1'){
                if($item!='99' && $status['status']!=$item){
                    continue;
                }
            }
            $orderlist[$value['id']]['id']=$value['id'];
            $orderlist[$value['id']]['created']=date('Y-m-d',$value['created']);
            $orderlist[$value['id']]['quantity']=intval($value['quantity']);
            $orderlist[$value['id']]['amount']=$value['amount']/100;
            $orderlist[$value['id']]['comment']=$value['comments'];
            $orderlist[$value['id']]['settle']=$value['settletype'];
            $orderlist[$value['id']]['status']=intval($status['status']);
            $orderlist[$value['id']]['after']=intval($status['after']);
            $orderlist[$value['id']]['commented']=false;
            $orderlist[$value['id']]['source']=$value['source'];
            switch ($orderlist[$value['id']]['status']) {
                case '0':
                    $orderlist[$value['id']]['statusname']='待付款';
                    break;
                case '1':
                    $orderlist[$value['id']]['statusname']='待发货';
                    break;
                case '2':
                    $orderlist[$value['id']]['statusname']='待收货';
                    break;
                case '3':
                    $orderlist[$value['id']]['statusname']='交易完成';
                    break;
                case '4':
                    $orderlist[$value['id']]['statusname']='交易关闭';
                    break;
                case '98':
                    $orderlist[$value['id']]['statusname']='退款/售后';
                    switch ($orderlist[$value['id']]['after']) {
                        case '0':
                            $orderlist[$value['id']]['aftername']='';
                            break;
                        case '10':
                            $orderlist[$value['id']]['aftername']='申请退款';
                            break;
                        case '11':
                            $orderlist[$value['id']]['aftername']='已退款';
                            break;
                        case '12':
                            $orderlist[$value['id']]['aftername']='拒绝退款';
                            break;
                        case '13':
                            $orderlist[$value['id']]['aftername']='退款失败';
                            break;
                        case '20':
                            $orderlist[$value['id']]['aftername']='申请退货';
                            break;
                        case '21':
                            $orderlist[$value['id']]['aftername']='已退货';
                            break;
                        case '22':
                            $orderlist[$value['id']]['aftername']='拒绝退货';
                            break;
                        case '23':
                            $orderlist[$value['id']]['aftername']='退款失败';
                            break;
                    }
                    break;
            }
            $memberuid=D('member_order')->where(array('id'=>$value['id']))->getField('uid');
            $orderlist[$value['id']]['nickname']=get_nickname($memberuid);
            if($orderlist[$value['id']]['nickname']==''){
                $orderlist[$value['id']]['nickname']='匿名';
            }
        }
        foreach ($orderlist as $key => $value) {
            $orderdet=D('orderdetail')->where(array('orderid'=>$key))->order('id')->select();
            $orderlist[$key]['products']=array();
            foreach ($orderdet as $key1 => $value1) {
                $map=array();
                $map['orderid']=$value['id'];
                $map['product_id']=$value1['product_id'];
                $map['uid']=$uid;
                $commentcnt=D('product_comment')->where($map)->count();
                if($item=='99' && ($value['status']!='3' || $commentcnt)){
                    continue;
                }
                $prod['id']=$value1['product_id'];
                $prod['image']=get_picture_path($value1['linkimg'],'product','96x72');
                $prod['name']=$value1['name'];
                $prod['catename']=$value1['catename'];
                $prod['groupname']=$value1['groupname'];
                $prod['price']=intval($value1['price']/100);
                $prod['defaultdisplayprice']=$prod['price'];
                $prod['quantity']=intval($value1['quantity']);
                $prod['amount']=$value1['amount']/100;
                $shop=D('maker_store')->where(array('uid'=>$value1['makerid']))->field('id,name')->find();
                $prod['store']['id']=$shop['id'];
                $prod['store']['name']=$shop['name'];
                if($commentcnt){
                    $prod['commented']=true;
                }else{
                    $prod['commented']=false;
                }
                array_push($orderlist[$key]['products'], $prod);
            }
        }
        foreach ($orderlist as $key => $value) {
            if(empty($value['products'])){
                unset($orderlist[$key]);
            }
        }
        return $orderlist;
    }

    public function getSupplierOrder($uid,$item,$curpage=0,$listRows=0){
        $map['uid']=$uid;
        $order=D('supplier_order')->where($map)->order('id desc')->select();
        $orderlist=array();
        $prodapi=new ProductApi;
        foreach ($order as $key => $value) {
            $status=D('order_status')->where(array('orderid'=>$value['id']))->find();
            if($item!='-1'){
                if($item!='99' && $status['status']!=$item){
                    continue;
                }
            }
            $orderlist[$value['id']]['id']=$value['id'];
            $orderlist[$value['id']]['created']=date('Y-m-d',$value['created']);
            $orderlist[$value['id']]['quantity']=intval($value['quantity']);
            $orderlist[$value['id']]['amount']=$value['amount']/100;
            $orderlist[$value['id']]['payamount']=$value['amount'];
            $orderlist[$value['id']]['comment']=$value['comments'];
            $orderlist[$value['id']]['settle']=$value['settletype'];
            $orderlist[$value['id']]['status']=intval($status['status']);
            $orderlist[$value['id']]['after']=intval($status['after']);
            $orderlist[$value['id']]['source']=intval($value['source']);
            switch ($orderlist[$value['id']]['status']) {
                case '0':
                    $orderlist[$value['id']]['statusname']='待付款';
                    break;
                case '1':
                    $orderlist[$value['id']]['statusname']='待发货';
                    break;
                case '2':
                    $orderlist[$value['id']]['statusname']='待收货';
                    break;
                case '3':
                    $orderlist[$value['id']]['statusname']='交易完成';
                    break;
                case '4':
                    $orderlist[$value['id']]['statusname']='交易关闭';
                    break;
                case '98':
                    $orderlist[$value['id']]['statusname']='退款/售后';
                    switch ($orderlist[$value['id']]['after']) {
                        case '0':
                            $orderlist[$value['id']]['aftername']='';
                            break;
                        case '10':
                            $orderlist[$value['id']]['aftername']='申请退款';
                            break;
                        case '11':
                            $orderlist[$value['id']]['aftername']='已退款';
                            break;
                        case '12':
                            $orderlist[$value['id']]['aftername']='拒绝退款';
                            break;
                        case '13':
                            $orderlist[$value['id']]['aftername']='退款失败';
                            break;
                        case '20':
                            $orderlist[$value['id']]['aftername']='申请退货';
                            break;
                        case '21':
                            $orderlist[$value['id']]['aftername']='已退货';
                            break;
                        case '22':
                            $orderlist[$value['id']]['aftername']='拒绝退货';
                            break;
                        case '23':
                            $orderlist[$value['id']]['aftername']='退款失败';
                            break;
                    }
                    break;
            }

            $payuid=D('orderdetail')->where(array('orderid'=>$value['id'],'supplierid'=>$uid))->field('memberid,makerid')->find();
            if($value['source']=='1'){
                $queryuid=$payuid['makerid'];
            }else{
                $queryuid=$payuid['memberid'];
            }
            if($queryuid>0){
                $orderlist[$value['id']]['nickname']=get_nickname($queryuid);
                if($orderlist[$value['id']]['nickname']==''){
                    $orderlist[$value['id']]['nickname']='匿名';
                }
            }else{
                $orderlist[$value['id']]['nickname']='';
            }
        }
        foreach ($orderlist as $key => $value) {
            $orderdet=D('orderdetail')->where(array('orderid'=>$key,'supplierid'=>$uid))->order('id')->select();
            $orderlist[$key]['products']=array();
            foreach ($orderdet as $key1 => $value1) {
                $map=array();
                $map['orderid']=$value['id'];
                $map['product_id']=$value1['product_id'];
                $map['uid']=$uid;
                $commentcnt=D('product_comment')->where($map)->count();
                if($item=='99' && ($value['status']!='3' || $commentcnt)){
                    continue;
                }
                $prod['id']=$value1['product_id'];
                $prod['image']=get_picture_path($value1['linkimg'],'product','96x72');
                $prod['name']=$value1['name'];
                $prod['catename']=$value1['catename'];
                $prod['groupname']=$value1['groupname'];
                $prod['price']=intval($value1['price']/100);
                $prod['defaultdisplayprice']=$prod['price'];
                $prod['quantity']=intval($value1['quantity']);
                $prod['amount']=$value1['amount']/100;
                $shop=D('maker_store')->where(array('uid'=>$value1['makerid']))->field('id,name')->find();
                $prod['store']['id']=$shop['id'];
                $prod['store']['name']=$shop['name'];
                if($commentcnt){
                    $prod['commented']=true;
                }else{
                    $prod['commented']=false;
                }
                array_push($orderlist[$key]['products'], $prod);
            }
        }
        foreach ($orderlist as $key => $value) {
            if(empty($value['products'])){
                unset($orderlist[$key]);
            }
        }
        $totalcount=count($orderlist);
        $rowindex=0;
        if($curpage==0){
            $curpage=1;
        }
        foreach ($orderlist as $key => $value) {
            if($curpage>0 && $listRows>0){
                $minkey=$curpage*$listRows-$listRows;
                $maxkey=$curpage*$listRows-1;
            }
            if($rowindex<$minkey || $rowindex>$maxkey){
                unset($orderlist[$key]);
            }
            $rowindex=$rowindex+1;
        }
        $orderlist['totalcount']=$totalcount;
        return $orderlist;
    }

    public function getMyOrderSum($uid){
        $map['uid']=$uid;
        $memorder=D('member_order')->where($map)->getField('id',true);
        if(empty($memorder)){
            $map['source']=1;
        }else{
            $strorderid=implode(',',$memorder);
            $map['_string']='((source=1 and uid='.$uid.') OR (source=2 and id in('.$strorderid.')))';
            unset($map['uid']);
        }
        $order=D('maker_order')->where($map)->distinct(true)->field('id')->select();
        $orders=array(
            "paying"=>0,
            "shipping"=>0,
            "receiving"=>0,
            "finish"=>0,
            "commenting"=>0,
            "servicing"=>0,
            "todayamount"=>0,
            "todayordercnt"=>0,
            );
        $today=date('Y-m-d');
        foreach ($order as $key => $value) {
            $status=D('order_status')->where(array('orderid'=>$value['id']))->field('status,after,isdel')->find();
            if($status['isdel']=='1' || $status['status']=='4'){
                continue;
            }
            switch ($status['status']) {
                case '0':
                    $orders['paying']=intval($orders['paying'])+1;
                    break;
                case '1':
                    $orders['shipping']=intval($orders['shipping'])+1;
                    break;
                case '2':
                    $orders['receiving']=intval($orders['receiving'])+1;
                    break;
                case '3':
                    $orders['finish']=intval($orders['finish'])+1;
                    $mapcom['orderid']=$value['id'];
                    $mapcom['uid']=$uid;
                    $commentcnt=D('product_comment')->where($mapcom)->count();
                    if($commentcnt==0){
                        $orders['commenting']=intval($orders['commenting'])+1;
                    }
                    break;
                case '98':
                    $orders['servicing']=intval($orders['receiving'])+1;
                    break;
            }
        }
        return $orders;
    }

    public function getMakerOrderSum($uid){
        $map['uid']=$uid;
        $map['source']=2;
        $order=D('maker_order')->where($map)->order('id desc')->select();
        $orders=array(
            "paying"=>0,
            "shipping"=>0,
            "receiving"=>0,
            "finish"=>0,
            "commenting"=>0,
            "servicing"=>0,
            "undo"=>0,
            "todayamount"=>0,
            "todayordercnt"=>0,
            );
        $today=date('Y-m-d');
        foreach ($order as $key => $value) {
            $status=D('order_status')->where(array('orderid'=>$value['id']))->field('status,after,isdel')->find();
            if($status['isdel']=='1' || $status['status']=='4'){
                continue;
            }
            switch ($status['status']) {
                case '0':
                    $orders['paying']=intval($orders['paying'])+1;
                    break;
                case '1':
                    $orders['shipping']=intval($orders['shipping'])+1;
                    break;
                case '2':
                    $orders['receiving']=intval($orders['receiving'])+1;
                    break;
                case '3':
                    $orders['finish']=intval($orders['finish'])+1;
                    $mapcom['orderid']=$value['id'];
                    $mapcom['uid']=$uid;
                    $commentcnt=D('product_comment')->where($mapcom)->count();
                    if($commentcnt==0){
                        $orders['commenting']=intval($orders['commenting'])+1;
                    }
                    break;
                case '98':
                    $orders['servicing']=intval($orders['servicing'])+1;
                    break;
            }
            if(date('Y-m-d',$value['created'])==$today){
                $orders['todayamount']=intval($orders['todayamount'])+$value['amount'];
                $orders['todayordercnt']=intval($orders['todayordercnt'])+1;
            }
        }
        $orders['todayamount']=intval($orders['todayamount']/100);
        return $orders;
    }

    public function shopOrderFrontPay($data){
        $map['orderid']=$data['orderid'];
        $map['settletype']='cash';
        $order=D('order_settle')->where($map)->find();
        if($order){
            $dataord=array();
            if($order['thdfrontpaid']=='0'){
                if($data['thdfrontpaid']){
                    $dataord['thirdfrontpaid']=1;
                }else{
                    $dataord['thirdfrontpaid']=0;
                }
            }
            if($order['thirdseq']==''){
                $dataord['thirdseq']=$data['thdseq'];
            }
            if($order['thirdtime']==''){
                $dataord['thirdtime']=$data['time'];
            }
            if($order['comments']==''){
                $dataord['comments']=$data['thdmsg'];
            }

            if(count($dataord)>0){
                D('order_settle')->where(array('id'=>$order['id']))->save($dataord);

                $datalog['pid']=$order['id'];
                $datalog['orderid']=$order['orderid'];
                $datalog['uid']=$order['uid'];
                $datalog['quantity']=$order['quantity'];
                $datalog['amount']=$order['amount'];
                $datalog['settletype']=$order['settletype'];
                $datalog['paid']=$order['paid'];
                $datalog['thirdparty']=$order['thirdparty'];
                $datalog['thirdchannel']=$order['thirdchannel'];
                $datalog['thirdseq']=$dataord['thirdseq'];
                $datalog['thirdfrontpaid']=$dataord['thirdfrontpaid'];
                $datalog['thirdbackpaid']=$order['thirdbackpaid'];
                $dataord['thirdtime']=$dataord['thirdtime'];
                $dataord['tn']=$order['tn'];
                $datalog['comments']=$dataord['comments'];
                $datalog['created']=$order['created'];
                $datalog['paytime']=$order['paytime'];
                $datalog['logtime']=time();
                $datalog['orgamount']=$order['orgamount'];
                $datalog['cpvalue']=$order['cpvalue'];
                $datalog['shipping']=$order['shipping'];
                D('order_settle_log')->add($datalog);
            }
        }
    }

    public function shopOrderPay($data){
        $map['orderid']=$data['orderid'];
        $map['settletype']='cash';
        $order=D('order_settle')->where($map)->find();
        if($order){
            if($data['thdpaid']){
                $dataord['paid']=1;

            }else{
                $dataord['paid']=0;
            }
            $dataord['thirdseq']=$data['thdseq'];
            $dataord['thirdfrontpaid']=$data['thdpaid'];
            $dataord['thirdbackpaid']=$data['thdpaid'];
            $dataord['thirdtime']=$data['time'];
            $dataord['paytime']=time();
            $dataord['comments']=$data['thdmsg'];
            D('order_settle')->where(array('id'=>$order['id']))->save($dataord);
            D('order_status')->where(array('orderid'=>$order['orderid']))->setField('status',1);

            $datalog['pid']=$order['id'];
            $datalog['orderid']=$order['orderid'];
            $datalog['uid']=$order['uid'];
            $datalog['quantity']=$order['quantity'];
            $datalog['amount']=$order['amount'];
            $datalog['settletype']=$order['settletype'];
            $datalog['paid']=$dataord['paid'];
            $datalog['thirdparty']=$order['thirdparty'];
            $datalog['thirdchannel']=$dataord['thirdchannel'];
            $datalog['thirdseq']=$dataord['thirdseq'];
            $datalog['thirdfrontpaid']=$dataord['thirdfrontpaid'];
            $datalog['thirdbackpaid']=$dataord['thirdbackpaid'];
            $dataord['thirdtime']=$dataord['thirdtime'];
            $dataord['tn']=$order['tn'];
            $datalog['comments']=$dataord['comments'];
            $datalog['created']=$order['created'];
            $datalog['paytime']=$order['paytime'];
            $datalog['logtime']=time();
            $datalog['orgamount']=$order['orgamount'];
            $datalog['cpvalue']=$order['cpvalue'];
            $datalog['shipping']=$order['shipping'];
            D('order_settle_log')->add($datalog);
        }else{
            if($data['thdpaid']){
                $datalog['paid']=1;

            }else{
                $datalog['paid']=0;
            }
            $datalog['pid']=0;
            $datalog['orderid']=$data['orderid'];
            $datalog['uid']=0;
            $datalog['quantity']=0;
            $datalog['amount']=$data['amount'];
            $datalog['settletype']='cash';
            $datalog['thirdparty']=$data['thdparty'];
            $datalog['thirdseq']=$data['thdseq'];
            $datalog['thirdfrontpaid']=$data['thdpaid'];
            $datalog['thirdbackpaid']=$data['thdpaid'];
            $dataord['tn']=$data['tn'];
            $datalog['comments']=$data['thdmsg'];
            $dataord['thirdtime']=$data['time'];
            $dataord['paytime']=time();
            $datalog['logtime']=time();
            D('order_settle_log')->add($datalog);
        }
    }

    public function shopOrderRequest($orderid,$source){
        $map['orderid']=$orderid;
        $map['settletype']='cash';
        $order=D('order_settle')->where($map)->find();
        if($order){
            D('order_settle')->where(array('id'=>$order['id']))->setField('thirdchannel',$source);

            $datalog['pid']=$order['id'];
            $datalog['orderid']=$order['orderid'];
            $datalog['uid']=$order['uid'];
            $datalog['quantity']=$order['quantity'];
            $datalog['amount']=$order['amount'];
            $datalog['settletype']=$order['settletype'];
            $datalog['paid']=$order['paid'];
            $datalog['thirdparty']=$order['thirdparty'];
            $datalog['thirdchannel']=$source;
            $datalog['thirdseq']=$order['thirdseq'];
            $datalog['thirdfrontpaid']=$order['thirdfrontpaid'];
            $datalog['thirdbackpaid']=$order['thirdbackpaid'];
            $datalog['thirdtime']=$order['thirdtime'];
            $datalog['tn']=$order['tn'];
            $datalog['comments']=$order['comments'];
            $datalog['created']=$order['created'];
            $datalog['paytime']=$order['paytime'];
            $datalog['logtime']=time();
            $datalog['orgamount']=$order['orgamount'];
            $datalog['cpvalue']=$order['cpvalue'];
            $datalog['shipping']=$order['shipping'];
            D('order_settle_log')->add($datalog);
            return array('status'=>true,'info'=>'','amount'=>$order['amount']);
        }else{
            return array('status'=>false,'info'=>'未找到订单');
        }
    }

    public function shopOrderUpdateTN($orderid,$tn){
        $map['orderid']=$orderid;
        $map['settletype']='cash';
        $order=D('order_settle')->where($map)->find();
        if($order){
            D('order_settle')->where(array('id'=>$order['id']))->setField('tn',$tn);

            $datalog['pid']=$order['id'];
            $datalog['orderid']=$order['orderid'];
            $datalog['uid']=$order['uid'];
            $datalog['quantity']=$order['quantity'];
            $datalog['amount']=$order['amount'];
            $datalog['settletype']=$order['settletype'];
            $datalog['paid']=$order['paid'];
            $datalog['thirdparty']=$order['thirdparty'];
            $datalog['thirdchannel']=$order['thirdchannel'];
            $datalog['thirdseq']=$order['thirdseq'];
            $datalog['thirdfrontpaid']=$order['thirdfrontpaid'];
            $datalog['thirdbackpaid']=$order['thirdbackpaid'];
            $datalog['thirdtime']=$order['thirdtime'];
            $datalog['tn']=$tn;
            $datalog['comments']=$order['comments'];
            $datalog['created']=$order['created'];
            $datalog['paytime']=$order['paytime'];
            $datalog['logtime']=time();
            $datalog['orgamount']=$order['orgamount'];
            $datalog['cpvalue']=$order['cpvalue'];
            $datalog['shipping']=$order['shipping'];
            D('order_settle_log')->add($datalog);
            return array('status'=>true,'info'=>'');
        }else{
            return array('status'=>false,'info'=>'未找到订单');
        }
    }

    public function getOrderSettle($orderid){
        return D('order_settle')->where(array('orderid'=>$orderid,'thirdparty'=>'unionpay'))->find();
    }

    public function getShopCartTotalQuantity($uid,$productid,$makeruid,$groupid){
        $map['uid']=$uid;
        $map['product_id']=$productid;
        $map['type']=2;
        $map['makeruid']=$makeruid;
        $map['group_id']=$groupid;
        return D('cart')->where($map)->getField('quantity');
    }

    public function getStoreCartTotalQuantity($uid,$productid,$groupid){
        $map['uid']=$uid;
        $map['product_id']=$productid;
        $map['type']=1;
        $map['group_id']=$groupid;
        return D('cart')->where($map)->getField('quantity');
    }

    public function UserOrderDel($orderid,$uid){
        return D('order_status')->where(array('orderid'=>$orderid))->setField(array('isdel'=>1,'uid'=>$uid));
    }

    public function UserOrderCancel($orderid,$uid){
        $res=D('order_status')->where(array('orderid'=>$orderid))->setField(array('status'=>4,'uid'=>$uid));
        if($res){
            $orderdetail=D('orderdetail')->where(array('orderid'=>$orderid))->select();
            foreach ($orderdetail as $key => $value) {
                $storeprodid=D('maker_store_product')->where(array('uid'=>$value['makerid'],'product_id'=>$value['product_id']))->getField('id');
                D('maker_store_productspec')->where(array('id'=>$storeprodid,'group_id'=>$value['group_id']))->setInc('inventory',$value['quantity']);
            }
            $makerorder=D('maker_order')->where(array('orderid'=>$orderid))->select();
            foreach ($makerorder as $key => $value) {
                if($value['cpid']!='0'){
                    D('coupondetail')->where(array('id'=>$value['cpid']))->setField('status',0);
                }
            }
            return true;
        }
    }

    public function UserOrderAfterUpdate($orderid,$uid,$after){
        $orgstatus=D('order_status')->where(array('orderid'=>$orderid))->getField('status');
        return D('order_status')->where(array('orderid'=>$orderid))->setField(array('status'=>98,'after'=>$after,'uid'=>$uid,'orgstatus'=>$orgstatus));
    }

    public function UserOrderConfirm($orderid,$uid){
        return D('order_status')->where(array('orderid'=>$orderid))->setField(array('status'=>3,'uid'=>$uid));
    }
}