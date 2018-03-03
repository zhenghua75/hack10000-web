<?php

namespace Home\Controller;
use Common\Api\CategoryApi;
use Common\Api\ProductApi;
use User\Api\UserApi;
use Common\Api\OrderApi;
use Common\Api\ShopApi;
use Common\Api\DocumentApi;
use Common\Api\SystemApi;

/**
 * 前台商城控制器
 * 主要获取商城聚合数据
 */
class StoreController extends HomeController {

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
            }else{
                $this->error( '您还没有登陆',U('User/login'),false,5);
            }
        }
    }

	//商城首页
    public function index($source='web'){
        $part=I('part');
        $cate=I('cate');
        $curpage=I('p');
        $cataapi=new CategoryApi;
        if($source=='app'){
            $catelist=$cataapi->getAllCatalog();
            $prodpart=array();
            foreach (C('PRODUCTPART') as $key => $value) {
                $tmp['id']=intval($key);
                $tmp['name']=$value;
                array_push($prodpart, $tmp);
            }
        }else{
            if(empty($cate)){
                $cate=0;
            }
            $catelist=$cataapi->getAllCatalog($cate);
        }
        $pageslide=D('page')->where(array('url'=>'store/index'))->select();
        $prodapi=new ProductApi;
        $prodlist=$prodapi->getStorePorductList($part,$cate,$curpage);
        if($source=='app'){
            $docapi=new DocumentApi;
            $slideshowlist=array();
            foreach ($pageslide as $key => $value) {
                $slideshow['image']=get_picture_path($value['itemid'],'slideshow','600x450');
                $size=getimagesize($slideshow['image']);
                $slideshow['imagesize']=array('width'=>$size[0],'height'=>$size[1]);
                $slideshow['id']=$value['itemid'];
                $slideshow['linktype']=$value['linkidtype'];
                $slideshow['content']=intval($docapi->getDocCatelog($value['linkid']));
                array_push($slideshowlist, $slideshow);
            }
            $sysapi=new SystemApi;
            $sysapi->writeAccessLog('社会实践商城','app');
            $this->ajaxReturn(array('success'=>ture,'body'=>array('productpart'=>$prodpart,'catalog'=>$catelist,'product'=>$prodlist['prod'],'slideshow'=>$slideshowlist)));
        }else{
            $sysapi=new SystemApi;
            $sysapi->writeAccessLog('社会实践商城');
            foreach ($pageslide as $key => &$value) {
                $value['imagepath']=get_picture_path($value['itemid'],'slideshow','org');
            }
            $total      =   $prodlist['total']? $prodlist['total'] : count($prodlist['prod']) ;
            $listRows   =   C('PROD_LIST_ROWS') > 0 ? C('PROD_LIST_ROWS') : 24;
            $page       =   new \Think\Page($total, $listRows);
            $voList     =   $prodlist['prod'];
            $p          =   $page->show();
            $this->assign('_list', $voList);
            $this->assign('_page', $p? $p: '');
            // 记录当前列表页的cookie
            Cookie('__forward__',$_SERVER['REQUEST_URI']);
            $this->assign('catelist',$catelist);
            $this->assign('slideshow',$pageslide);
            $this->meta_title = '社会实践';
            $this->assign('pagename','page-storeindex');
            $this->display();
        }
    }

    //商城首页
    public function selfemployed($source='web'){
        $part=I('part');
        $cate=I('cate');
        $curpage=I('p');
        $cataapi=new CategoryApi;
        if($source=='app'){
            $catelist=$cataapi->getAllCatalog();
            $prodpart=array();
            foreach (C('PRODUCTPART') as $key => $value) {
                $tmp['id']=intval($key);
                $tmp['name']=$value;
                array_push($prodpart, $tmp);
            }
        }else{
            if(empty($cate)){
                $cate=0;
            }
            $catelist=$cataapi->getAllCatalog($cate);
        }
        $pageslide=D('page')->where(array('url'=>'store/selfemployed'))->select();
        $prodapi=new ProductApi;
        $prodlist=$prodapi->getStorePorductList($part,$cate,$curpage);
        if($source=='app'){
            $docapi=new DocumentApi;
            $slideshowlist=array();
            foreach ($pageslide as $key => $value) {
                $slideshow['image']=get_picture_path($value['itemid'],'slideshow','600x450');
                $size=getimagesize($slideshow['image']);
                $slideshow['imagesize']=array('width'=>$size[0],'height'=>$size[1]);
                $slideshow['id']=$value['itemid'];
                $slideshow['linktype']=$value['linkidtype'];
                $slideshow['content']=intval($docapi->getDocCatelog($value['linkid']));
                array_push($slideshowlist, $slideshow);
            }
            $sysapi=new SystemApi;
            $sysapi->writeAccessLog('自主创业商城','app');
            $this->ajaxReturn(array('success'=>ture,'body'=>array('productpart'=>$prodpart,'catalog'=>$catelist,'product'=>$prodlist['prod'],'slideshow'=>$slideshowlist)));
        }else{
            $sysapi=new SystemApi;
            $sysapi->writeAccessLog('自主创业商城');
            foreach ($pageslide as $key => &$value) {
                $value['imagepath']=get_picture_path($value['itemid'],'slideshow','org');
            }
            $total      =   $prodlist['total']? $prodlist['total'] : count($prodlist['prod']) ;
            $listRows   =   C('PROD_LIST_ROWS') > 0 ? C('PROD_LIST_ROWS') : 24;
            $page       =   new \Think\Page($total, $listRows);
            $voList     =   $prodlist['prod'];
            $p          =   $page->show();
            $this->assign('_list', $voList);
            $this->assign('_page', $p? $p: '');
            // 记录当前列表页的cookie
            Cookie('__forward__',$_SERVER['REQUEST_URI']);
            $this->assign('catelist',$catelist);
            $this->assign('slideshow',$pageslide);
            $this->meta_title = '自主创业';
            $this->assign('pagename','page-storeindex');
            $this->display();
        }
    }

    //创客商城通用商品详情
    public function product($source='web',$id=0){
        if(!$id){
            $this->error('商品不存在或已下步架',U('Store/index'));
        }
        $token=I('token');
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
            $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>false,'error'=>'您还没有登陆')));
        }
        $prodapi=new ProductApi;
        $prodapi->updateProductView($id);
        $info=$prodapi->getProductInfo($id,1);
        $userapi=new UserApi;
        $info['bookmarked']=$userapi->isCollect($id,1,$uid,'product');
        $info['liked']=$userapi->isFavor($id,1,$uid,'product');
        if($source=='app'){
            $info['detail']=str_replace('org', '800x800', $info['detail']);
            $size=getimagesize($info['detail']);
            $info['detailsize']=array('width'=>$size[0],'height'=>$size[1]);
            $info['lastComment']=$prodapi->getProductLastComments($info['id'],1);
            $sysapi=new SystemApi;
            $sysapi->writeAccessLog('商城商品详情','app');
            $this->ajaxReturn(array('success'=>true,'body'=>$info));
        }else{
            $specs=array();
            $dataprice=array();
            if(count($info['specs'])==1 && $info['specs'][0]['gid']==0){
                $dataprice['marketprice']=$info['specs'][0]['marketPrice'];
                $dataprice['hackprice']=$info['specs'][0]['hackPrice'];
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
            $commentlist=$prodapi->getProductComments($info['id'],1);
            $sysapi=new SystemApi;
            $sysapi->writeAccessLog('商城商品详情');
            $specgroup=$prodapi->getProductSpecGroup($id);
            $this->assign('info',$info);
            $this->assign('specs',$specs);
            $this->assign('specgroup',$specgroup);
            $this->assign('dataprice',$dataprice);
            $this->assign('commentlist',$commentlist);
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
        $hackprice=I('selhackprice');
        $quantity=I('selquantity');
        $token=I('token');
        if($source=='app'){
            $auth=session('user_auth');
            if($auth['token']!=$token){
                $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>false,'error'=>'您还没有登陆')));
            }
        }
        $hcoin=0;
        if($this->memberstatus[1] || $this->memberstatus[2]){
            if($this->memberstatus[1] && $this->memberstatus[1]['status']!='1'){
                if($source=='app'){
                    $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'您的创客申请还未通过，暂时不能选购商品')));
                }else{
                    $this->error('您的创客申请还未通过，暂时不能选购商品');
                }
            }
            if($this->memberstatus[2] && $this->memberstatus[2]['status']!='1'){
                if($source=='app'){
                    $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'您的创客申请还未通过，暂时不能选购商品')));
                }else{
                    $this->error('您的创客申请还未通过，暂时不能选购商品');
                }
            }
            $userapi=new UserApi;
            $res=$userapi->getHCoin($uid);
            if($res['hcoin']==0){
                $this->error($res['info']);
            }else{
                $hcoin=$res['hcoin'];
                if($hcoin<intval($hackprice)){
                    if($source=='app'){
                        $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'慧爱币余额不足')));
                    }else{
                        $this->error('慧爱币余额不足');
                    }
                }
            }
            $prodapi=new ProductApi;
            $specgroup=$prodapi->getProductSpecGroup($prodid);
            $inventorytmp=0;
            if($specgroup['sys_attrprice']){
                foreach ($specgroup['sys_attrprice'] as $key => $value) {
                    if($value['group_id']==$group_id){
                        $inventorytmp=$value['inventory'];
                    }
                }
            }else{
                $info=$prodapi->getProductInfo($prodid,1);
                if($info['specs'][0]['gid']==0){
                    $inventorytmp=$info['specs'][0]['inventory'];
                }
            }
            $orderapi=new OrderApi;
            $carttotal=$orderapi->getStoreCartTotalQuantity($uid,$prodid,$group_id);
            if(intval($inventorytmp)<=intval($carttotal)){
                if($source=='app'){
                    $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'库存不足，您来晚了！')));
                }else{
                    $this->ajaxReturn(array('status' => 0, 'info' => '库存不足，您来晚了！'));
                }
            }else{
                $orderapi=new OrderApi;
                $data['uid']=$uid;
                $data['product_id']=$prodid;
                $data['group_id']=$group_id;
                if($source=='app'){
                    $data['quantity']=$quantity;
                }else{
                    $data['quantity']=1;
                }
                $data['type']='1';
                $data['makeruid']=0;
                $res=$orderapi->insertCart($data,1);
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
        }else{
            if($source=='app'){
                $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'您还没有成为创客，请先申请成为创客')));
            }else{
                $this->error('您还没有成为创客，请先申请成为创客');
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
        $datatmp=I('cartlist');
        if($source=='app'){
            $cartid=json_decode($datatmp,true);
        }else{
            $cartid=$datatmp;
        }
        if($source=='app'){
            $auth=session('user_auth');
            if($auth['token']!=$token){
                $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>false,'error'=>'您还没有登陆')));
            }
            $uid=$auth['uid'];
        }else{
            $uid=is_login();
        }
        if(empty($cartid)){
            if($source=='app'){
                $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'没有需要结算的商品，请先选择需要需要结算的商品')));
            }else{
                $this->error('没有需要结算的商品，请先选择需要需要结算的商品',U('User/makercart'));
            }
        }else{
            $orderapi=new OrderApi;
            if($source=='app'){
                $list=$orderapi->updateAfterGetStoreCart($uid,$cartid);
            }else{
                $list=$orderapi->getSettleStoreCart($uid);
            }
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
            $shopapi=new ShopApi;
            $shop=$shopapi->getMyShop($uid);
            if($source=='app'){
                $applist=array();
                $total=$list['total'];
                unset($list['total']);
                foreach ($list as $key => $value) {
                    $newvalue=$value;
                    $newvalue['cartid']=$key;
                    foreach ($value['specs'] as $key => $value) {
                        $newvalue['specs']=$value;
                    }
                    array_push($applist, $newvalue);
                }
                $this->ajaxReturn(array('success'=>true,'body'=>array('cartlist'=>$applist,'total'=>$total)));
            }else{
                foreach ($list as $key => $value) {
                    if(!in_array($key, $cartid)){
                        unset($list[$key]);
                    }
                }
                $sumamount=0;
                $realamount=0;
                foreach ($list as $key => $value) {
                    foreach ($value['specs'] as $key1 => $value1) {
                        if($value1['selected']){
                            $selspecname='';
                            if($value1['gid']!=0){
                                foreach ($value1['spec'] as $key2 => $value2) {
                                    $selspecname=$selspecname.$value2['name'].':'.$value2['value']['name'].' ';
                                }
                            }
                            $list[$key]['selspecname']=$selspecname;
                            $list[$key]['selamount']=$value1['amount'];
                            $list[$key]['selquantity']=$value1['quantity'];
                            $list[$key]['selhackprice']=$value1['hackPrice'];
                            if($value1['image']){
                                $list[$key]['selimage']=$value1['image'];
                            }else{
                                $list[$key]['selimage']=$value['images'][0];
                            }
                            $sumamount=$sumamount+$value1['amount'];
                            $realamount=$realamount+$value1['amount'];
                        }
                    }
                }
                $this->assign('shop',$shop);
                $this->assign('list',$list);
                $this->assign('sumamount',$sumamount);
                $this->assign('realamount',$realamount);
                $this->assign('pagename','page-ordersettle');
                $this->display();
            }
        }
    }

    public function orderpay($source='web',$token=0){
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
        $selcartid=I('selcartid');
        if($source=='app'){
            $selcartid=explode(',', $selcartid);
        }
        $comments=I('comment');
        if($source=='app'){
            $comments=explode(',', $comments);
        }
        $realamount=I('realamount');
        $orderapi=new OrderApi;
        //检查价格是否有变，如果有变，则重新确认
        $rescheck=$orderapi->checkStorePrice($selcartid,$realamount);
        if(!$rescheck['invent']['status']){
            if($source=='app'){
                $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>$rescheck['invent']['info'])));
            }else{
                $this->error($rescheck['invent']['info']);
            }
        }else{
            if(!$rescheck['hcoin']['status']){
                if($source=='app'){
                    $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>$rescheck['hcoin']['info'])));
                }else{
                    $this->error($rescheck['hcoin']['info']);
                }
            }
        }

        //检查慧爱币是否足够支付
        $userapi=new UserApi;
        $hcoin=$userapi->getHCoin($uid);
        if($hcoin['hcoin']<$realamount){
            if($source=='app'){
                $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'慧爱币余额不足')));
            }else{
                $this->error('慧爱币余额不足');
            }
        }
        //生成订单，并支付
        $res=$orderapi->newStoreOrder($uid,$selcartid,$comments,$source);
        if($res){
            if($source=='app'){
                $this->ajaxReturn(array('success'=>true,'body'=>array('isLogined'=>true,'error'=>'支付成功')));
            }else{
                $this->redirect('Store/orderpaysuccess');
            }
        }else{
            if($source=='app'){
                $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'支付失败')));
            }else{
                $this->error('支付失败');
            }
        }
    }

    //支付成功
    public function orderpaysuccess(){
        $this->assign('pagename','page-orderpaysuccess');
        $this->display();
    }

    //收藏
    public function collect($source='web'){
        $object_id=I('object_id');
        $type=I('type');
        $token=I('token');
        if($object_id==0 || $object_id==""){
            $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'参数错误')));
        }
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
            $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>false,'error'=>'您还没有登陆')));
        }
        $userapi=new UserApi;
        $res=$userapi->collect($object_id,'1',$uid,$type);
        if($res['command']){
            $this->ajaxReturn(array('success'=>true,'body'=>array('isLogined'=>true,'error'=>'已收藏','bookmarked'=>true)));
        }else{
            $this->ajaxReturn(array('success'=>true,'body'=>array('isLogined'=>true,'error'=>'已取消收藏','bookmarked'=>false)));
        }
    }

    //喜欢
    public function favor($source='web'){
        $object_id=I('object_id');
        $token=I('token');
        if($object_id==0 || $object_id==""){
            $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'参数错误')));
        }
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
            $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>false,'error'=>'您还没有登陆')));
        }
        $userapi=new UserApi;
        if($userapi->favor($object_id,'1',$uid,'product')){
            $this->ajaxReturn(array('success'=>true,'body'=>array('isLogined'=>true,'error'=>'已成功')));
        }else{
            $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'失败')));
        }
    }

    //创新实验
    public function innovate($source='web',$token=0){
        $page=D('page')->where(array('url'=>'index/innovate'))->select();
        if($source=='app'){
            $sysapi=new SystemApi;
            $sysapi->writeAccessLog('创新实验商城','app');
        }else{
            foreach ($page as $key => &$value) {
                if($value['itemtype']=='slideshow'){
                    $value['imagepath']=get_picture_path($value['itemid'],'slideshow','org');
                }else{
                    $content=get_picture_path($value['itemid'],'page','org');
                    unset($page[$key]);
                }
            }
            $sysapi=new SystemApi;
            $sysapi->writeAccessLog('创新实验商城');
            $this->meta_title = '创新实验';
            $this->assign('slideshow',$page);
            $this->assign('content',$content);
            $this->meta_title = '创新实验';
            $this->assign('pagename','page-storeinnovate');
            $this->display();
        }
    }

	//秒杀
    public function rush(){
        $this->display();
    }
	//预售
    public function presell(){
        $this->display();
    }
    //特供商品
    public function special(){
        $this->display();
    }
    //众筹
    public function crowdfundi(){
        $this->display();
    }
    //预告
    public function trailer_goods(){
        $this->display();
    }
    //即将开始
    public function straight_rush(){
        $this->display();
    }
    //众筹详情
    public function crowdfundi_goods(){
        $this->display();
    }

    //app获取所有评论
    public function getProductCommentList($product_id){
        $prodapi=new ProductApi;
        $list=$prodapi->getProductComments($product_id,1);
        $this->ajaxReturn(array('success'=>true,'body'=>$list));
    }
}