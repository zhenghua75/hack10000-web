<?php

namespace Common\Api;
use Common\Model\PictureModel;
use Common\Model\ProductModel;
use Common\Model\SupplierModel;
use User\Api\UserApi;
use Common\Api\ShopApi;

class ProductApi {

    public function getAllProductSpec($uid){
        $map['status']='1';
        if($uid){
            $map['uid']=array('in','0,'.$uid);
        }
        $specdef = M('product_specdef')->where(array('status'=>'1'))->order('sort')->select();
        $specval = M('product_specval')->where($map)->order('sid,uid,sort')->select();
        $speclist=array();
        foreach ($specdef as $key => $value) {
            $speclist[$value['sid']]=$value;
            $speclist[$value['sid']]['value']=array();
            foreach ($specval as $key1 => $value1) {
                if($value['sid']==$value1['sid']){
                    array_push($speclist[$value['sid']]['value'], $value1);
                }
            }
        }
        return $speclist;
    }

    public function getProductSpecBySID($uid,$sid){
        $list=$this->getAllProductSpec($uid);
        $return=array();
        foreach ($sid as $key => $value) {
            array_push($return, $list[$value]);
        }
        return $return;
    }

    public function picture_upload($data){
        $return  = array('status' => 1, 'id' => '', 'path' => '', 'info' => '');
        $picmodel = new PictureModel();
        $pcid=empty($data['pcid']) ? 0 : $data['pcid'];
        $info = $picmodel->upload($data['uid'],$data['imgtype'],$data['files'],$pcid);
        if($info){
            foreach ($info as $key => $value) {
                $return['status']=1;
                $return['id']=$value['id'];
                $return['path']=get_picture_path($value['id'],$data['imgtype'],'300x225');
            }
        }else{
            $return['status']=0;
            $return['info']=$picmodel->getError();
        }
        return $return;
    }

    public function get_user_product_pic($uid,$imgtype,$pcid){
        $mempic=D("member_picture");
        $list=$mempic->where(array('uid'=>$uid,'imgtype'=>$imgtype,'pcid'=>$pcid))->select();
        foreach ($list as $key => $value) {
            if($imgtype=="product"){
                $list[$key]['path']=get_picture_path($value['pid'],$imgtype,'96x72');
            }else{
                $list[$key]['path']=get_picture_path($value['pid'],$imgtype,'org');
            }
        }
        return $list;
    }

    public function get_user_product_pic_class($uid,$type,$classname=''){
        $map['uid']=$uid;
        $map['type']=$type;
        if(!empty($classname)){
            $map['name']=array('like','%'.$classname.'%');
        }
        $mempic=D("member_picclass");
        $list=$mempic->where($map)->getField('pcid,name');
        $list[0]='默认目录';
        return $list;
    }

    public function custSpecval($data){
        $return  = array('status' => 1, 'vid' => '', 'info' => '');
        $prodmodel = new ProductModel();
        $newvid = $prodmodel->custSpecval($data);
        if($newvid){
            $return['vid']=$newvid;
        }else{
            $return['status']=0;
            $return['info']=$prodmodel->getError();
        }
        return $return;
    }

    public function productInsert($data){
        $return  = array('status' => 1, 'product_id' => '', 'info' => '');
        $prodmodel = new ProductModel();
        $newpid = $prodmodel->productInsert($data);
        if($newpid){
            $return['product_id']=$newpid;
        }else{
            $return['status']=0;
            $return['info']=$prodmodel->getError();
        }
        return $return;
    }

    public function productEdit($data){
        $return  = array('status' => 1, 'product_id' => '', 'info' => '');
        $prodmodel = new ProductModel();
        $prodid = $prodmodel->productEdit($data);
        if($prodid){
            $return['product_id']=$prodid;
        }else{
            $return['status']=0;
            $return['info']=$prodmodel->getError();
        }
        return $return;
    }

    public function getStorePorductList($part='',$cate=0,$page=1){
        $prodmodel = new ProductModel();
        $storelist=$prodmodel->getStorePorductList($part,$cate);
        $list['total']=count($storelist);
        $list['prod']=array();
        $listRows = C('PROD_LIST_ROWS') > 0 ? C('PROD_LIST_ROWS') : 24;
        $firstRow=$listRows * ($page - 1);
        $tmplist=array_slice($storelist, $firstRow, $listRows);
        foreach ($tmplist as $key => $value) {
            $prod=$this->getProductBaseDetail($value);
            array_push($list['prod'], $prod);
        }
        return $list;
    }

    public function getProductBaseDetail($product_id){
        $prodmodel = new ProductModel();
        $proddata=$prodmodel->getProductDetail($product_id);
        $userapi = new UserApi;
        $hsspecdef=$prodmodel->getHashSpecdef();
        $hsspecval=$prodmodel->getHashSpecval();
        $prod['id']=$proddata['product']['product_id'];
        $prod['status']=$proddata['product']['status'];
        $prod['name']=$proddata['product']['name'];
        $prod['productpart']=intval($proddata['product']['productpart']);
        $prod['imageid']=$proddata['productpic'][0]['linkimg'];
        $prod['image']=get_picture_path($prod['imageid'],'product','300x225');
        $prod['desc']=$proddata['product']['simcomment'];
        $prod['catalog_id']=$proddata['product']['catalog_id'];
        $prod['catalog_name']=D('product_catalog')->where(array('id'=>$prod['catalog_id']))->getField('name');
        if($proddata['productspec']){
            $defaultprice=$prodmodel->getProductDefaultDisplayPrice($product_id);
            $prod['defaultdisplayprice']=$defaultprice['hackprice'];
        }else{
            $prod['defaultdisplayprice']=$proddata['product']['hackprice']/100;
        }

        return $prod;
    }

    public function getProductInfo($product_id,$type,$uid=0,$shopid=0){
        $prodmodel = new ProductModel();
        $prod=$this->getProductDetail($product_id,$uid);
        if($shopid==0){
            $prod['store']['id']=0;
            $prod['store']['name']='';
            $prod['store']['guid']='';
        }else{
            $shopapi=new ShopApi;
            $shop=$shopapi->getShopByID($shopid);
            $prod['store']['id']=$shopid;
            $prod['store']['name']=$shop['name'];
            $prod['store']['guid']=$shop['guid'];
        }
        $prod['type']=intval($type);
        $prod['saleQuantity']=0;
        $comscore=$this->getProductCommentScore($product_id,$type,$uid);
        $prod['commentQuantity']=$comscore['cnt'];
        $prod['commentScore']=intval($comscore['score']/$comscore['cnt']);
        $prod['bookmarkQuantity']=$this->getCollectCount($product_id,$type,$shopid);
        $prod['bookmarked']=false;
        $prod['likeQunatity']=$this->getFavorCount($product_id,$type,$shopid);
        $prod['liked']=false;
        $prod['lastComment']=array();
        if($prod['specs'][0]['gid']==0){
            if($type==1){
                $prod['defaultdisplayprice']=$prod['specs'][0]['hackPrice'];
            }else{
                $prod['defaultdisplayprice']=$prod['specs'][0]['price'];
            }
        }else{
            $defaultprice=$prodmodel->getProductDefaultDisplayPrice($product_id,$uid);
            if($type==1){
                $prod['defaultdisplayprice']=$defaultprice['hackprice'];
            }else{
                $prod['defaultdisplayprice']=$defaultprice['price'];
            }
        }

        return $prod;
    }

    public function getProductInfoEdit($product_id){
        $prodmodel = new ProductModel();
        $proddata=$prodmodel->getProductDetail($product_id);
        $prod=$proddata['product'];
        $prod['supprice']=$prod['supprice']/100;
        $prod['hackprice']=$prod['hackprice']/100;
        $prod['marketprice']=$prod['marketprice']/100;
        $prod['price']=$prod['price']/100;
        $prod['detailpath']=get_picture_path($prod['detailpid'],'productdetail','org');
        $prod['images']=array();
        foreach ($proddata['productpic'] as $key => $value) {
            $prod['images'][$value['linkimg']]=get_picture_path($value['linkimg'],'product','96x72');
        }
        $prod['specs']=array();
        $prod['specslist']=array();
        foreach ($proddata['productspec'] as $key => $value) {
            $prod['specs'][$value['specgroup']]['group_id']=$value['group_id'];
            $prod['specs'][$value['specgroup']]['supprice']=$value['supprice']/100;
            $prod['specs'][$value['specgroup']]['inventory']=$value['inventory'];
            $prod['specs'][$value['specgroup']]['linkimg']=$value['linkimg'];
            $prod['specs'][$value['specgroup']]['linkimgpath']=get_picture_path($value['linkimg'],'product','96x72');
            $spectmp=explode('-', $value['specgroup']);
            foreach ($spectmp as $key1 => $value1) {
                if(!array_key_exists($value1, $prod['specslist'])){
                    $prod['specslist'][$value1]=1;
                }
            }
        }
        return $prod;
    }

    public function getProductDetail($product_id,$uid=0){
        $prodmodel = new ProductModel();
        $proddata=$prodmodel->getProductDetail($product_id);
        $userapi = new UserApi;
        $supcompany=$userapi->getSupplierCompany($proddata['product']['uid']);
        $hsspecdef=$prodmodel->getHashSpecdef();
        $hsspecval=$prodmodel->getHashSpecval();

        $prod=array();
        $prod['id']=$proddata['product']['product_id'];
        $prod['status']=$proddata['product']['status'];
        $prod['vendor']=array('id'=>$proddata['product']['uid'],'name'=>$supcompany);
        $prod['name']=$proddata['product']['name'];
        $prod['productpart']=intval($proddata['product']['productpart']);
        $prod['images']=array();
        foreach ($proddata['productpic'] as $key => $value) {
            array_push($prod['images'], get_picture_path($value['linkimg'],'product','96x72'));
        }
        $prod['desc']=$proddata['product']['simcomment'];
        $prod['detail']=get_picture_path($proddata['product']['detailpid'],'productdetail','org');
        //$prod['freeShipping']=false;

        $shiptpllist=D('product_attr')->where(array('product_id'=>$product_id,'status'=>1,'type'=>'shippingtpl'))->getField('value',true);
        $prod['shippingtpl']=array();
        foreach ($shiptpllist as $key => $value) {
            $shiptpl=D('shippingtpl')->where(array('id'=>$value))->find();
            array_push($prod['shippingtpl'], array('id'=>$shiptpl['id'],'name'=>$shiptpl['name']));
            // if(!$prod['freeShipping'] && $shiptpl['isfree']==1){
            //     $prod['freeShipping']=true;
            // }
        }

        $prod['catalog_id']=$proddata['product']['catalog_id'];
        $prod['catalog_name']=D('product_catalog')->where(array('id'=>$prod['catalog_id']))->getField('name');
        $prod['specs']=array();
        if($proddata['productspec']){
            foreach ($proddata['productspec'] as $key => $value) {
                if($uid!=0){
                    $shopprod=D('maker_store_product')->where(array('product_id'=>$product_id,'uid'=>$uid,'status'=>'1'))->find();
                    $shopprodspec=D('maker_store_productspec')->where(array('id'=>$shopprod['id']))->getField('group_id,inventory');
                    if(!array_key_exists($value['group_id'], $shopprodspec)){
                        continue;
                    }
                }
                $gspec=array();
                $gspec['gid']=$value['group_id'];
                $gspec['spec']=array();
                $specsplit=explode('-', $value['specgroup']);
                foreach ($specsplit as $key1 => $value1) {
                    $specvalsplit=explode('_', $value1);
                    $spec=array();
                    $spec['id']=$specvalsplit[0];
                    $spec['name']=$hsspecdef[$specvalsplit[0]];
                    $spec['value']=array();
                    $spec['value']['id']=$specvalsplit[1];
                    $spec['value']['name']=$hsspecval[$specvalsplit[1]];
                    array_push($gspec['spec'], $spec);
                }
                $gspec['hackPrice']=$value['hackprice']/100;
                $gspec['marketPrice']=$value['marketprice']/100;
                $gspec['price']=$value['price']/100;
                if($uid==0){
                    $gspec['inventory']=intval($value['inventory']);
                }else{
                    $gspec['inventory']=intval($shopprodspec[$value['group_id']]);
                }
                $gspec['image']=get_picture_path($value['linkimg'],'product','560x420');
                if($gspec['image']=='0'){
                    $gspec['image']='';
                }
                $gspec['selected']=false;
                $gspec['quantity']=0;
                $gspec['amount']=0;
                $gspec['comment']='';
                array_push($prod['specs'], $gspec);
            }
        }else{
            $gspec=array();
            $gspec['gid']=0;
            $gspec['spec']=array();
            $spec=array();
            $spec['id']=0;
            $spec['name']="";
            $spec['value']=array();
            $spec['value']['id']=0;
            $spec['value']['name']="";
            array_push($gspec['spec'], $spec);
            $gspec['hackPrice']=$proddata['product']['hackprice']/100;
            $gspec['marketPrice']=$proddata['product']['marketprice']/100;
            $gspec['price']=$proddata['product']['price']/100;
            if($uid==0){
                $gspec['inventory']=intval($proddata['product']['inventory']);
            }else{
                $shopprod=D('maker_store_product')->where(array('product_id'=>$product_id,'uid'=>$uid,'status'=>'1'))->find();
                $shopprodspec=D('maker_store_productspec')->where(array('id'=>$shopprod['id']))->getField('group_id,inventory');
                $gspec['inventory']=intval($shopprodspec[0]);
            }
            $gspec['image']="";
            $gspec['selected']=false;
            $gspec['quantity']=0;
            $gspec['amount']=0;
            $gspec['comment']='';
            array_push($prod['specs'], $gspec);
        }

        return $prod;
    }

    public function getProductDetailPrice($product_id,$group_id){
        $prodmodel = new ProductModel();
        $proddata=$prodmodel->getProductDetail($product_id);
        $pirce=array();
        if($proddata['productspec']){
            foreach ($proddata['productspec'] as $key => $value) {
                if($value['group_id']==$group_id){
                    $pirce['hackprice']=$value['hackprice']/100;
                    $pirce['price']=$value['price']/100;
                }
            }
        }else{
            $pirce['hackprice']=$proddata['product']['hackprice']/100;
            $pirce['price']=$proddata['product']['price']/100;
        }

        return $pirce;
    }

    // public function getShopProductDetail($product_id,$id){
    //     $prodmodel = new ProductModel();
    //     $proddata=$prodmodel->getProductDetail($product_id);
    //     $userapi = new UserApi;
    //     $supcompany=$userapi->getSupplierCompany($proddata['product']['uid']);
    //     $hsspecdef=$prodmodel->getHashSpecdef();
    //     $hsspecval=$prodmodel->getHashSpecval();

    //     $prod=array();
    //     $prod['id']=$proddata['product']['product_id'];
    //     $prod['status']=$proddata['product']['status'];
    //     $prod['vendor']=array('id'=>$proddata['product']['uid'],'name'=>$supcompany);
    //     $prod['name']=$proddata['product']['name'];
    //     $prod['images']=array();
    //     foreach ($proddata['productpic'] as $key => $value) {
    //         array_push($prod['images'], get_picture_path($value['linkimg'],'product','96x72'));
    //     }
    //     $prod['desc']=$proddata['product']['simcomment'];
    //     $prod['detail']=$proddata['product']['detailcomment'];
    //     $prod['freeShipping']=true;
    //     $prod['specs']=array();
    //     $shopprodspec=D('maker_store_productspec')->where(array('id'=>$id))->getField('group_id,inventory');
    //     if($proddata['productspec']){
    //         foreach ($proddata['productspec'] as $key => $value) {
    //             if(array_key_exists($value['group_id'], $shopprodspec)){
    //                 $gspec=array();
    //                 $gspec['gid']=$value['group_id'];
    //                 $gspec['spec']=array();
    //                 $specsplit=explode('-', $value['specgroup']);
    //                 foreach ($specsplit as $key1 => $value1) {
    //                     $specvalsplit=explode('_', $value1);
    //                     $spec=array();
    //                     $spec['id']=$specvalsplit[0];
    //                     $spec['name']=$hsspecdef[$specvalsplit[0]];
    //                     $spec['value']=array();
    //                     $spec['value']['id']=$specvalsplit[1];
    //                     $spec['value']['name']=$hsspecval[$specvalsplit[1]];
    //                     array_push($gspec['spec'], $spec);
    //                 }
    //                 $gspec['hackPrice']=$value['hackprice']/100;
    //                 $gspec['marketPrice']=$value['marketprice']/100;
    //                 $gspec['price']=$value['price']/100;
    //                 $gspec['inventory']=intval($shopprodspec[$value['group_id']]);
    //                 $gspec['image']=get_picture_path($value['linkimg'],'product','560x420');
    //                 if($gspec['image']=='0'){
    //                     $gspec['image']='';
    //                 }
    //                 $gspec['selected']=false;
    //                 $gspec['quantity']=0;
    //                 $gspec['amount']=0;
    //                 $gspec['comment']='';
    //                 array_push($prod['specs'], $gspec);
    //             }
    //         }
    //     }else{
    //         $gspec=array();
    //         $gspec['gid']=0;
    //         $gspec['spec']=array();
    //         $spec=array();
    //         $spec['id']=0;
    //         $spec['name']="";
    //         $spec['value']=array();
    //         $spec['value']['id']=0;
    //         $spec['value']['name']="";
    //         array_push($gspec['spec'], $spec);
    //         $gspec['hackPrice']=$proddata['product']['hackprice']/100;
    //         $gspec['marketPrice']=$proddata['product']['marketprice']/100;
    //         $gspec['price']=$proddata['product']['price']/100;
    //         $gspec['inventory']=intval($shopprodspec[0]['inventory']);
    //         $gspec['image']="";
    //         $gspec['selected']=false;
    //         $gspec['quantity']=0;
    //         $gspec['amount']=0;
    //         $gspec['comment']='';
    //         array_push($prod['specs'], $gspec);
    //     }

    //     return $prod;
    // }
    
    public function getProductSpecGroup($product_id,$uid=0){
        $prodmodel = new ProductModel();
        $specgroup=$prodmodel->getProductSpecGroup($product_id,$uid);
        $return=array();
        $return['mktprice']=($specgroup['makprice_min']/100).'-'.($specgroup['makprice_max']/100);
        if($uid==0){
            $return['price']=($specgroup['hackprice_min']/100).'-'.($specgroup['hackprice_max']/100);
        }else{
            $return['price']=($specgroup['price_min']/100).'-'.($specgroup['price_max']/100);
        }
        $return['sys_attrprice']=array();
        foreach ($specgroup['group'] as $key => $value) {
            $tmp=array();
            $tmp['group_id']=$value['group_id'];
            if($uid==0){
                $tmp['price']=$value['hackprice']/100;
            }else{
                $tmp['price']=$value['price']/100;
            }
            $tmp['mktprice']=$value['marketprice']/100;
            $tmp['imgpath']=get_picture_path($value['linkimg'],'product','560x420');
            $tmp['inventory']=$value['inventory'];
            $return['sys_attrprice'][$value['specgroup']]=$tmp;
        }
        return $return;
    } 

    public function getSupplierPorductList($uid,$prodname=''){
        $prodmodel = new ProductModel();
        $arrayprod=$prodmodel->getSupplierPorductList($uid,$prodname);
        $hsspecdef=$prodmodel->getHashSpecdef();
        $hsspecval=$prodmodel->getHashSpecval();
        $list=array();
        foreach ($arrayprod as $key => $value) {
            $productspec=D('product_spec')->where(array('product_id'=>$value['product_id'],'status'=>'1'))->order('group_id')->select();
            $prod['product_id']=$value['product_id'];
            $prod['uid']=$value['uid'];
            $prod['name']=$value['name'];
            $prod['catalog_id']=$value['catalog_id'];
            $prod['simcomment']=str_replace('|', ' ', $value['simcomment']);
            $prod['supprice']=$value['supprice']/100;
            $prod['inventory']=$value['inventory'];
            $prod['status']=$value['status'];
            switch ($prod['status']) {
                case '0':
                    $prod['statusname']='待审核';
                    break;
                case '1':
                    $prod['statusname']='正常';
                    break;
                case '2':
                    $prod['statusname']='待上架';
                    break;
                case '3':
                    $prod['statusname']='已下架';
                    break;
            }
            $prod['image']=D('product_picture')->where(array('product_id'=>$value['product_id'],'status'=>'1'))->order('sort')->getField('linkimg');
            $prod['imagepath']=get_picture_path($prod['image'],'product','96x72');
            if($productspec){
               $prod['supprice']=$prodmodel->getSupplierProductDefaultDisplayPrice($value['product_id']);
               $prod['inventory']=D('product_spec')->where(array('product_id'=>$value['product_id'],'status'=>'1'))->sum('inventory');
            }
            array_push($list, $prod);
        }
        return $list;
    }

    public function getCollectCount($product_id,$source,$shopid){
        return D('member_collection')->where(array('objid'=>$product_id,'objtype'=>'product','source'=>$source,'collect'=>1,'sourceobjid'=>$shopid))->count();
    }

    public function getFavorCount($product_id,$source,$shopid){
        return D('member_collection')->where(array('objid'=>$product_id,'objtype'=>'product','source'=>$source,'favor'=>1,'sourceobjid'=>$shopid))->count();
    }

    public function getProductSupplierID($product_id){
        return D('product')->where(array('product_id'=>$product_id))->getField('uid');
    }

    public function getProductDefaultPicture($product_id){
        $picid=D('product_picture')->where(array('product_id'=>$product_id,'status'=>'1'))->order('sort')->getField('linkimg');
        return get_picture_path($picid,'product','96x72');
    }

    public function updateProductView($product_id,$shopuid=0){
        if($shopuid==0){
            D('product')->where(array('product_id'=>$product_id))->setInc('view');
        }else{
            D('maker_store_product')->where(array('uid'=>$shopuid,'product_id'=>$product_id))->setInc('view');
        }
    }

    public function calcShippingAmount($cartlist,$addressid){
        $address=D('member_shippingaddr')->where(array('id'=>$addressid))->find();
        $sumship=array();
        foreach ($cartlist as $key => $value) {
            $sumship[$value]['isempty']=false;
            $sumship[$value]['error']='';
            $cart=D('cart')->where(array('id'=>$key))->find();
            $price=$this->getProductDetailPrice($cart['product_id'],$cart['group_id']);
            $prod=D('product')->where(array('product_id'=>$cart['product_id']))->find();
            if(array_key_exists($value, $sumship)){
                $sumship[$value]['quantity']=intval($sumship[$value]['quantity'])+intval($cart['quantity']);
                $sumship[$value]['amount']=intval($sumship[$value]['amount'])+intval($price['price']*100*$cart['quantity']);
                $sumship[$value]['weight']=$sumship[$value]['weight']+$prod['weight']*$cart['quantity'];
                $sumship[$value]['volume']=$sumship[$value]['volume']+$prod['volume']*$cart['quantity'];
            }else{
                $sumship[$value]['quantity']=$cart['quantity'];
                $sumship[$value]['amount']=$price['price']*100*$cart['quantity'];
                $sumship[$value]['weight']=$prod['weight']*$cart['quantity'];
                $sumship[$value]['volume']=$prod['volume']*$cart['quantity'];
            }
        }
        foreach ($sumship as $key => $value) {
            $sumship[$key]['weight']=ceil($sumship[$key]['weight']);
            $sumship[$key]['volume']=ceil($sumship[$key]['volume']);
            $value['weight']=$sumship[$key]['weight'];
            $value['volume']=$sumship[$key]['volume'];
            $shiptpl=D('shippingtpl')->where(array('id'=>$key))->find();
            $sumship[$key]['id']=$key;
            $sumship[$key]['name']=$shiptpl['name'];
            if($shiptpl['isfree']=='1' && $shiptpl['amount']<=0){
                $sumship[$key]['shipping']=0;
                continue;
            }elseif($shiptpl['isfree']=='1' && $shiptpl['amount']>0){
                if($value['amount']>=$shiptpl['amount']){
                    $sumship[$key]['shipping']=0;
                    continue;
                }
            }
            $map['tplid']=$shiptpl['id'];
            $map['province']=$address['province'];
            $map['mesureunit']=$shiptpl['mesureunit'];
            $map['status']=1;
            $map['pricetype']='0';
            $shipdetail0=D('shippingdetail')->where($map)->select();

            //有首重的情况，计算首重运费
            if($shipdetail0){
                $shipdetail0val='';
                foreach ($shipdetail0 as $keydtun => &$valuedtun) {
                    if($valuedtun['calvaluemax']=='-1'){
                        if($shiptpl['mesureunit']=='weight'){
                            $valuedtun['calvaluemax']=intval($value['weight'])+100;
                        }else{
                            $valuedtun['calvaluemax']=intval($value['volume'])+100;
                        }
                    }

                    if($valuedtun['district']==$address['district']){
                        if($shiptpl['mesureunit']=='weight'){
                            if($value['weight']<=$valuedtun['calvaluemax']){
                                $shipdetail0val=$valuedtun;
                                break;
                            }
                        }else{
                            if($value['volume']<=$valuedtun['calvaluemax']){
                                $shipdetail0val=$valuedtun;
                                break;
                            }
                        }
                    }else{
                        if($valuedel01['city']==$address['city']){
                            if($shiptpl['mesureunit']=='weight'){
                                if($value['weight']<=$valuedtun['calvaluemax']){
                                    $shipdetail0val=$valuedtun;
                                    break;
                                }
                            }else{
                                if($value['volume']<=$valuedtun['calvaluemax']){
                                    $shipdetail0val=$valuedtun;
                                    break;
                                }
                            }
                        }else{
                            if($shiptpl['mesureunit']=='weight'){
                                if($value['weight']<=$valuedtun['calvaluemax']){
                                    $shipdetail0val=$valuedtun;
                                    break;
                                }
                            }else{
                                if($value['volume']<=$valuedtun['calvaluemax']){
                                    $shipdetail0val=$valuedtun;
                                    break;
                                }
                            }
                        }
                    }
                }
                if(empty($shipdetail0val)){
                    $sumship[$key]['isempty']=true;
                    $sumship[$key]['error']='城市不支持配送';
                }else{
                    $sumship[$key]['shipping']=intval($sumship[$key]['shipping'])+($shipdetail0val['price']/100);
                }
            }

            //计算续重运费
            if($shiptpl['mesureunit']=='weight'){
                $map['_string']='calvaluemin<'.$value['weight'];
            }else{
                $map['_string']='calvaluemin<'.$value['volume'];
            }
            $map['pricetype']='1';
            $shipdetail1=D('shippingdetail')->where($map)->order('calvaluemin')->select();
            if($shipdetail1){
                foreach ($shipdetail1 as $keyun => &$valueun) {
                    if($valueun['calvaluemax']=='-1'){
                        if($shiptpl['mesureunit']=='weight'){
                            $valueun['calvaluemax']=intval($value['weight'])+100;
                        }else{
                            $valueun['calvaluemax']=intval($value['volume'])+100;
                        }
                    }
                    if($shiptpl['mesureunit']=='weight'){
                        if($value['weight']>$valueun['calvaluemax']){
                            unset($shipdetail1[$keyun]);
                        }
                    }else{
                        if($value['volume']>$valueun['calvaluemax']){
                            unset($shipdetail1[$keyun]);
                        }
                    }
                }
                $isexist=false;
                $detkey='';
                foreach ($shipdetail1 as $key1 => $value1) {
                    if($value1['district']==$address['district']){
                        $isexist=true;
                        $detkey=$key1;
                        break;
                    }
                }
                if($isexist){
                    if($shiptpl['mesureunit']=='weight'){
                        if($shipdetail0){
                            $weightceil=$value['weight']-1;
                        }else{
                            $weightceil=$value['weight'];
                        }
                        $outfee=$shipdetail1[$detkey]['price']*$weightceil;
                        $outfee=$outfee/100;
                        $sumship[$key]['shipping']=intval($sumship[$key]['shipping'])+$outfee;
                    }else{
                        if($shipdetail0){
                            $volumeceil=$value['volume']-1;
                        }else{
                            $volumeceil=$value['volume'];
                        }
                        $outfee=$shipdetail1[$detkey]['price']*$volumeceil;
                        $outfee=$outfee/100;
                        $sumship[$key]['shipping']=intval($sumship[$key]['shipping'])+$outfee;
                    }
                }else{
                    $detkey='';
                    foreach ($shipdetail1 as $key11 => $value11) {
                        if($value11['city']==$address['city']){
                            $isexist=true;
                            $detkey=$key11;
                            break;
                        }
                    }
                    if($isexist){
                        if($shiptpl['mesureunit']=='weight'){
                            if($shipdetail0){
                                $weightceil=$value['weight']-1;
                            }else{
                                $weightceil=$value['weight'];
                            }
                            $outfee=$shipdetail1[$detkey]['price']*$weightceil;
                            $outfee=$outfee/100;
                            $sumship[$key]['shipping']=intval($sumship[$key]['shipping'])+$outfee;
                        }else{
                            if($shipdetail0){
                                $volumeceil=$value['volume']-1;
                            }else{
                                $volumeceil=$value['volume'];
                            }
                            $outfee=$shipdetail1[$detkey]['price']*$volumeceil;
                            $outfee=$outfee/100;
                            $sumship[$key]['shipping']=intval($sumship[$key]['shipping'])+$outfee;
                        }
                    }else{
                        $detkey='';
                        foreach ($shipdetail1 as $key12 => $value12) {
                            if($value12['province']==$address['province']){
                                $isexist=true;
                                $detkey=$key12;
                                break;
                            }
                        }
                        if($isexist){
                            if($shiptpl['mesureunit']=='weight'){
                                if($shipdetail0){
                                    $weightceil=$value['weight']-1;
                                }else{
                                    $weightceil=$value['weight'];
                                }
                                $outfee=$shipdetail1[$detkey]['price']*$weightceil;
                                $outfee=$outfee/100;
                                $sumship[$key]['shipping']=intval($sumship[$key]['shipping'])+$outfee;
                            }else{
                                if($shipdetail0){
                                    $volumeceil=$value['volume']-1;
                                }else{
                                    $volumeceil=$value['volume'];
                                }
                                $outfee=$shipdetail1[$detkey]['price']*$volumeceil;
                                $outfee=$outfee/100;
                                $sumship[$key]['shipping']=intval($sumship[$key]['shipping'])+$outfee;
                            }
                        }else{
                            $sumship[$key]['isempty']=true;
                            $sumship[$key]['error']='城市不支持配送';
                        }
                    }
                }
            }else{
                if(empty($shipdetail0)){
                    if($shiptpl['mesureunit']=='weight'){
                        $map['_string']='calvaluemin>'.$value['weight'];
                    }else{
                        $map['_string']='calvaluemin>'.$value['volume'];
                    }
                    $map['pricetype']='1';
                    $firstcnt=D('shippingdetail')->where($map)->count();
                    if($firstcnt){
                        $sumship[$key]['isempty']=true;
                        $sumship[$key]['error']='未达到起订数量';
                    }else{
                        $sumship[$key]['isempty']=true;
                        $sumship[$key]['error']='城市不支持配送';
                    }
                }
            }
        }

        return $sumship;
    }

    public function getProductComments($product_id,$type,$makerid=0){
        $map['source']=$type;
        $map['product_id']=$product_id;
        if($makerid){
            $map['makerid']=$makerid;
        }
        $coms=D('product_comment')->where($map)->select();
        $rescoms=array();
        $userapi=new UserApi;
        foreach ($coms as $key => $value) {
            $tmpcom['id']=$value['id'];
            $tmpcom['body']=$value['comment'];
            $tmpuser['id']=$value['uid'];
            $user=$userapi->info($value['uid']);
            $tmpuser['name']=$user['nickname'];
            $tmpuser['headImage']=get_picture_path($user['portrait'],'portrait','150x150');
            $tmpcom['user']=$tmpuser;
            $tmpcom['date']=date('Y-m-d H:i',$value['created']);
            $tmpcom['specs']=D('orderdetail')->where(array('id'=>$value['orderdetailid']))->getField('groupname');
            array_push($rescoms, $tmpcom);
        }
        return $rescoms;
    }

    public function getProductLastComments($product_id,$type,$makerid=0){
        $map['source']=$type;
        $map['product_id']=$product_id;
        if($makerid){
            $map['makerid']=$makerid;
        }
        $coms=D('product_comment')->where($map)->order('created desc')->find();
        $userapi=new UserApi;
        $rescoms['id']=$coms['id'];
        $rescoms['body']=$coms['comment'];
        $tmpuser['id']=$coms['uid'];
        $user=$userapi->info($coms['uid']);
        $tmpuser['name']=$user['nickname'];
        $tmpuser['headImage']=get_picture_path($user['portrait'],'portrait','150x150');
        $rescoms['user']=$tmpuser;
        $rescoms['date']=date('Y-m-d H:i',$coms['created']);
        $rescoms['specs']=D('orderdetail')->where(array('id'=>$coms['orderdetailid']))->getField('groupname');

        return $rescoms;
    }

    public function getProductCommentScore($product_id,$type,$makerid=0){
        $map['source']=$type;
        $map['product_id']=$product_id;
        if($makerid){
            $map['makerid']=$makerid;
        }
        $comsum=D('product_comment')->where($map)->field('product_id,count(*) as cnt,sum(score) as score')->group('product_id')->select();

        return $comsum[0];
    }
}