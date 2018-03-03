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

class DocumentApi {

	public function getEventCategory($parentname){
		$cateparent = D('Category')->where(array('name'=>$parentname,'status'=>'1'))->find();
		if($cateparent){
			$catechild = D('Category')->where(array('status'=>'1','pid'=>$cateparent['id'],'display'=>'1'))->field('id,title,name')->order('sort')->select();
			return $catechild;
		}else{
			return array();
		}
	}

	public function getDocumentList($uid,$catagory){
		$list=array();
		foreach ($catagory as $key => $value) {
			$doc = D('Document')->where(array('category_id'=>$value['id'],'status'=>'1'))->order('category_id,id')->getField('id,title,description,create_time');
			$sign = D('event_signup')->where(array('uid'=>$uid))->getField('event_id,status');
			foreach ($doc as $key1 => &$value1) {
				if(array_key_exists($value1['id'], $sign)){
					$value1['signup']=$sign[$value1['id']];
				}else{
					$value1['signup']='0';
				}
				$value1['created']=date('Y-m-d H:i', $value1['create_time']);
			}
			
			$list[$value['id']]=$doc;
		}
		return $list;
	}

	public function getDocumentListByCate($uid,$cate){
		$cateid=D('category')->where(array('name'=>$cate))->getField('id');
		$list=array();
		$doc = D('Document')->where(array('category_id'=>$cateid,'status'=>'1'))->order('id desc')->getField('id,title,description,create_time');
		$sign = D('event_signup')->where(array('uid'=>$uid))->getField('event_id,status');
		foreach ($doc as $key1 => &$value1) {
			if(array_key_exists($value1['id'], $sign)){
				$value1['signup']=$sign[$value1['id']];
			}else{
				$value1['signup']='0';
			}
			$value1['created']=date('Y-m-d H:i', $value1['create_time']);
		}
		
		return $doc;
	}

	public function eventSignup($data){
		$sign=D('event_signup')->where(array('uid'=>$data['uid'],'event_id'=>$data['event_id']))->find();
		if($sign){
			return $sign['id'];
		}else{
			$res=D('event_signup')->add($data);
			return $res;
		}
	}

	public function getRegAgreement(){
		return D('Document')->where(array('name'=>'regagreement','status'=>'1'))->getField('id');
	}

	public function getDocCatelog($docid){
		return D('Document')->where(array('id'=>$docid))->getField('category_id');
	}

	public function getDocumentByName($docname){
		$doc=D('Document')->where(array('name'=>$docname))->find();
		$doc['content']=D('document_article')->where(array('id'=>$doc['id']))->getField('content');
		return $doc;
	}
}