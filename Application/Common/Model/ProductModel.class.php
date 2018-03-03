<?php

namespace Common\Model;
use Think\Model;
use Common\Api\CategoryApi;
/**
 * 会员模型
 */
class ProductModel extends Model{
	/* 用户模型自动验证 */
	protected $_validate = array(
		array('name', 'require', '商品名称必须填写'),
		array('uid', 'require', '未登录'),
		array('catalog_id', 'require', '获取分类错误'),
	);

	/* 用户模型自动完成 */
	protected $_auto = array(
        array('status', 0, self::MODEL_INSERT),
        array('created', NOW_TIME, self::MODEL_INSERT),
	);

	public function productInsert($data){
		$prodpic=D('product_picture');
		$prodspec=D('product_spec');
		if($this->create($data['product'])){
			$newprodid = $this->add();

			//添加商品主图
			$datappic=array();
			foreach ($data['mainpic'] as $key => $value) {
				$tmpppic['product_id']=$newprodid;
				$tmpppic['linkimg']=$value;
				$tmpppic['uid']=$data['product']['uid'];
				$tmpppic['pcid']=0;
				$tmpppic['imgpos']='';
				$tmpppic['sort']=$key;
				$tmpppic['status']='1';
				array_push($datappic, $tmpppic);
			}
			if($datappic){
				$prodpic->addAll($datappic);
			}
			
			//添加商品规格
			$datapspec=array();
			foreach ($data['prodspecgroup'] as $key => $value) {
				$tmppspec['product_id']=$newprodid;
				$tmppspec['specgroup']=$value;
				$tmppspec['supprice']=$data['sprice'][$key];
				$tmppspec['inventory']=$data['sinventory'][$key];
				$tmppspec['linkimg']=$data['prodspid'][$key];
				$tmppspec['status']='1';
				array_push($datapspec, $tmppspec);
			}
			if($datapspec){
				$prodspec->addAll($datapspec);
			}

			return $newprodid;
		} else {
          return false;
		}
	}

	public function productEdit($data){
		$prodpic=D('product_picture');
		$prodspec=D('product_spec');
		$prod=$this->where(array('product_id'=>$data['product']['product_id']))->find();
		if($prod){
			$data['product']['status']=0;
			$this->where(array('product_id'=>$data['product']['product_id']))->save($data['product']);

			$prodpic->where(array('product_id'=>$data['product']['product_id']))->setField('status',-1);
			//添加商品主图
			$datappic=array();
			foreach ($data['mainpic'] as $key => $value) {
				$tmpppic['product_id']=$prod['product_id'];
				$tmpppic['linkimg']=$value;
				$tmpppic['uid']=$prod['uid'];
				$tmpppic['pcid']=0;
				$tmpppic['imgpos']='';
				$tmpppic['sort']=$key;
				$tmpppic['status']='1';
				array_push($datappic, $tmpppic);
			}
			if($datappic){
				$prodpic->addAll($datappic);
			}
			
			$prodspec->where(array('product_id'=>$data['product']['product_id']))->setField('status',-1);
			//添加商品规格
			$datapspec=array();
			foreach ($data['prodspecgroup'] as $key => $value) {
				$tmppspec['product_id']=$prod['product_id'];
				$tmppspec['specgroup']=$value;
				$tmppspec['supprice']=$data['sprice'][$key];
				$tmppspec['inventory']=$data['sinventory'][$key];
				$tmppspec['linkimg']=$data['prodspid'][$key];
				$tmppspec['status']='1';
				array_push($datapspec, $tmppspec);
			}
			if($datapspec){
				$prodspec->addAll($datapspec);
			}

			return $prod['product_id'];
		} else {
			$this->error='商品不存在，请重新选择商品修改';
          	return false;
		}
	}

	public function custSpecval($data){
		$specval=D('product_specval');
		$custcnt=$specval->where(array('uid'=>$data['uid'],'sid'=>$data['sid']))->count();
		$data['sort']=$custcnt+1;
		$data['status']='1';
		if($specval->create($data)){
			$result = $specval->add($data);
			return $result;
		} else {
          return 0;
		}
	}

	public function getSupplierPorductList($uid,$prodname=''){
		$map['status']=array('neq','-1');
		$map['uid']=$uid;
		if(!empty($prodname)){
			$map['name']=array('like','%'.$prodname.'%');
		}
		return $list=$this->where($map)->order('created desc')->select();
	}

	public function getStorePorductList($part='',$cate=0){
        $cateapi=new CategoryApi;
		$list=array();
		if($part==''&&$cate==0){
			$list=$this->where(array('status'=>'1','producttype'=>'0'))->order('created desc')->getField('product_id',true);
		}elseif($part!=''&&$cate==0){
			$list=$this->where(array('status'=>'1','productpart'=>$part,'producttype'=>'0'))->order('created desc')->getField('product_id',true);
		}elseif($part==''&&$cate!=0){
			$tmp=$this->where(array('status'=>'1','producttype'=>'0'))->field('catalog_id,product_id')->order('created desc')->select();
	        $catelvl=$cateapi->getCatalogByID($cate);
	        $allcatelist=$cateapi->getCatalogAllList();
	        $list=array();
	        if(count($catelvl)==1){
	        	$lvl2list=$allcatelist[$cate];
	        	foreach ($lvl2list as $key => $value) {
	        		$lvl3list=$allcatelist[$value];
	        		foreach ($tmp as $key1 => $value1) {
	        			if(in_array($value1['catalog_id'], $lvl3list)){
	        				array_push($list, $value1['product_id']);
	        			}
	        		}
	        	}
	        }elseif(count($catelvl)==2){
        		$lvl3list=$allcatelist[$cate];
        		foreach ($tmp as $key1 => $value1) {
        			if(in_array($value1['catalog_id'], $lvl3list)){
        				array_push($list, $value1['product_id']);
        			}
        		}
	        }else{
	        	$list=$this->where(array('status'=>'1','catalog_id'=>$cate, 'producttype'=>'0'))->order('created desc')->getField('product_id',true);
	        }
		}else{
			$tmp=$this->where(array('status'=>'1','productpart'=>$part, 'producttype'=>'0'))->field('catalog_id,product_id')->order('created desc')->select();
	        $catelvl=$cateapi->getCatalogByID($cate);
	        $allcatelist=$cateapi->getCatalogAllList();
	        $list=array();
	        if(count($catelvl)==1){
	        	$lvl2list=$allcatelist[$cate];
	        	foreach ($lvl2list as $key => $value) {
	        		$lvl3list=$allcatelist[$value];
	        		foreach ($tmp as $key1 => $value1) {
	        			if(in_array($value1['catalog_id'], $lvl3list)){
	        				array_push($list, $value1['product_id']);
	        			}
	        		}
	        	}
	        }elseif(count($catelvl)==2){
        		$lvl3list=$allcatelist[$cate];
        		foreach ($tmp as $key1 => $value1) {
        			if(in_array($value1['catalog_id'], $lvl3list)){
        				array_push($list, $value1['product_id']);
        			}
        		}
	        }else{
	        	$list=$this->where(array('status'=>'1','catalog_id'=>$cate, 'productpart'=>$part,'producttype'=>'0'))->order('created desc')->getField('product_id',true);
	        }
		}
		return $list;
	}

	public function getProductDetail($product_id){
		$return=array();
		$return['product']=$this->where(array('product_id'=>$product_id))->find();
		$return['productpic']=D('product_picture')->where(array('product_id'=>$product_id,'status'=>'1'))->order('sort')->select();
		$return['productspec']=D('product_spec')->where(array('product_id'=>$product_id,'status'=>'1'))->order('group_id')->select();
		return $return;
	}

	public function getProductSpecGroup($product_id,$uid){
		$return=array();
		if($uid==0){
			$return['group']=D('product_spec')->where(array('product_id'=>$product_id,'status'=>'1'))->order('group_id')->select();
			$return['makprice_min']=D('product_spec')->where(array('product_id'=>$product_id,'status'=>'1'))->min('marketprice');
			$return['makprice_max']=D('product_spec')->where(array('product_id'=>$product_id,'status'=>'1'))->max('marketprice');
			$return['hackprice_min']=D('product_spec')->where(array('product_id'=>$product_id,'status'=>'1'))->min('hackprice');
			$return['hackprice_max']=D('product_spec')->where(array('product_id'=>$product_id,'status'=>'1'))->max('hackprice');
		}else{
			$shopprodid=D('maker_store_product')->where(array('uid'=>$uid,'product_id'=>$product_id,'status'=>'1'))->find();
			$strgroupid=D('maker_store_productspec')->where(array('id'=>$shopprodid['id']))->getField('group_id',true);
			$shopprodgroup=D('maker_store_productspec')->where(array('id'=>$shopprodid['id']))->getField('group_id,inventory');
			$map['product_id']=$product_id;
			$map['status']='1';
			$map['group_id']=array('in',$strgroupid);
			$return['group']=D('product_spec')->where($map)->order('group_id')->select();
			foreach ($return['group'] as $key => &$value) {
				$value['inventory']=$shopprodgroup[$value['group_id']];
			}
			$return['makprice_min']=D('product_spec')->where($map)->min('marketprice');
			$return['makprice_max']=D('product_spec')->where($map)->max('marketprice');
			$return['price_min']=D('product_spec')->where($map)->min('price');
			$return['price_max']=D('product_spec')->where($map)->max('price');
		}

		return $return;
	}

	public function getHashSpecdef(){
		return D('product_specdef')->getField('sid,name');
	}

	public function getHashSpecval(){
		return D('product_specval')->getField('vid,name');
	}

	public function getProductDefaultDisplayPrice($product_id,$uid=0){
		if($uid==0){
			$makprice_min=D('product_spec')->where(array('product_id'=>$product_id,'status'=>'1'))->min('marketprice');
			$makprice_max=D('product_spec')->where(array('product_id'=>$product_id,'status'=>'1'))->max('marketprice');
			$hackprice_min=D('product_spec')->where(array('product_id'=>$product_id,'status'=>'1'))->min('hackprice');
			$hackprice_max=D('product_spec')->where(array('product_id'=>$product_id,'status'=>'1'))->max('hackprice');
			$price_min=D('product_spec')->where(array('product_id'=>$product_id,'status'=>'1'))->min('price');
			$price_max=D('product_spec')->where(array('product_id'=>$product_id,'status'=>'1'))->max('price');
		}else{
			$shopprodid=D('maker_store_product')->where(array('uid'=>$uid,'product_id'=>$product_id,'status'=>'1'))->find();
			$strgroupid=D('maker_store_productspec')->where(array('id'=>$shopprodid['id']))->getField('group_id');
			$map['product_id']=$product_id;
			$map['status']='1';
			$map['group_id']=array('in',$strgroupid);
			$makprice_min=D('product_spec')->where($map)->min('marketprice');
			$makprice_max=D('product_spec')->where($map)->max('marketprice');
			$hackprice_min=D('product_spec')->where($map)->min('hackprice');
			$hackprice_max=D('product_spec')->where($map)->max('hackprice');
			$price_min=D('product_spec')->where($map)->min('price');
			$price_max=D('product_spec')->where($map)->max('price');
		}

		if($makprice_min==$makprice_max){
			$return['marketprice']=$makprice_min/100;
		}else{
			$return['marketprice']=($makprice_min/100).'-'.($makprice_max/100);
		}
		if($hackprice_min==$hackprice_max){
			$return['hackprice']=$hackprice_min/100;
		}else{
			$return['hackprice']=($hackprice_min/100).'-'.($hackprice_max/100);
		}
		if($price_min==$price_max){
			$return['price']=$price_min/100;
		}else{
			$return['price']=($price_min/100).'-'.($price_max/100);
		}
        return $return;
	}

	public function getSupplierProductDefaultDisplayPrice($product_id){
		if($uid==0){
			$supprice_min=D('product_spec')->where(array('product_id'=>$product_id,'status'=>'1'))->min('supprice');
			$supprice_max=D('product_spec')->where(array('product_id'=>$product_id,'status'=>'1'))->max('supprice');
		}

		if($supprice_min==$supprice_max){
			$supprice=$supprice_min/100;
		}else{
			$supprice=($supprice_min/100).'-'.($supprice_max/100);
		}
        return $supprice;
	}

	public function getProduct3LevelCatalog($product_id){
		$cateid=$this->where(array('product_id'=>$product_id))->getField('catalog_id');
		$cate=D('product_catalog')->where(array('id'=>$cateid))->find();
		return $cate;
	}

	public function productIsError($product_id){
		$res=$this->where(array('product_id'=>$product_id))->getField('status');
		if($res=='-1'){
			return true;
		}else{
			$specs=D('product_spec')->where(array('product_id'=>$product_id,'status'=>'1'))->select();
			$specdef=D('product_specdef')->where(array('status'=>'1'))->getField('sid',true);
			foreach ($specs as $key => $value) {
				if($value['hackprice'])
				$spec=explode('-', $value);
				foreach ($spec as $key1 => $value1) {
					$specsv=explode('_', $value1);
					if(!in_array($specsv[0], $specdef)){
						return true;
					}
					$specval=D('product_specval')->where(array('sid'=>$specsv[0],'status'=>'1'))->getField('vid',true);
					if(!in_array($specsv[1], $specval)){
						return true;
					}
				}
			}
			$validspecs=D('product_spec')->where(array('product_id'=>$product_id,'status'=>'1'))->getField('group_id',true);
			$storeprod=D('maker_store_product')->where(array('product_id'=>$product_id,'status'=>'1'))->select();
			foreach ($storeprod as $key => $value) {
				$specs=D('maker_store_productspec')->where(array('id'=>$value['id']))->select();
				foreach ($specs as $key1 => $value1) {
					if($value1['group_id']!='0'){
						if(!in_array($value1['group_id'], $validspecs)){
							return true;
						}
					}
				}
			}
		}
	}
}
