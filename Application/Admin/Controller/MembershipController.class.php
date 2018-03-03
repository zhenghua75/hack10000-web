<?php

namespace Admin\Controller;
use Common\Model\PictureModel;

/**
 * 后台用户控制器
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
class MembershipController extends AdminController {

    /**
     * 用户管理首页
     * @author 麦当苗儿 <zuojiazi@vip.qq.com>
     */
    public function index(){
        $nickname       =   I('nickname');
        $mobile=I('mobile');
        $map['status']  =   array('egt',0);
        if($nickname!=''){
            if(is_numeric($nickname)){
                $map['uid']= intval($nickname);
            }else{
                $map['nickname']    =   array('like', '%'.(string)$nickname.'%');
                if($mobile!=''){
                    $map['mobile']    =   array('like', '%'.(string)$mobile.'%');
                }
            }
        }else{
            if($mobile!=''){
                $map['mobile']    =   array('like', '%'.(string)$mobile.'%');
            }
        }
        if(!array_key_exists('uid', $map)){
            $map['uid']=array('neq','1');
        }

        $list   = $this->lists('Member', $map);
        foreach ($list as $key => $value) {
            $role=D('member_role')->where(array('uid'=>$value['uid'],'status'=>'1'))->select();
            $idname='普通';
            foreach ($role as $key1 => $value1) {
                switch ($value1['roleid']) {
                    case '1':
                        $idname=$idname.'|个人创客';
                        break;
                    case '2':
                        $idname=$idname.'|机构创客';
                        break;
                    case '3':
                        $idname=$idname.'|创客供应商';
                        break;
                    case '4':
                        $idname=$idname.'|供应商';
                        break;
                }
            }
            $list[$key]['idname']=$idname;
        }
        int_to_string($list);
        $this->assign('_list', $list);
        $this->meta_title = '用户信息';
        $this->display();
    }

    /**
     * 会员状态修改
     * @author 朱亚杰 <zhuyajie@topthink.net>
     */
    public function changeStatus($method=null){
        $id = array_unique((array)I('id',0));
        if( in_array(C('USER_ADMINISTRATOR'), $id)){
            $this->error("不允许对超级管理员执行该操作!");
        }
        $id = is_array($id) ? implode(',',$id) : $id;
        if ( empty($id) ) {
            $this->error('请选择要操作的数据!');
        }
        $map['uid'] =   array('in',$id);
        switch ( strtolower($method) ){
            case 'forbiduser':
                $this->forbid('Member', $map );
                break;
            case 'resumeuser':
                $this->resume('Member', $map );
                break;
            case 'deleteuser':
                $this->delete('Member', $map );
                break;
            default:
                $this->error('参数非法');
        }
    }

    /**
     * 获取用户注册错误信息
     * @param  integer $code 错误编码
     * @return string        错误信息
     */
    private function showRegError($code = 0){
        switch ($code) {
            case -1:  $error = '用户名长度必须在16个字符以内！'; break;
            case -2:  $error = '用户名被禁止注册！'; break;
            case -3:  $error = '用户名被占用！'; break;
            case -4:  $error = '密码长度必须在6-30个字符之间！'; break;
            case -5:  $error = '邮箱格式不正确！'; break;
            case -6:  $error = '邮箱长度必须在1-32个字符之间！'; break;
            case -7:  $error = '邮箱被禁止注册！'; break;
            case -8:  $error = '邮箱被占用！'; break;
            case -9:  $error = '手机格式不正确！'; break;
            case -10: $error = '手机被禁止注册！'; break;
            case -11: $error = '手机号被占用！'; break;
            default:  $error = '未知错误';
        }
        return $error;
    }

    public function maker(){
        $truename=I('truename');
        $mobile=I('mobile');
        $maprole['status']  =   array('egt',0);
        $maprole['roleid']  =   1;
        $maprole['uid']  =  array('neq',1);
        $model=M('member_role');
        if($truename==''){
            if($mobile==''){
                $total=$model->where($maprole)->count();
                $listRows = C('LIST_ROWS') > 0 ? C('LIST_ROWS') : 10;
                $page = new \Think\Page($total, $listRows);
                if($total>$listRows){
                    $page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
                }
                $p =$page->show();
                $this->assign('_page', $p? $p: '');
                $this->assign('_total',$total);
                $options['limit'] = $page->firstRow.','.$page->listRows;
                $model->setProperty('options',$options);
                $list=$model->where($maprole)->select();
            }else{
                $map['mobile']=array('like','%'.$mobile.'%');
                $map['uid']  =  array('neq',1);
                $uid=M('member')->where($map)->getField('uid',true);
                if($uid){
                    $maprole['uid']=array('in',$uid);
                    $total=$model->where($maprole)->count();
                    $listRows = C('LIST_ROWS') > 0 ? C('LIST_ROWS') : 10;
                    $page = new \Think\Page($total, $listRows);
                    if($total>$listRows){
                        $page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
                    }
                    $p =$page->show();
                    $this->assign('_page', $p? $p: '');
                    $this->assign('_total',$total);
                    $options['limit'] = $page->firstRow.','.$page->listRows;
                    $model->setProperty('options',$options);
                    $list=$model->where($maprole)->select();
                }else{
                    $list=array();
                }
            }
        }else{
            if(is_numeric($truename)){
                $maprole['uid']=intval($truename);
                $list=$model->where($maprole)->select();
            }else{
                $map['name']='truename';
                $map['value']=array('like', '%'.(string)$truename.'%');
                $map['status']='1';
                $map['uid']  =  array('neq',1);
                $uid=M('member_info')->where($map)->getField('uid',true);
                if($mobile!=''){
                    $map['mobile']=array('like','%'.$mobile.'%');
                    $map['uid']  =  array('neq',1);
                    $uidmem=M('member')->where($map)->getField('uid',true);
                    $uidfind=array();
                    foreach ($uid as $key => $value) {
                        foreach ($uidmem as $key1 => $value1) {
                            if($value==$value1){
                                array_push($uidfind, $value);
                            }
                        }
                    }
                }else{
                    $uidfind=$uid;
                }
                if($uidfind){
                    $maprole['uid']=array('in',$uidfind);
                    $total=$model->where($maprole)->count();
                    $listRows = C('LIST_ROWS') > 0 ? C('LIST_ROWS') : 10;
                    $page = new \Think\Page($total, $listRows);
                    if($total>$listRows){
                        $page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
                    }
                    $p =$page->show();
                    $this->assign('_page', $p? $p: '');
                    $this->assign('_total',$total);
                    $options['limit'] = $page->firstRow.','.$page->listRows;
                    $model->setProperty('options',$options);
                    $list=$model->where($maprole)->select();
                }else{
                    $list=array();
                }
            }
        }
        foreach ($list as $key => $value) {
            $map=array();
            $map['uid']=$value['uid'];
            $map['roleid']=$value['roleid'];
            $map['status']='1';
            $info=M('member_info')->where($map)->getField('name,value');
            $list[$key]['truename']=$info['truename'];
            $list[$key]['idno']=$info['idno'];
            $list[$key]['schoolname']=$info['schoolname'];
            $list[$key]['province']=$info['province'];
            $list[$key]['city']=$info['city'];
            $list[$key]['district']=$info['district'];
            $list[$key]['studentid']=$info['studentid'];
            $list[$key]['qq']=$info['qq'];
            $meminfo=M('member')->where(array('uid'=>$value['uid']))->field('hcoin,mobile')->find();
            $list[$key]['hcoin']=$meminfo['hcoin']/100;
            $list[$key]['mobile']=$meminfo['mobile'];
        }
        int_to_string($list);
        foreach ($list as $key => $value) {
            $list[$key]['area']=$value['province'].$value['city'].$value['district'];
            switch ($value['status']) {
                case '0':
                    $list[$key]['status_text']="已接收申请";
                    break;
                case '100':
                    $list[$key]['status_text']="审核信息真实性";
                    break;
                case '101':
                    $list[$key]['status_text']="授权";
                    break;
                case '1':
                    $list[$key]['status_text']="审核通过";
                    break;
                case '2':
                    $list[$key]['status_text']="审核拒绝";
                    break;
                default:
                    $list[$key]['status_text']="禁用";
                    break;
            }
        }
        $this->assign('_list', $list);
        $this->meta_title = '个人创客信息';
        $this->display();
    }

    public function makerChangeStatus(){
        $uid = array_unique((array)I('id',0));
        $status = I('status');
        $id = is_array($uid) ? implode(',',$uid) : $uid;
        if ( empty($id) ) {
            $this->error('请选择要操作的数据!');
        }
        $map['uid'] =   array('in',$id);
        $map['roleid'] = 1;
        $res=D('member_role')->where($map)->setField(array('status'=>$status,'updated'=>time()));
        if($res && $status=='1'){
            $isreg=D('hcoin_record')->where(array('uid'=>array('in',$id),'type'=>'REG'))->count();
            if($isreg){
                $charge=0;
            }else{
                $registcoin=C('REGISTHCOIN');
                $begin=strtotime($registcoin['begin']);
                $end=strtotime($registcoin['end']);
                $today=strtotime(date('Y-m-d 00:00:00'));
                $diff=floor(($today-$begin)/86400);
                if($end>$today){
                    $charge=$registcoin['base']-($diff*$registcoin['redius']);
                    if($charge<0){
                        $charge=0;
                    }
                }else{
                    $charge=0;
                }
            }
            $charge=$charge*100;
            if($charge>0){
                if(is_array($uid)){
                    for ($i=0;$i<count($uid);$i++) {
                        D('member')->where(array('uid'=>$uid[$i]))->setInc('hcoin',$charge);
                        $data=array();
                        $data['uid']=$uid[$i];
                        $data['type']='REG';
                        $data['charge']=$charge;
                        $data['comments']='申请创客赠送';
                        $data['created']=time();
                        D('hcoin_record')->add($data);
                    }
                }else{
                    D('member')->where(array('uid'=>$id))->setInc('hcoin',$charge);
                    $data=array();
                    $data['uid']=$id;
                    $data['type']='REG';
                    $data['charge']=$charge;
                    $data['comments']='申请创客赠送';
                    $data['created']=time();
                    D('hcoin_record')->add($data);
                }
            }
        }
        $msg = array_merge( array( 'success'=>'操作成功！', 'error'=>'操作失败！', 'url'=>'' ,'ajax'=>IS_AJAX) , (array)$msg );
        if($res) {
            $this->success($msg['success'],$msg['url'],$msg['ajax']);
        }else{
            $this->error($msg['error'],$msg['url'],$msg['ajax']);
        }
    }

    public function makerorg(){
        $company       =   I('company');
        $mobile=I('mobile');
        $maprole['status']  =   array('egt',0);
        $maprole['roleid']  =   2;
        $maprole['uid']  =  array('neq',1);
        $model=M('member_role');
        if($company==''){
            if($mobile==''){
                $total=$model->where($maprole)->count();
                $listRows = C('LIST_ROWS') > 0 ? C('LIST_ROWS') : 10;
                $page = new \Think\Page($total, $listRows);
                if($total>$listRows){
                    $page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
                }
                $p =$page->show();
                $this->assign('_page', $p? $p: '');
                $this->assign('_total',$total);
                $options['limit'] = $page->firstRow.','.$page->listRows;
                $model->setProperty('options',$options);
                $list=$model->where($maprole)->select();
            }else{
                $map['mobile']=array('like','%'.$mobile.'%');
                $map['uid']  =  array('neq',1);
                $uid=M('member')->where($map)->getField('uid',true);
                if($uid){
                    $maprole['uid']=array('in',$uid);
                    $total=$model->where($maprole)->count();
                    $listRows = C('LIST_ROWS') > 0 ? C('LIST_ROWS') : 10;
                    $page = new \Think\Page($total, $listRows);
                    if($total>$listRows){
                        $page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
                    }
                    $p =$page->show();
                    $this->assign('_page', $p? $p: '');
                    $this->assign('_total',$total);
                    $options['limit'] = $page->firstRow.','.$page->listRows;
                    $model->setProperty('options',$options);
                    $list=$model->where($maprole)->select();
                }else{
                    $list=array();
                }
            }
        }else{
            if(is_numeric($company)){
                $maprole['uid']=intval($company);
                $list=$model->where($maprole)->select();
            }else{
                $map['name']='company';
                $map['value']=array('like', '%'.(string)$company.'%');
                $map['status']='1';
                $map['uid']  =  array('neq',1);
                $uid=M('member_info')->where($map)->getField('uid');
                if($mobile!=''){
                    $map['mobile']=array('like','%'.$mobile.'%');
                    $map['uid']  =  array('neq',1);
                    $uidmem=M('member')->where($map)->getField('uid',true);
                    $uidfind=array();
                    foreach ($uid as $key => $value) {
                        foreach ($uidmem as $key1 => $value1) {
                            if($value==$value1){
                                array_push($uidfind, $value);
                            }
                        }
                    }
                }else{
                    $uidfind=$uid;
                }

                if($uidfind){
                    $maprole['uid']=array('in',$uidfind);
                    $total=$model->where($maprole)->count();
                    $listRows = C('LIST_ROWS') > 0 ? C('LIST_ROWS') : 10;
                    $page = new \Think\Page($total, $listRows);
                    if($total>$listRows){
                        $page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
                    }
                    $p =$page->show();
                    $this->assign('_page', $p? $p: '');
                    $this->assign('_total',$total);
                    $options['limit'] = $page->firstRow.','.$page->listRows;
                    $model->setProperty('options',$options);
                    $list=$model->where($maprole)->select();
                }else{
                    $list=array();
                }
            }
        }
        foreach ($list as $key => $value) {
            $map=array();
            $map['uid']=$value['uid'];
            $map['roleid']=$value['roleid'];
            $map['status']='1';
            $info=M('member_info')->where($map)->getField('name,value');
            $list[$key]['company']=$info['company'];
            $list[$key]['province']=$info['province'];
            $list[$key]['city']=$info['city'];
            $list[$key]['district']=$info['district'];
            $list[$key]['busilicense']=$info['busilicense'];
            $list[$key]['busilicpid']=$info['busilicpid'];
            $list[$key]['orgpid']=$info['orgpid'];
            $list[$key]['banklicpid']=$info['banklicpid'];
            $list[$key]['backno']=$info['backno'];
            $list[$key]['corporatename']=$info['corporatename'];
            $list[$key]['corporateidno']=$info['corporateidno'];
            $list[$key]['linkname']=$info['linkname'];
            $list[$key]['linkphone']=$info['linkphone'];
            $meminfo=M('member')->where(array('uid'=>$value['uid']))->field('hcoin,mobile')->find();
            $list[$key]['hcoin']=$meminfo['hcoin']/100;
            $list[$key]['mobile']=$meminfo['mobile'];
        }
        int_to_string($list);
        foreach ($list as $key => $value) {
            $list[$key]['area']=$value['province'].$value['city'].$value['district'];
            $list[$key]['busilicpath']=get_picture_path($value['busilicpid'],'makerorg','org');
            $list[$key]['orgpath']=get_picture_path($value['orgpid'],'makerorg','org');
            $list[$key]['banklicpath']=get_picture_path($value['banklicpid'],'makerorg','org');
            switch ($value['status']) {
                case '0':
                    $list[$key]['status_text']="已接收申请";
                    break;
                case '100':
                    $list[$key]['status_text']="审核信息真实性";
                    break;
                case '101':
                    $list[$key]['status_text']="授权";
                    break;
                case '1':
                    $list[$key]['status_text']="审核通过";
                    break;
                case '2':
                    $list[$key]['status_text']="审核拒绝";
                    break;
                default:
                    $list[$key]['status_text']="禁用";
                    break;
            }
        }
        $this->assign('_list', $list);
        $this->meta_title = '机构创客信息';
        $this->display();
    }

    public function makerorgChangeStatus(){
        $id = array_unique((array)I('id',0));
        $status = I('status');
        $id = is_array($id) ? implode(',',$id) : $id;
        if ( empty($id) ) {
            $this->error('请选择要操作的数据!');
        }
        $map['uid'] =   array('in',$id);
        $map['roleid'] =  2;
        $res=D('member_role')->where($map)->setField(array('status'=>$status,'updated'=>time()));
        if($res && $status=='1'){
            $registcoin=C('REGISTHCOIN');
            $begin=strtotime($registcoin['begin']);
            $end=strtotime($registcoin['end']);
            $today=strtotime(date('Y-m-d 00:00:00'));
            $diff=floor(($today-$begin)/86400);
            if($end>$today){
                $charge=$registcoin['base']-($diff*$registcoin['redius']);
                if($charge<0){
                    $charge=0;
                }
            }else{
                $charge=0;
            }
            $charge=$charge*100;
            if(is_array($uid)){
                for ($i=0;$i<count($uid);$i++) {
                    D('member')->where(array('uid'=>$uid[$i]))->setInc('hcoin',$charge);
                    $data=array();
                    $data['uid']=$uid[$i];
                    $data['type']='REG';
                    $data['charge']=$charge;
                    $data['comments']='申请创客赠送';
                    $data['created']=time();
                    D('hcoin_record')->add($data);
                }
            }else{
                D('member')->where(array('uid'=>$id))->setInc('hcoin',$charge);
                $data=array();
                $data['uid']=$id;
                $data['type']='REG';
                $data['charge']=$charge;
                $data['comments']='申请创客赠送';
                $data['created']=time();
                D('hcoin_record')->add($data);
            }
        }
        $msg = array_merge( array( 'success'=>'操作成功！', 'error'=>'操作失败！', 'url'=>'' ,'ajax'=>IS_AJAX) , (array)$msg );
        if($res) {
            $this->success($msg['success'],$msg['url'],$msg['ajax']);
        }else{
            $this->error($msg['error'],$msg['url'],$msg['ajax']);
        }
    }

    public function supplier(){
        $company       =   I('company');
        $mobile=I('mobile');
        $maprole['status']  =   array('egt',0);
        $maprole['roleid']  =   4;
        $maprole['uid']  =  array('neq',1);
        $model=M('member_role');
        if($company==''){
            if($mobile==''){
                $total=$model->where($maprole)->count();
                $listRows = C('LIST_ROWS') > 0 ? C('LIST_ROWS') : 10;
                $page = new \Think\Page($total, $listRows);
                if($total>$listRows){
                    $page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
                }
                $p =$page->show();
                $this->assign('_page', $p? $p: '');
                $this->assign('_total',$total);
                $options['limit'] = $page->firstRow.','.$page->listRows;
                $model->setProperty('options',$options);
                $list=$model->where($maprole)->select();
            }else{
                $map['mobile']=array('like','%'.$mobile.'%');
                $map['uid']  =  array('neq',1);
                $uid=M('member')->where($map)->getField('uid',true);
                if($uid){
                    $maprole['uid']=array('in',$uid);
                    $total=$model->where($maprole)->count();
                    $listRows = C('LIST_ROWS') > 0 ? C('LIST_ROWS') : 10;
                    $page = new \Think\Page($total, $listRows);
                    if($total>$listRows){
                        $page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
                    }
                    $p =$page->show();
                    $this->assign('_page', $p? $p: '');
                    $this->assign('_total',$total);
                    $options['limit'] = $page->firstRow.','.$page->listRows;
                    $model->setProperty('options',$options);
                    $list=$model->where($maprole)->select();
                }else{
                    $list=array();
                }
            }
        }else{
            if(is_numeric($company)){
                $maprole['uid']=intval($company);
                $list=$model->where($maprole)->select();
            }else{
                $map['name']='company';
                $map['value']=array('like', '%'.(string)$company.'%');
                $map['status']='1';
                $uid=M('member_info')->where($map)->getField('uid');
                if($mobile!=''){
                    $map['mobile']=array('like','%'.$mobile.'%');
                    $map['uid']  =  array('neq',1);
                    $uidmem=M('member')->where($map)->getField('uid',true);
                    $uidfind=array();
                    foreach ($uid as $key => $value) {
                        foreach ($uidmem as $key1 => $value1) {
                            if($value==$value1){
                                array_push($uidfind, $value);
                            }
                        }
                    }
                }else{
                    $uidfind=$uid;
                }
                if($uidfind){
                    $maprole['uid']=array('in',$uidfind);
                    $total=$model->where($maprole)->count();
                    $listRows = C('LIST_ROWS') > 0 ? C('LIST_ROWS') : 10;
                    $page = new \Think\Page($total, $listRows);
                    if($total>$listRows){
                        $page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
                    }
                    $p =$page->show();
                    $this->assign('_page', $p? $p: '');
                    $this->assign('_total',$total);
                    $options['limit'] = $page->firstRow.','.$page->listRows;
                    $model->setProperty('options',$options);
                    $list=$model->where($maprole)->select();
                }else{
                    $list=array();
                }
            }
        }
        foreach ($list as $key => $value) {
            $map=array();
            $map['uid']=$value['uid'];
            $map['roleid']=$value['roleid'];
            $map['status']='1';
            $info=M('member_info')->where($map)->getField('name,value');
            $list[$key]['company']=$info['company'];
            $list[$key]['province']=$info['province'];
            $list[$key]['city']=$info['city'];
            $list[$key]['district']=$info['district'];
            $list[$key]['busilicense']=$info['busilicense'];
            $list[$key]['busilicpid']=$info['busilicpid'];
            $list[$key]['orgpid']=$info['orgpid'];
            $list[$key]['banklicpid']=$info['banklicpid'];
            $list[$key]['backno']=$info['backno'];
            $list[$key]['corporatename']=$info['corporatename'];
            $list[$key]['corporateidno']=$info['corporateidno'];
            $list[$key]['linkname']=$info['linkname'];
            $list[$key]['linkphone']=$info['linkphone'];
            $memmobile=M('member')->where(array('uid'=>$value['uid']))->getField('mobile');
            $list[$key]['mobile']=$memmobile;
        }
        int_to_string($list);
        foreach ($list as $key => $value) {
            $list[$key]['area']=$value['province'].$value['city'].$value['district'];
            $list[$key]['busilicpath']=get_picture_path($value['busilicpid'],'supplier','org');
            $list[$key]['orgpath']=get_picture_path($value['orgpid'],'supplier','org');
            $list[$key]['banklicpath']=get_picture_path($value['banklicpid'],'supplier','org');
            switch ($value['status']) {
                case '0':
                    $list[$key]['status_text']="已接收申请";
                    break;
                case '100':
                    $list[$key]['status_text']="审核信息真实性";
                    break;
                case '101':
                    $list[$key]['status_text']="授权";
                    break;
                case '1':
                    $list[$key]['status_text']="审核通过";
                    break;
                case '2':
                    $list[$key]['status_text']="审核拒绝";
                    break;
                default:
                    $list[$key]['status_text']="禁用";
                    break;
            }
        }
        $this->assign('_list', $list);
        $this->meta_title = '供应商信息';
        $this->display();
    }

    public function supplierChangeStatus(){
        $id = array_unique((array)I('id',0));
        $status = I('status');
        $id = is_array($id) ? implode(',',$id) : $id;
        if ( empty($id) ) {
            $this->error('请选择要操作的数据!');
        }
        $map['uid'] =   array('in',$id);
        $map['roleid'] =  4;
        $this->changeAllStatus('member_role',$map,array('status'=>$status));
    }

    public function sendpublicmsg(){
        $uid=is_login();
        $sendtype = I('sendtype');
        $role=C('MEMBERROLE');
        foreach ($role as $key => $value) {
            if($key!=$sendtype){
                unset($role[$key]);
            }
        }
        if(IS_POST){
            $data['comment']=I('comment');
            $tmp['comment']='<div style="width:100%">'.$tmp['comment'].'</div>';
            $data['from']=$uid;
            $data['type']=I('type');
            $data['status']='1';
            $tmp['title']=I('title');
            $data['created']=time();
            $data['roleid']=I('roleid');
            D('public_message')->add($data);
            $msg = array_merge( array( 'success'=>'发送成功！', 'error'=>'发送失败！', 'url'=>'' ,'ajax'=>IS_AJAX) , (array)$msg );
            $this->success($msg['success'],$msg['url'],$msg['ajax']);
        }else{
            $this->assign('sendtype', $sendtype);
            $this->assign('role', $role);
            $this->meta_title = '发送公共消息';
            $this->display();
        }
    }

    public function sendprivatemsg(){
        $uid=is_login();
        $id = I('ids');
        if(empty($id)){
            $this->error('请选择需要发送消息的会员');
        }
        $to_uid = explode(',',$id);
        $sendtype = I('sendtype');
        $role=C('MEMBERROLE');
        foreach ($role as $key => $value) {
            if($key!=$sendtype){
                unset($role[$key]);
            }
        }
        if(IS_POST){
            $data=array();
            foreach ($to_uid as $key => $value) {
                $tmp['comment']=I('comment');
                $tmp['comment']='<div style="width:100%">'.$tmp['comment'].'</div>';
                $tmp['from']=$uid;
                $tmp['to']=$value;
                $tmp['type']=I('type');
                $tmp['title']=I('title');
                $tmp['status']='0';
                $tmp['created']=time();
                $tmp['roleid']=I('roleid');
                array_push($data, $tmp);
            }
            D('private_message')->addAll($data);
            $url='';
            switch ($sendtype) {
                case '0':
                    $url=U('Membership/index');
                    break;
                case '1':
                    $url=U('Membership/maker');
                    break;
                case '2':
                    $url=U('Membership/makerorg');
                    break;
                case '4':
                    $url=U('Membership/supplier');
                    break;
            }
            $msg = array_merge( array( 'success'=>'发送成功！', 'error'=>'发送失败！', 'url'=>$url ,'ajax'=>IS_AJAX) , (array)$msg );
            $this->success($msg['success'],$msg['url'],$msg['ajax']);
        }else{
            $seluidlist=array();
            if(is_array($to_uid)){
                foreach ($to_uid as $key => $value) {
                    $seluidlist[$value]['nickname']=D('Member')->getNickName($value);
                    $seluidlist[$value]['mobile']=D('Member')->getMobile($value);
                }
            }else{
                $seluidlist[$to_uid]['nickname']=D('Member')->getNickName($to_uid);
                $seluidlist[$to_uid]['mobile']=D('Member')->getMobile($to_uid);
            }
            $this->assign('sendtype', $sendtype);
            $this->assign('role', $role);
            $this->assign('uid', $uid);
            $this->assign('seluidlist', $seluidlist);
            $this->assign('ids', $id);
            $this->meta_title = '发送消息';
            $this->display();
        }
    }

    public function picture_upload(){
        $data=I('data');
        $return  = array('status' => 1, 'id' => '', 'path' => '', 'info' => '');
        $picmodel = new PictureModel();
        $pcid=empty($data['pcid']) ? 0 : $data['pcid'];
        $info = $picmodel->upload($data['uid'],$data['imgtype'],$data['files'],$pcid);
        if($info){
            foreach ($info as $key => $value) {
                $return['status']=1;
                $return['id']=$value['id'];
                $return['path']=get_picture_path($value['id'],$data['imgtype'],'org');
            }
        }else{
            $return['status']=0;
            $return['info']=$picmodel->getError();
        }
        $this->ajaxReturn($return);
    }
}