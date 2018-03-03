<?php

namespace Common\Api;
use Common\Model\PictureModel;
use Common\Model\ProductModel;
use Common\Api\CategoryApi;
use User\Api\UserApi;

class ShopApi {

    public function picture_upload($data){
        $return  = array('status' => 1, 'id' => '', 'path' => '', 'info' => '');
        $picmodel = new PictureModel();
        $info = $picmodel->upload($data['uid'],$data['imgtype'],$data['files']);
        if($info){
            foreach ($info as $key => $value) {
                $return['status']=1;
                $return['id']=$value['id'];
                if(strtoupper($data['imgtype'])=='SHOPLOGO'){
                    $return['path']=get_picture_path($value['id'],$data['imgtype'],'200x200');
                }elseif(strtoupper($data['imgtype'])=='SHOPBACKGP'){
                    $return['path']=get_picture_path($value['id'],$data['imgtype'],'1200x400');
                }else{
                    $return['path']='';
                }
                
            }
        }else{
            $return['status']=0;
            $return['info']=$picmodel->getError();
        }
        return $return;
    }

    public function get_user_product_pic($uid,$imgtype){
        $mempic=D("member_picture");
        $list=$mempic->where(array('uid'=>$uid,'imgtype'=>$imgtype))->select();
        foreach ($list as $key => $value) {
            if($imgtype=="shoplogo"){
                $list[$key]['path']=get_picture_path($value['pid'],$imgtype,'200x200');
            }elseif($imgtype=="shopbackgp"){
                $list[$key]['path']=get_picture_path($value['pid'],$imgtype,'1200x400');
            }
        }
        return $list;
    }

    public function newShop($uid,$data){
        $shop=D('maker_store')->where(array('uid'=>$uid))->find();
        $res=1;
        if($shop){
            D('maker_store')->where(array('uid'=>$uid))->save($data);
        }else{
            $data['tplid']=1;
            $data['guid'] = strtoupper(md5(uniqid(mt_rand(), true)));
            $data['created']=time();
            $data['uid']=$uid;
            $res=D('maker_store')->add($data);
        }
        if($res){
            $shopnew=D('maker_store')->where(array('uid'=>$uid))->find();
            if(!$shopnew['qrcode']){
                $picmodel = new PictureModel();
                $info = $picmodel->qrcodeupload($shopnew['uid'],'SHOPQRCODE',$shopnew['guid']);
                if($info){
                    foreach ($info as $key => $value) {
                        D('maker_store')->where(array('uid'=>$shopnew['uid']))->save(array('qrcode'=>$value['id']));
                    }
                    return array('status' => 1, 'id' => $res, 'info' => '店铺设置成功');
                }else{
                    return array('status' => 0, 'id' => $res, 'info' => $picmodel->getError());
                }
            }else{
                return array('status' => 1, 'id' => 0, 'info' => '店铺设置成功');
            }
        }else{
            return array('status' => 0, 'id' => 0, 'info' => '店铺设置失败');
        }
    }

    public function getMyShop($uid){
        $shop=D('maker_store')->where(array('uid'=>$uid))->find();
        if($shop){
            $tpl=D('maker_store_tpl')->where(array('id'=>$shop['tplid']))->find();
            $shop['logopath']=get_picture_path($shop['logo'],'shoplogo','200x200');
            $shop['backgroudpath']=get_picture_path($shop['backgroud'],'shopbackgp','1200x400');
            $shop['qrcodepath']=get_picture_path($shop['qrcode'],'shopqrcode','org');
            $shop['tplhtml']=$tpl['tplhtml'];
        }else{
            $shopdata['uid']=$uid;
            $res=$this->newShop($uid,$shopdata);
            if($res['status']){
                $shop=D('maker_store')->where(array('uid'=>$uid))->find();
                $tpl=D('maker_store_tpl')->where(array('id'=>1))->find();
                $shop['logopath']='';
                $shop['backgroudpath']='';
                $shop['qrcodepath']=get_picture_path($shop['qrcode'],'shopqrcode','org');
                $shop['tplhtml']=$tpl['tplhtml'];
            }
        }

        return $shop;
    }

    public function getShopByGUID($guid,$uid=0){
        $shop=D('maker_store')->where(array('guid'=>$guid))->find();
        if($shop){
            $tpl=D('maker_store_tpl')->where(array('id'=>$shop['tplid']))->find();
            $shop['logopath']=get_picture_path($shop['logo'],'shoplogo','200x200');
            $shop['backgroudpath']=get_picture_path($shop['backgroud'],'shopbackgp','1200x400');
            $shop['tplhtml']=$tpl['tplhtml'];
            $shop['qrcodepath']=get_picture_path($shop['qrcode'],'shopqrcode','org');
            $shop['bookmarkQuantity']=$this->getShopCollectCount($shop['id']);
            $userapi=new UserApi;
            $shop['bookmarked']=$userapi->isCollect($shop['id'],2,$uid,'shop');
        }

        return $shop;
    }

    public function getShopByID($id){
        $shop=D('maker_store')->where(array('id'=>$id))->find();
        if($shop){
            $tpl=D('maker_store_tpl')->where(array('id'=>$shop['tplid']))->find();
            $shop['logopath']=get_picture_path($shop['logo'],'shoplogo','200x200');
            $shop['backgroudpath']=get_picture_path($shop['backgroud'],'shopbackgp','1200x400');
            $shop['tplhtml']=$tpl['tplhtml'];
        }

        return $shop;
    }

    public function getShopGuid($id){
        return D('maker_store')->where(array('id'=>$id))->getField('guid');
    }

    public function getShopTpl(){
        return D('maker_store_tpl')->select();
    }

    public function getShopName($uid){
        return D('maker_store')->where(array('uid'=>$uid))->getField('name');
    }

    public function getMyShopProductList($uid,$cate=0){
        $product=D('maker_store_product')->where(array('uid'=>$uid,'status'=>'1'))->select();
        $return=array();
        $catelist=array();
        $productlist=array();
        $strcateid='';
        $prodmodel = new ProductModel();
        $prodapi=new ProductApi;
        $cateapi=new CategoryApi;
        foreach ($product as $key => $value) {
            if($prodmodel->productIsError($value['product_id'])){
                continue;
            }
            $cate3level=$prodmodel->getProduct3LevelCatalog($value['product_id']);
            $catelevel=$cateapi->getCatalogByID($cate3level['id']);
            if(!array_key_exists($catelevel[0]['id'], $catelist)){
                $catelist[$catelevel[0]['id']]=$catelevel[0]['name'];
                $strcateid=$strcateid.','.$catelevel[0]['id'];
            }
            if($cate!=0 && $catelevel[0]['id']!=$cate){
                continue;
            }else{
                $prod=$prodapi->getProductDetail($value['product_id'],$uid);
                // $prod['store']['id']=$uid;
                // $prod['store']['name']=$this->getShopName($uid);
                // $prod['type']=2;
                // $prod['saleQuantity']=0;
                // $prod['commentQuantity']=0;
                // $prod['commentScore']=0;
                // $prod['bookmarkQuantity']=0;
                // $prod['bookmarked']=false;
                // $prod['likeQunatity']=0;
                // $prod['liked']=false;
                // $prod['lastComment']=array('id'=>1,'body'=>'测试','user'=>array('id'=>3,'name'=>'klong','headImage'=>'http://img.hack10000.com/portrait/default.png'),'date'=>'2015-12-12','specs'=>'颜色：白色');
                if($prod['specs'][0]['gid']==0){
                    $prod['defaultdisplayprice']=$prod['specs'][0]['price'];
                }else{
                    $defaultprice=$prodmodel->getProductDefaultDisplayPrice($value['product_id'],$uid);
                    $prod['defaultdisplayprice']=$defaultprice['price'];
                }
                $prod['image']=$prod['images'][0];
                $prod['image']=str_replace('96x72', '300x225', $prod['image']);
                unset($prod['specs']);
                unset($prod['images']);
                array_push($productlist, $prod);
            }
        }
        //分类按排序重新获取
        $strcateid=substr($strcateid, 1);
        $catelist=array();
        if(!empty($strcateid)){
            $catelist=D('product_catalog')->where(array('id'=>array('in',$strcateid)))->order('sort')->getField('id,name');
        }

        $return['prod']=$productlist;
        $return['cate']=$catelist;

        return $return;
    }

    public function updateShopTpl($uid,$tplid){
        $shop=D('maker_store')->where(array('uid'=>$uid))->find();
        if($shop){
            D('maker_store')->where(array('uid'=>$uid))->setField('tplid',$tplid);
            return array('status' => 1, 'info' => '店铺设置成功');
        }else{
            return array('status' => 0, 'info' => '店铺设置失败');
        }
    }

    public function getShopCollectCount($shopid){
        return D('member_collection')->where(array('objid'=>$shopid,'objtype'=>'shop','source'=>'2','collect'=>1))->count();
    }

    public function getFavorCount($shopid){
        return D('member_collection')->where(array('objid'=>$shopid,'objtype'=>'shop','source'=>'2','favor'=>1))->count();
    }

    public function getShopFans($shopid){
        $collect=D('member_collection')->where(array('objid'=>$shopid,'objtype'=>'shop','source'=>'2','collect'=>1))->getField('uid',true);
        $fans=array();
        foreach ($collect as $key => $value) {
            if(!array_key_exists($value, $fans)){
                $fans[$value]=get_nickname($value);
                if($fans[$value]==''){
                    $fans[$value]='匿名';
                }
            }
        }
        return $fans;
    }
}