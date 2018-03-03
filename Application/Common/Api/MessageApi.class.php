<?php

namespace Common\Api;
use User\Api\UserApi;

class MessageApi {

    public function getMessageList($uid,$role){
        $list=D('private_message')->where(array('to'=>$uid,'roleid'=>$role))->getField('pubmsgid',true);
        $publist=D('public_message')->where(array('roleid'=>$role,'status'=>'1'))->select();
        foreach ($publist as $key => $value) {
            if(!in_array($value['id'], $list)){
                $data['pubmsgid']=$value['id'];
                $data['comment']=$value['comment'];
                $data['title']=$value['title'];
                $data['from']=$value['from'];
                $data['to']=$uid;
                $data['type']=$value['type'];
                $data['roleid']=$value['roleid'];
                $data['status']='0';
                $data['created']=time();
                D('private_message')->add($data);
            }
        }

        $list1=D('private_message')->where(array('to'=>$uid,'roleid'=>$role,'type'=>'1'))->order('status,id')->select();
        $list2=D('private_message')->where(array('to'=>$uid,'roleid'=>$role,'type'=>'2'))->order('status,id')->select();
        $list3=D('private_message')->where(array('to'=>$uid,'roleid'=>$role,'type'=>'3'))->order('status,id')->select();
        $return=array();
        $return[1]=$list1;
        $return[2]=$list2;
        $return[3]=$list3;

        return $return;
    }

    public function getNewMessageCount($uid,$role){
        $cnt1=D('private_message')->where(array('to'=>$uid,'roleid'=>$role,'type'=>'1','status'=>'0'))->count();
        $cnt2=D('private_message')->where(array('to'=>$uid,'roleid'=>$role,'type'=>'2','status'=>'0'))->count();
        $cnt3=D('private_message')->where(array('to'=>$uid,'roleid'=>$role,'type'=>'3','status'=>'0'))->count();
        $return=array(1=>$cnt1,2=>$cnt2,3=>$cnt3);
        return $return;
    }

    public function getAppMessageList($uid){
        $list=D('private_message')->where(array('to'=>$uid))->select();
        $listkey=array();
        $userapi=new UserApi;
        foreach ($list as $key => $value) {
            if($value['roleid']==1 || $value['roleid']==2){
                if($userapi->isMaker($uid)){
                    if(array_key_exists($value['roleid'], $listkey)){
                        array_push($listkey[$value['roleid']], $value['pubmsgid']);
                    }else{
                        $listkey[$value['roleid']]=array();
                    }
                }
            }
            if($value['roleid']==4){
                if($userapi->isSupplier($uid)){
                    if(array_key_exists($value['roleid'], $listkey)){
                        array_push($listkey[$value['roleid']], $value['pubmsgid']);
                    }else{
                        $listkey[$value['roleid']]=array();
                    }
                }
            }
        }

        foreach ($listkey as $key => $value) {
            $publist=D('public_message')->where(array('roleid'=>$key,'status'=>'1'))->select();
            foreach ($publist as $key => $value) {
                if(!in_array($value['id'], $value)){
                    $data['pubmsgid']=$value['id'];
                    $data['comment']=$value['comment'];
                    $data['title']=$value['title'];
                    $data['from']=$value['from'];
                    $data['to']=$uid;
                    $data['type']=$value['type'];
                    $data['roleid']=$value['roleid'];
                    $data['status']='0';
                    $data['created']=time();
                    D('private_message')->add($data);
                }
            }
        }

        $list=D('private_message')->where(array('to'=>$uid))->field('id,title,comment,type,status,created')->order('id desc')->select();
        foreach ($list as $key => &$value) {
            $value['type']=intval($value['type']);
            $value['created']=date('Y-m-d H:i',$value['created']);
        }
        return $list;
    }

    public function getAppNewMessageCount($uid,$role){
        $cnt1=D('private_message')->where(array('to'=>$uid,'roleid'=>$role,'type'=>'1','status'=>'0'))->count();
        $cnt2=D('private_message')->where(array('to'=>$uid,'roleid'=>$role,'type'=>'2','status'=>'0'))->count();
        $cnt3=D('private_message')->where(array('to'=>$uid,'roleid'=>$role,'type'=>'3','status'=>'0'))->count();
        $return=array(1=>$cnt1,2=>$cnt2,3=>$cnt3);
        return $return;
    }

    public function readGetMessageByID($id){
        $msg=D('private_message')->where(array('id'=>$id))->field('id,title,comment,type,status,created')->find();
        if($msg['status']=='0'){
            D('private_message')->where(array('id'=>$id))->setField('status',1);
            $msg['status']='1';
        }
        return $msg;
    }
}