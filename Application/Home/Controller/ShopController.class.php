<?php

namespace Home\Controller;
use Common\Api\ShopApi;
use Common\Api\ProductApi;
use Common\Api\OrderApi;
use User\Api\UserApi;
use Common\Api\SystemApi;

/**
 * 创客店铺
 */
class ShopController extends HomeController {

    protected function _initialize(){
        parent::_initialize();
        if (!is_login()) {
            $source=I('source');
            if($source=='app'){
                $token=I('token');
                if($token=='' || $token=='0'){
                    $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>false,'error'=>'您还没有登陆')));
                }else{
                    $Member = D('Member');
                    if(!$Member->applogin($token)){ //登录用户
                        $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>false,'error'=>'您还没有登陆')));
                    }
                }
            }
        }
    }

	//创客店铺
    public function index($source='web',$token=0){
        $gi=I('gi');
    	if($gi=='0'){
            if($source=='app'){
                $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'参数错误')));
            }else{
                $this->error('参数错误',U('user/index'));
            }
    	}else{
            $uid=0;
            if($source=='app'){
                $auth=session('user_auth');
                if($auth['token']!=$token){
                    $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'身份验证错误')));
                }
                $uid=$auth['uid'];
            }else{
                $uid=is_login();
            }
            if(!$uid){
                if($source=='app'){
                    $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>false,'error'=>'您还没有登陆')));
                }
            }
	    	$shopapi=new ShopApi;
	    	$shop=$shopapi->getShopByGUID($gi,$uid);
	     	if($shop){
		        $cate=I('cate');
                $curpage=I('p');
		        $shopapi=new ShopApi;
		        $res=$shopapi->getMyShopProductList($shop['uid'],$cate);
		        if($source=='app'){
                    foreach ($res['prod'] as $key => &$value) {
                        $value['detail']=str_replace('org', '800x800', $value['detail']);
                        $size=getimagesize($value['detail']);
                        $value['detailsize']=array('width'=>$size[0],'height'=>$size[1]);
                    }
                    $list['shop']=$shop;
                    $list['products']=$res['prod'];
                    $sysapi=new SystemApi;
                    $sysapi->writeAccessLog('创客时空','app');
		            $this->ajaxReturn(array('success'=>ture,'body'=>$list));
		        }else{
                    $sysapi=new SystemApi;
                    $sysapi->writeAccessLog('创客时空');
		            $this->assign('catelist',$res['cate']);
		            $this->assign('prodlist',$res['prod']);
		            $this->assign('cateid',$cate);
			    	$this->assign('shop',$shop);
                    $this->meta_title = $shop['name'];
			        $this->assign('pagename','page-shopindex');
			        $this->display($shop['tplhtml']);
		        }
	     	}else{
                if($source=='app'){
                    $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'请关注店铺')));
                }else{
                    $this->error('您的店铺还没有创建，请先到店铺管理->店铺设置中设置店铺信息',U('maker/makerstore'));
                }
	     	}
	     }
    }

	//我的店铺
    public function myshop(){
    	$uid=is_login();
        if(!$uid){
            $this->error( '您还没有登陆',U('User/login'),false,5);
        }
    	$shopapi=new ShopApi;
    	$shop=$shopapi->getMyShop($uid);
     	if($shop){
	        $cate=I('cate');
	        $shopapi=new ShopApi;
	        $res=$shopapi->getMyShopProductList($uid,$cate);
	        if($source=='app'){
                foreach ($res['prod'] as $key => &$value) {
                    $value['detail']=str_replace('org', '800x800', $value['detail']);
                    $size=getimagesize($value['detail']);
                    $value['detailsize']=array('width'=>$size[0],'height'=>$size[1]);
                }
                $sysapi=new SystemApi;
                $sysapi->writeAccessLog('创客时空','app');
	            $this->ajaxReturn(array('success'=>ture,'body'=>$res));
	        }else{
                $sysapi=new SystemApi;
                $sysapi->writeAccessLog('创客时空');
	            $this->assign('catelist',$res['cate']);
	            $this->assign('prodlist',$res['prod']);
	            $this->assign('cateid',$cate);
		    	$this->assign('shop',$shop);
                $this->meta_title = $shop['name'];
		        $this->assign('pagename','page-shopindex');
		        $this->display($shop['tplhtml']);
	        }
     	}else{
     		$this->error('您的店铺还没有创建，请先到店铺管理->店铺设置中设置店铺信息',U('User/makerstore'));
     	}
    }

    //创客店铺通用商品详情
    public function product($source='web',$token=0,$id=0,$gi=0){
        if(!$gi){
            if($source=='app'){
                $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'参数错误')));
            }else{
                $this->error('参数错误');
            }
        }
        if(!$id){
            if($source=='app'){
                $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'商品不存在或已下步架')));
            }else{
                $this->error('商品不存在或已下步架',U('Shop/index?gi='.$gi));
            }
        }
        $uid=0;
        if($source=='app'){
            $auth=session('user_auth');
            if($auth['token']!=$token){
                $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'身份验证错误')));
            }
            $uid=$auth['uid'];
        }else{
            $uid=is_login();
        }
        if(!$uid){
            if($source=='app'){
                $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>false,'error'=>'您还没有登陆')));
            }
        }
    	$shopapi=new ShopApi;
    	$shop=$shopapi->getShopByGUID($gi);
        $prodapi=new ProductApi;
        $prodapi->updateProductView($id,$shop['uid']);
        $info=$prodapi->getProductInfo($id,2,$shop['uid'],$shop['id']);
        $userapi=new UserApi;
        $info['bookmarked']=$userapi->isCollect($id,2,$uid,'product',$shop['id']);
        $info['liked']=$userapi->isFavor($id,2,$uid,'product',$shop['id']);
        if($source=='app'){
            $info['detail']=str_replace('org', '800x800', $info['detail']);
            $size=getimagesize($info['detail']);
            $info['detailsize']=array('width'=>$size[0],'height'=>$size[1]);
            $info['lastComment']=$prodapi->getProductLastComments($info['id'],2,$shop['uid']);
            $sysapi=new SystemApi;
            $sysapi->writeAccessLog('商品详情','app');
            $this->ajaxReturn(array('success'=>true,'body'=>$info));
        }else{
            $specs=array();
            $dataprice=array();
            if(count($info['specs'])==1 && $info['specs'][0]['gid']==0){
                $dataprice['marketprice']=$info['specs'][0]['marketPrice'];
                $dataprice['price']=$info['specs'][0]['price'];
            }else{
                foreach ($info['specs'] as $key => $value) {
                    foreach ($value['spec'] as $key1 => $value1) {
                        if(!array_key_exists($value1['id'], $specs)){
                            $specs[$value1['id']]['name']=$value1['name'];
                            $specs[$value1['id']]['value']=array();
                            array_push($specs[$value1['id']]['value'], $value1['value']);
                        }else{
                            $isexist=false;
                            foreach ($specs[$value1['id']]['value'] as $key2 => $value2) {
                                if($value2['id']==$value1['value']['id']){
                                    $isexist=true;
                                    break;
                                }
                            }
                            if(!$isexist){
                                array_push($specs[$value1['id']]['value'], $value1['value']);
                            }
                        }
                    }
                }
            }
            $specgroup=$prodapi->getProductSpecGroup($id,$shop['uid']);
            $commentlist=$prodapi->getProductComments($info['id'],2,$shop['uid']);
            $sysapi=new SystemApi;
            $sysapi->writeAccessLog('商品详情');
            $this->assign('info',$info);
            $this->assign('specs',$specs);
            $this->assign('specgroup',$specgroup);
            $this->assign('dataprice',$dataprice);
            $this->assign('commentlist',$commentlist);
            $this->assign('shop',$shop);
            $this->assign('pagename','page-product');
            $this->display();
        }
    }

    public function gotocart($source='web',$token=0){
        $uid=0;
        if($source=='app'){
            $auth=session('user_auth');
            if(empty($auth) || $auth['token']!=$token){
                $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>false,'error'=>'您还没有登陆')));
            }
            $uid=$auth['uid'];
        }else{
            $uid=is_login();
        }
        if($uid==0){
            if($source=='app'){
                $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>false,'error'=>'您还没有登陆')));
            }else{
                $this->error( '您还没有登陆',U('User/login'),false,5);
            }
        }
        $prodid=I('product_id');
        $group_id=I('selgroupid');
        $hackprice=I('selprice');
        $quantity=I('selquantity');
        $token=I('token');
        $gi=I('gi');
        if(!$gi){
            if($source=='app'){
                $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'参数错误')));
            }else{
                $this->error('参数错误');
            }
        }
        if($source=='app'){
            $auth=session('user_auth');
            if($auth['token']!=$token){
                $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>false,'error'=>'您还没有登陆')));
            }
        }
        $shopapi=new ShopApi;
        $shop=$shopapi->getShopByGUID($gi);
        if(!$shop){
            if($source=='app'){
                $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'店铺不存在')));
            }else{
                $this->error('店铺不存在');
            }
        }
        $prodapi=new ProductApi;
        $specgroup=$prodapi->getProductSpecGroup($prodid,$shop['uid']);
        $inventorytmp=0;
        if($specgroup['sys_attrprice']){
            foreach ($specgroup['sys_attrprice'] as $key => $value) {
                if($value['group_id']==$group_id){
                    $inventorytmp=$value['inventory'];
                }
            }
        }else{
            $info=$prodapi->getProductInfo($prodid,2,$shop['uid'],$shop['id']);
            if($info['specs'][0]['gid']==0){
                $inventorytmp=$info['specs'][0]['inventory'];
            }
        }
        $orderapi=new OrderApi;
        $carttotal=$orderapi->getShopCartTotalQuantity($uid,$prodid,$shop['uid'],$group_id);
        if(intval($inventorytmp)<=intval($carttotal)){
            if($source=='app'){
                $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'库存不足，您来晚了！')));
            }else{
                $this->ajaxReturn(array('status' => 0, 'info' => '库存不足，您来晚了！'));
            }
        }else{
            $data['uid']=$uid;
            $data['product_id']=$prodid;
            $data['group_id']=$group_id;
            if($source=='app'){
                $data['quantity']=$quantity;
            }else{
                $data['quantity']=1;
            }
            $data['type']='2';
            $data['makeruid']=$shop['uid'];
            $res=$orderapi->insertCart($data,2);
            if($source=='app'){
                if($res['status']){
                    $this->ajaxReturn(array('success'=>true,'body'=>array('isLogined'=>true,'error'=>'加入购物车成功')));
                }else{
                    $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'加入购物车失败')));
                }
                
            }else{
                $this->ajaxReturn($res);
            }
        }
    }

    public function ordersettle($source='web',$token=0){
        $uid=0;
        if($source=='app'){
            $auth=session('user_auth');
            if(empty($auth) || $auth['token']!=$token){
                $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>false,'error'=>'您还没有登陆')));
            }
            $uid=$auth['uid'];
        }else{
            $uid=is_login();
        }
        if($uid==0){
            if($source=='app'){
                $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>false,'error'=>'您还没有登陆')));
            }else{
                $this->error( '您还没有登陆',U('User/login'),false,5);
            }
        }
        $cartid=I('cartlist');
        $shipmethod=I('shippingmethod');
        $selshipaddr=I('selshipaddr');
        $shopid=I('shopid');
        $coupon=I('coupon');
        $comments=I('comments');

        if($source=='app'){
            if(stripos($cartid,',')){
                $cartid=explode(',', $cartid);
                if($shipmethod){
                    $shipmethod=explode(',', $shipmethod);
                }
            }else{
                $cartid=array(0=>$cartid);
                if($shipmethod){
                    $shipmethod=array(0=>$shipmethod);
                }
            }
            if($shopid){
                if(stripos($shopid,',')){
                    $shopid=explode(',', $shopid);
                    $coupon=explode(',', $coupon);
                }else{
                    $shopid=array(0=>$shopid);
                    $coupon=array(0=>$coupon);
                }
            }
            if($comments){
                $comments=json_decode($comments,true);
            }
        }
        
        if(empty($cartid)){
            if($source=='app'){
                $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'没有需要结算的商品，请先选择需要需要结算的商品')));
            }else{
                $this->error('没有需要结算的商品，请先选择需要需要结算的商品',U('User/myshopcart'));
            }
        }else{
            if($shopid && $coupon){
                $shopcoupon=array();
                foreach ($shopid as $key => $value) {
                    $shopcoupon[$value]=$coupon[$key];
                }
            }

            $userapi=new UserApi;
            $addrlist=$userapi->getshippingaddress($uid);
            if(empty($addrlist)){
                if($source=='app'){
                    $this->ajaxReturn(array('success'=>true,'body'=>array('isLogined'=>true,'error'=>'请先添加您的收货地址','shipaddrempty'=>true)));
                }else{
                    $this->error('请先添加您的收货地址','/User/index?t=4');
                }
            }
            $cartcomment=array();
            if($source=='web'){
                foreach ($cartid as $key => $value) {
                    $cartcomment[$value]=$comments[$key];
                }
            }else{
                if(empty($comments)){
                    foreach ($cartid as $key => $value) {
                        $cartcomment[$value]='';
                    }
                }else{
                    $cartcomment=$comments;
                }
            }
            $orderapi=new OrderApi;
            // if($source=='app'){
            //     $list=$orderapi->updateAfterGetShopCart($uid,$cartid);
            // }else{
            $list=$orderapi->getSettleShopCart($uid,$cartid,$shopcoupon,$cartcomment);
            //}
            if(!$list['checkres']['status']){
                if($source=='app'){
                    $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>$list['checkres']['info'])));
                    return;
                }else{
                    $this->error($list['checkres']['info']);
                    return;
                }
            }else{
                unset($list['checkres']);
            }
            $prodamount=$list['prodamount'];
            $sumamount=intval($list['amount']);
            $sumquantity=$list['quantity'];
            unset($list['amount']);
            unset($list['quantity']);
            unset($list['prodamount']);
            if($shipmethod){
                $shipcart=array();
                foreach ($cartid as $key => $value) {
                    $shipcart[$value]=$shipmethod[$key];
                }
            }else{
                $shipcart=array();
                foreach ($list as $key => $value) {
                    foreach ($value['products'] as $key1 => $value1) {
                        $shipcart[$value1['cartid']]=$value1['shippingtpl'][0]['id'];
                    }
                }
            }
            foreach ($list as $key => $value) {
                foreach ($value['products'] as $key1 => $value1) {
                    foreach ($value1['shippingtpl'] as $key2 => $value2) {
                        if($value2['id']==$shipcart[$value1['cartid']]){
                            $list[$key]['products'][$key1]['shippingtpl'][$key2]['selected']=true;
                        }else{
                            $list[$key]['products'][$key1]['shippingtpl'][$key2]['selected']=false;
                        }
                    }
                }
            }
            if($source=='app'){
                if($selshipaddr){
                    foreach ($addrlist as $key => $value) {
                        if($value['id']==$selshipaddr){
                            $shippingaddr=$value;
                            break;
                        }
                    }
                }else{
                    foreach ($addrlist as $key => $value) {
                        if($value['isdefault']=='1'){
                            $shippingaddr=$value;
                            break;
                        }
                    }
                    if(empty($shippingaddr)){
                        $shippingaddr=$addrlist[0];
                    }
                }
                $shippingaddr['selected']=true;
                if($shippingaddr['isdefault']=='1'){
                    $shippingaddr['isdefault']=true;
                }else{
                    $shippingaddr['isdefault']=false;
                }
                //$applist=array();
                $total['prodamount']=$prodamount;
                $total['amount']=$sumamount;
                $total['quantity']=$sumquantity;
                // foreach ($list as $key => $value) {
                //     $newvalue=$value;
                //     $newvalue['cartid']=$key;
                //     foreach ($value['specs'] as $key => $value) {
                //         $newvalue['specs']=$value;
                //     }
                //     array_push($applist, $newvalue);
                // }
                // if($shipmethod){
                //     $shipcart=array();
                //     foreach ($cartid as $key => $value) {
                //         $shipcart[$value]=$shipmethod[$key];
                //     }
                // }else{
                //     $shipcart=array();
                //     foreach ($applist as $key => $value) {
                //         $shipcart[$value['cartid']]=$value['shippingtpl'][0]['id'];
                //     }
                // }
                $prodapi=new ProductApi;
                $shipping=$prodapi->calcShippingAmount($shipcart,$shippingaddr['id']);
                $issettle=1;
                foreach ($shipping as $key => $value) {
                    if($value['isempty']=='1'){
                        $issettle=0;
                    }
                    $total['amount']=$total['amount']+$value['shipping'];
                }
                $this->ajaxReturn(array('success'=>true,'body'=>array('cartlist'=>$list,'shippngaddr'=>$shippingaddr,'total'=>$total,'shipping'=>$shipping)));
            }else{
                if($selshipaddr){
                    $posindex=0;
                    foreach ($addrlist as $key => $value) {
                        if($value['id']==$selshipaddr){
                            $shippingaddr=$value;
                            $shippingaddr['selected']=true;
                            $addrlist[$key]['selected']=true;
                            $posindex=$key;
                        }else{
                            $addrlist[$key]['selected']=false;
                        }
                    }
                    $addrlist[$posindex]=$addrlist[0];
                    $addrlist[0]=$shippingaddr;
                }else{
                    foreach ($addrlist as $key => $value) {
                        if($value['isdefault']=='1'){
                            $shippingaddr=$value;
                            $addrlist[$key]['selected']=true;
                        }else{
                            $addrlist[$key]['selected']=false;
                        }
                    }
                }

                $prodapi=new ProductApi;
                $shipping=$prodapi->calcShippingAmount($shipcart,$shippingaddr['id']);
                $issettle=1;
                foreach ($shipping as $key => $value) {
                    if($value['isempty']=='1'){
                        $issettle=0;
                    }
                    $sumamount=$sumamount+$value['shipping'];
                }
                $this->assign('list',$list);
                $this->assign('prodamount',$prodamount);
                $this->assign('sumamount',$sumamount);
                $this->assign('addrlist',$addrlist);
                $this->assign('shipping',$shipping);
                $this->assign('issettle',$issettle);
                $this->assign('pagename','page-ordersettle');
                $this->display();
            }
        }
    }

    public function orderpay($source='web',$token=0){
        $uid=0;
        if($source=='app'){
            $auth=session('user_auth');
            if($auth['token']!=$token){
                $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'身份验证错误')));
            }
            $uid=$auth['uid'];
        }else{
            $uid=is_login();
        }
        if(!$uid){
            if($source=='app'){
                $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>false,'error'=>'您还没有登陆')));
            }else{
                $this->error( '您还没有登陆',U('User/login'),false,5);
            }
        }
        $selcartid=I('cartlist');
        if($source=='app'){
            $selcartid=explode(',', $selcartid);
        }
        $comments=I('comments');
        $realamount=I('realamount');
        $shipmethod=I('shippingmethod');
        $selshipaddr=I('selshipaddr');
        $shopid=I('shopid');
        $coupon=I('coupon');

        $shopcoupon=array();
        foreach ($shopid as $key => $value) {
            $shopcoupon[$value]=$coupon[$key];
        }

        $shipcart=array();
        foreach ($selcartid as $key => $value) {
            $shipcart[$value]=$shipmethod[$key];
        }

        if(empty($selcartid)){
            if($source=='app'){
                $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'没有需要结算的商品，请先选择需要需要结算的商品')));
            }else{
                $this->error('没有需要结算的商品，请先选择需要需要结算的商品');
            }
        }
        if(empty($shipmethod)){
            if($source=='app'){
                $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'运输方式参数不正确')));
            }else{
                $this->error('运输方式参数不正确');
            }
        }
        if(empty($shopcoupon)){
            if($source=='app'){
                $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'优惠券参数不正确')));
            }else{
                $this->error('优惠券参数不正确');
            }
        }
        if(empty($selshipaddr)){
            if($source=='app'){
                $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'收货地址错误')));
            }else{
                $this->error('收货地址错误');
            }
        }else{
            $memaddress=D('member_shippingaddr')->where(array('id'=>$selshipaddr))->find();
            if($memaddress['provincename']==''||$memaddress['cityname']==''||$memaddress['districtname']==''||$memaddress['detailaddr']==''||$memaddress['receivename']==''||$memaddress['linkphone']==''){
                if($source=='app'){
                    $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'收货地址错误')));
                }else{
                    $this->error('收货地址错误');
                }
            }
        }
        $orderapi=new OrderApi;
        //检查价格是否有变，如果有变，则重新确认
        $rescheck=$orderapi->checkShopPrice($selcartid,$realamount);
        if(!$rescheck['invent']['status']){
            if($source=='app'){
                $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>$rescheck['invent']['info'])));
            }else{
                $this->error($rescheck['invent']['info']);
            }
        }else{
            if(!$rescheck['cash']['status']){
                if($source=='app'){
                    $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>$rescheck['cash']['info'])));
                }else{
                    $this->error($rescheck['cash']['info']);
                }
            }
        }

        $prodapi=new ProductApi;
        $shipping=$prodapi->calcShippingAmount($shipcart,$selshipaddr);
        $issettle=1;
        $messhiperror='';
        foreach ($shipping as $key => $value) {
            if($value['isempty']=='1'){
                $issettle=0;
                $messhiperror=$value['error'];
            }
        }
        if(!$issettle){
            if($source=='app'){
                $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>$messhiperror)));
            }else{
                $this->error($messhiperror);
            }
        }

        //生成订单，并支付
        $res=$orderapi->newShopOrder($uid,$shipcart,$comments,$source,$shipping,$shopcoupon,$selshipaddr);
        if($res['status']){
            if($source=='app'){
                $this->ajaxReturn(array('success'=>true,'body'=>array('isLogined'=>true,'error'=>'支付成功')));
            }else{
                $this->redirect('/unionpay/frontconsume?orderId='.$res['orderid'].'&txnAmt='.$res['amount']);
            }
        }else{
            if($source=='app'){
                $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'生成订单失败')));
            }else{
                $this->error('生成订单失败'.$res['info']);
            }
        }
    }

    //支付成功
    public function orderpaysuccess(){
        $this->assign('pagename','page-orderpaysuccess');
        $this->display();
    }

    //收藏
    public function collect($source='web',$token=0){
        $type=I('type');
        $object_id=I('object_id');
        $token=I('token');
        if($object_id==0 || $object_id==""){
            $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'参数错误')));
        }
        $uid=0;
        if($source=='app'){
            $auth=session('user_auth');
            if($auth['token']!=$token){
                $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'身份验证错误')));
            }
            $uid=$auth['uid'];
        }else{
            $uid=is_login();
        }
        if(!$uid){
            if($source=='app'){
                $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>false,'error'=>'您还没有登陆')));
            }else{
                $this->error( '您还没有登陆',U('User/login'),false,5);
            }
        }

        if($source=='app'){
            $gi=I('gi');
            if(!$gi){
                $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'参数错误')));
            }
            $shopapi=new ShopApi;
            $shop=$shopapi->getShopByGUID($gi);
            if(!$shop){
                $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'店铺不存在')));
            }else{
                $sourceobjid=$shop['id'];
            }
        }else{
            $sourceobjid=I('sourceobjid');
        }
        if($type=='product'){
            if($sourceobjid==0 || $sourceobjid==""){
                $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'参数错误')));
            }
        }

        $userapi=new UserApi;
        $res=$userapi->collect($object_id,'2',$uid,$type,$sourceobjid);
        if($res['command']){
            $this->ajaxReturn(array('success'=>true,'body'=>array('isLogined'=>true,'error'=>'已收藏','bookmarked'=>true)));
        }else{
            $this->ajaxReturn(array('success'=>true,'body'=>array('isLogined'=>true,'error'=>'已取消收藏','bookmarked'=>false)));
        }
    }

    //喜欢
    public function favor($source='web',$token=0){
        $object_id=I('object_id');
        $token=I('token');
        if($object_id==0 || $object_id==""){
            $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'参数错误')));
        }
        $uid=0;
        if($source=='app'){
            $auth=session('user_auth');
            if($auth['token']!=$token){
                $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'身份验证错误')));
            }
            $uid=$auth['uid'];
        }else{
            $uid=is_login();
        }
        if(!$uid){
            if($source=='app'){
                $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>false,'error'=>'您还没有登陆')));
            }else{
                $this->error( '您还没有登陆',U('User/login'),false,5);
            }
        }
        $userapi=new UserApi;
        if($userapi->favor($object_id,'2',$uid,'product')){
            $this->ajaxReturn(array('success'=>true,'body'=>array('isLogined'=>true,'error'=>'已成功')));
        }else{
            $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'失败')));
        }
    }

    //app获取所有评论
    public function getProductCommentList($product_id,$gi){
        if(empty($gi)){
            $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'参数错误')));
        }
        $shopapi=new ShopApi;
        $shop=$shopapi->getShopByGUID($gi);
        $prodapi=new ProductApi;
        $list=$prodapi->getProductComments($product_id,2,$shop['uid']);
        $this->ajaxReturn(array('success'=>true,'body'=>$list));
    }
}