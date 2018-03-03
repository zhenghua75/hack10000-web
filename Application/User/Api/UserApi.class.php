<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace User\Api;
use User\Api\Api;
use User\Model\MemberModel;
use Common\Model\PictureModel;
use Common\Api\ProductApi;
use Common\Api\ShopApi;

class UserApi extends Api{
    /**
    * 构造方法，实例化操作模型
    */
    protected function _init(){
        $this->model = new MemberModel();
    }

    /**
    * 注册一个新用户
    * @param  string $username 用户名
    * @param  string $password 用户密码
    * @param  string $email    用户邮箱
    * @param  string $mobile   用户手机号码
    * @return integer          注册成功-用户信息，注册失败-错误编号
    */
    public function register($username, $password, $email, $mobile = ''){
        return $this->model->register($username, $password, $email, $mobile);
    }

    /**
    * 用户登录认证
    * @param  string  $username 用户名
    * @param  string  $password 用户密码
    * @param  integer $type     用户名类型 （1-用户名，2-邮箱，3-手机，4-UID）
    * @return integer           登录成功-用户ID，登录失败-错误编号
    */
    public function login($username, $password){
        return $this->model->login($username, $password);
    }

    /**
    * 获取用户信息
    * @param  string  $uid         用户ID或用户名
    * @param  boolean $is_username 是否使用用户名查询
    * @return array                用户信息
    */
    public function info($uid, $is_username = false){
        return $this->model->info($uid, $is_username);
    }

    /**
    * 检测用户名
    * @param  string  $field  用户名
    * @return integer         错误编号
    */
    public function checkUsername($username){
        return $this->model->checkField($username, 1);
    }

    /**
    * 检测邮箱
    * @param  string  $email  邮箱
    * @return integer         错误编号
    */
    public function checkEmail($email){
        return $this->model->checkField($email, 2);
    }

    /**
    * 检测手机
    * @param  string  $mobile  手机
    * @return integer         错误编号
    */
    public function checkMobile($mobile){
        return $this->model->checkField($mobile, 3);
    }

    /**
    * 检测手机验证码
    */
    public function checkVerifyMobile($mobile,$verifymobile){
        $map['mobile']=$mobile;
        $verify=D('verifycode')->where($map)->order('id desc')->find();
        if($verify){
            $created=$verify['created'];
            $diff=ceil((time()-$created)/60);
            if($diff<30){
                if($verify['code']==$verifymobile){
                  return true;
                }else{
                  return false;
                }
            }else{
                return false;
            }
        }else{
            return false;
        }
    }

    /**
    * 更新用户信息
    * @param int $uid 用户id
    * @param array $data 修改的字段数组
    * @return true 修改成功，false 修改失败
    * @author huajie <banhuajie@163.com>
    */
    public function updateInfo($uid, $data){
        if($this->model->updateUserFields($uid, $data) !== false){
            $return['status'] = true;
        }else{
            $return['status'] = false;
            $return['info'] = $this->model->getError();
        }
        return $return;
    }

    public function updatePass($uid, $password, $data){
        if($this->model->updateUserPass($uid, $password, $data) !== false){
            $return['status'] = true;
        }else{
            $return['status'] = false;
            $return['info'] = $this->model->getError();
        }
        return $return;
    }

    public function updateForgetPass($uid, $password){
        if($this->model->updateForgetPass($uid, $password) !== false){
            $return['status'] = true;
        }else{
            $return['status'] = false;
            $return['info'] = $this->model->getError();
        }
        return $return;
    }

    public function getMobileUser($mobile){
        return $this->model->getMemberInfoByMobile($mobile);
    }

    public function getMemberInfo($uid){
        $res=D('member_info')->where(array('uid'=>$uid,'status'=>'1'))->order('roleid')->select();
        $data=array();
        foreach ($res as $key => $value) {
            $data[$value['roleid']][$value['name']]=$value['value'];
        }
        foreach ($data as $key => &$value) {
            $status=D('member_role')->where(array('uid'=>$uid,'roleid'=>$key))->getField('status');
            $value['status']=intval($status);
            switch ($status) {
                case '0':
                    $value['status_text']="已接收申请";
                    break;
                case '100':
                    $value['status_text']="审核信息真实性";
                    break;
                case '101':
                    $value['status_text']="授权";
                    break;
                case '1':
                    $value['status_text']="审核通过";
                    break;
                case '2':
                    $value['status_text']="审核拒绝";
                    break;
                default:
                    $value['status_text']="禁用";
                    break;
            }
            if($key==4 || $key==2){
                $value['busilicpidorgname']=D('picture')->where(array('id'=>$value['busilicpid']))->getField('org_filename');
                $value['orgpidorgname']=D('picture')->where(array('id'=>$value['orgpid']))->getField('org_filename');
                $value['banklicpidorgname']=D('picture')->where(array('id'=>$value['banklicpid']))->getField('org_filename');
            }
            if($key==2){
                $value['busilicpidpath']=get_picture_path($value['busilicpid'],'makerorg','org');
                $value['orgpidpath']=get_picture_path($value['orgpid'],'makerorg','org');
                $value['banklicpidpath']=get_picture_path($value['banklicpid'],'makerorg','org');
            }elseif($key==4){
                $value['busilicpidpath']=get_picture_path($value['busilicpid'],'supplier','org');
                $value['orgpidpath']=get_picture_path($value['orgpid'],'supplier','org');
                $value['banklicpidpath']=get_picture_path($value['banklicpid'],'supplier','org');
            }
        }
        return $data;
    }

    /**
    * 修改头像
    */
    public function updatePortrait($uid,$files){
        $return  = array('status' => 1, 'id' => '', 'path' => '', 'info' => '');
        $picmodel = new PictureModel();
        $info = $picmodel->upload($uid,'PORTRAIT',$files);
        if($info){
            $return['status'] = 1;
            foreach ($info as $key => $value) {
                $this->model->updatePortrait($uid, $value['id']);
                $portpath=get_picture_path($value['id'],'portrait','150x150');
                if($portpath=='0'){
                    $portpath='http://img.hack10000.com/portrait/default.png';
                }
                session('user_auth.portrait_path',$portpath);
                $auth=session('user_auth');
                session('user_auth_sign', data_auth_sign($auth));
                $return['id']=$value['id'];
                $return['path']=$portpath;
            }
        } else {
            $return['status'] = 0;
            $return['info']   = $picmodel->getError();
        }
        
        return $return;
    }

    /*发送手机验证码*/
    public function sms_send_verifycode($number){
        $code = rand(100000, 999999);
        $msg='验证码：'.$code.' 。'.C('WEB_SITE_TITLE').'提醒您：此验证码用于注册或修改密码时使用，请注意，切勿转发。';
        $res=$this->sms_send($number,$msg);
        if($res['status']){
            $coderow['mobile']=$number;
            $coderow['code']=$code;
            $coderow['created']=NOW_TIME;
            D('verifycode')->add($coderow);
        }
        return $res;
    }

    /*发送短信*/
    public function sms_send($number, $message) {
        $curgateway=C('CURSMSGATEWAY');
        $gateway=C(strtoupper($curgateway));

        $gate_url = $gateway['url'];
        $gate_port = $gateway['port'];
        $gate_uid = $gateway['account'];
        $gate_pwd = $gateway['auth'];
        $strmd5 = strtolower(md5($gate_pwd));
        $message=str_replace('%2F', '/', rawurlencode($message));

        $query = 'uid='. $gate_uid
              .'&auth='. $strmd5
              .'&mobile='. $number
              .'&msg='. $message
              .'&expid=0'
              .'&encode=utf-8';

        $sms_url='http://'.$gate_url.':'.$gate_port.'/hy/?'.$query;

        $ch = curl_init();
        $timeout = 5;
        curl_setopt($ch, CURLOPT_URL, $sms_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        $result = curl_exec($ch);
        curl_close($ch);

        switch(strtolower($curgateway)){
            case 'sioogwgateway':
                $gatewayerror=$this->sms_sioogw_error_codes();
                break;
        }
        $response = explode(',', $result);
        $resmsg=$gatewayerror[$response[0]];

        if ($response[0]=='0') {
            return array('status'=>TRUE, 'message'=>$resmsg);
        }else{
            return array('status'=>FALSE, 'message'=>$resmsg);
        }
    }

    public function sms_sioogw_error_codes() {
        return array(
            '0' => "操作成功",
            '-1' => "签权失败",
            '-2' => "未检索到被叫号码",
            '-3' => "被叫号码过多",
            '-4' => "内容未签名",
            '-5' => "内容过长",
            '-6' => "余额不足",
            '-7' => "暂停发送",
            '-8' => "保留",
            '-9' => "定时发送时间格式错误",
            '-10' => "下发内容为空",
            '-11' => "账户无效",
            '-12' => "Ip地址非法",
            '-13' => "操作频率快",
            '-14' => "操作失败",
            '-15' => "拓展码无效",
            '-16' => "取消定时,seqid错误",
            '-17' => "未开通报告",
            '-18' => "暂留",
            '-19' => "未开通上行",
            '-20' => "暂留",
            '-21' => "包含屏蔽词",
        );
    }

    public function picture_upload($data){
        $return  = array('status' => 1, 'id' => '', 'path' => '', 'info' => '');
        $picmodel = new PictureModel();
        $info = $picmodel->upload($data['uid'],$data['imgtype'],$data['files']);
        if($info){
            foreach ($info as $key => $value) {
                $return['status']=1;
                $return['id']=intval($value['id']);
                switch (strtolower($data['imgtype'])) {
                    case 'makerorg':
                        $return['path']=get_picture_path($value['id'],'makerorg','org');
                        break;
                    case 'supplier':
                        $return['path']=get_picture_path($value['id'],'supplier','org');
                        break;
                }
            }
        }else{
            $return['status']=0;
            $return['info']=$picmodel->getError();
        }
        return $return;
    }

    public function makerregister($data){
        $return  = array('status' => 1, 'info' => '申请成功', 'data' => '');
        $uid=$data['uid'];
        $meminfo=array();
        foreach ($data as $key => $value) {
            if($key!='uid'){
                $meminfo[]=array('uid'=>$uid,'roleid'=>1,'name'=>$key,'value'=>$value,'created'=>time(),'updated'=>time(),'status'=>'1');
            }
        }
        $res=M('member_info')->where(array('uid'=>$uid,'roleid'=>1,'status'=>'1'))->count();
        if($res){
            M('member_info')->where(array('uid'=>$uid,'roleid'=>1,'status'=>'1'))->save(array('status'=>'-1'));
        }
        M('member_info')->addAll($meminfo);

        $res=D('member_role')->where(array('uid'=>$uid,'roleid'=>1))->find();
        if(!$res){
            D('member_role')->add(array('uid'=>$uid,'roleid'=>1,'created'=>time(),'updated'=>time(),'status'=>'0'));
            // $isreg=D('hcoin_record')->where(array('uid'=>$uid,'type'=>'REG'))->count();
            // if(!$isreg){
            //     $registcoin=C('REGISTHCOIN');
            //     $begin=strtotime($registcoin['begin']);
            //     $end=strtotime($registcoin['end']);
            //     $today=strtotime(date('Y-m-d 00:00:00'));
            //     $diff=floor(($today-$begin)/86400);
            //     if($end>$today){
            //         $charge=$registcoin['base']-($diff*$registcoin['redius']);
            //         if($charge<0){
            //             $charge=0;
            //         }
            //     }else{
            //         $charge=0;
            //     }
            //     $charge=$charge*100;
            //     if($charge>0){
            //         D('member')->where(array('uid'=>$uid))->setInc('hcoin',$charge);
            //         $datahcoin=array();
            //         $datahcoin['uid']=$uid;
            //         $datahcoin['type']='REG';
            //         $datahcoin['charge']=$charge;
            //         $datahcoin['comments']='申请创客赠送';
            //         $datahcoin['created']=time();
            //         D('hcoin_record')->add($datahcoin);
            //     }
            // }
        }elseif($res['status']=='2'){
            D('member_role')->where(array('uid'=>$uid,'roleid'=>1))->setField('status','0');
        }

        return $return;
    }

    public function makerorgregister($data){
        $return  = array('status' => 1, 'info' => '申请成功', 'data' => '');
        $uid=$data['uid'];

        $meminfo=array();
        foreach ($data as $key => $value) {
            if($key!='uid'){
                $meminfo[]=array('uid'=>$uid,'roleid'=>2,'name'=>$key,'value'=>$value,'created'=>time(),'updated'=>time(),'status'=>'1');
            }
        }

        $res=M('member_info')->where(array('uid'=>$uid,'roleid'=>2,'status'=>'1'))->count();
        if($res){
            M('member_info')->where(array('uid'=>$uid,'roleid'=>2,'status'=>'1'))->save(array('status'=>'-1'));
        }
        M('member_info')->addAll($meminfo);

        $res=D('member_role')->where(array('uid'=>$uid,'roleid'=>2))->find();
        if(!$res){
            D('member_role')->add(array('uid'=>$uid,'roleid'=>2,'created'=>time(),'updated'=>time(),'status'=>'0'));
            // $isreg=D('hcoin_record')->where(array('uid'=>$uid,'type'=>'REG'))->count();
            // if(!$isreg){
            //     $registcoin=C('REGISTHCOIN');
            //     $begin=strtotime($registcoin['begin']);
            //     $end=strtotime($registcoin['end']);
            //     $today=strtotime(date('Y-m-d 00:00:00'));
            //     $diff=floor(($today-$begin)/86400);
            //     if($end>$today){
            //         $charge=$registcoin['base']-($diff*$registcoin['redius']);
            //         if($charge<0){
            //             $charge=0;
            //         }
            //     }else{
            //         $charge=0;
            //     }
            //     $charge=$charge*100;
            //     if($charge>0){
            //         D('member')->where(array('uid'=>$uid))->setInc('hcoin',$charge);
            //         $datahcoin=array();
            //         $datahcoin['uid']=$uid;
            //         $datahcoin['type']='REG';
            //         $datahcoin['charge']=$charge;
            //         $datahcoin['comments']='申请创客赠送';
            //         $datahcoin['created']=time();
            //         D('hcoin_record')->add($datahcoin);
            //     }
            // }
        }elseif($res['status']=='2'){
            D('member_role')->where(array('uid'=>$uid,'roleid'=>2))->setField('status','0');
        }
        
        return $return;
    }

    public function supplierregister($data){
        $return  = array('status' => 1, 'info' => '申请成功', 'data' => '');
        $uid=$data['uid'];

        $meminfo=array();
        foreach ($data as $key => $value) {
            if($key!='uid'){
                $meminfo[]=array('uid'=>$uid,'roleid'=>4,'name'=>$key,'value'=>$value,'created'=>time(),'updated'=>time(),'status'=>'1');
            }
        }

        $res=M('member_info')->where(array('uid'=>$uid,'roleid'=>4,'status'=>'1'))->count();
        if($res){
            M('member_info')->where(array('uid'=>$uid,'roleid'=>4,'status'=>'1'))->save(array('status'=>'-1'));
        }
        M('member_info')->addAll($meminfo);

        $res=D('member_role')->where(array('uid'=>$uid,'roleid'=>4))->find();
        if(!$res){
            D('member_role')->add(array('uid'=>$uid,'roleid'=>4,'created'=>time(),'updated'=>time(),'status'=>'0'));
        }elseif($res['status']=='2'){
            D('member_role')->where(array('uid'=>$uid,'roleid'=>4))->setField('status','0');
        }
        
        return $return;
    }

    public function registerprocess($uid){
        $map['uid']=$uid;
        $res=D('member_role')->where($map)->getField('roleid,status');
        if($res){
            foreach ($res as $key => $value) {
                $result[$key]['status']=$value;
                $map['roleid']=$key;
                $map['status']='1';
                switch ($key) {
                    case '1':
                        $map['name']=array('in','truename,idno');
                        $field=D('member_info')->where($map)->getField('name,value');
                        $result[$key]['name']=$field['truename'];
                        $result[$key]['idno']=$field['idno'];
                        break;
                    case '2':
                        $map['name']=array('in','company,busilicense');
                        $field=D('member_info')->where($map)->getField('name,value');
                        $result[$key]['name']=$field['company'];
                        $result[$key]['idno']=$field['busilicense'];
                        break;
                    case '4':
                        $map['name']=array('in','company,busilicense');
                        $field=D('member_info')->where($map)->getField('name,value');
                        $result[$key]['name']=$field['company'];
                        $result[$key]['idno']=$field['busilicense'];
                        break;
                }
                switch ($value) {
                    case '0':
                        if($key==1 || $key==2){
                            $result[$key]['info']="您的开店申请已提交成功，我们会尽快进行审核，请耐心等待！";
                        }else{
                            $result[$key]['info']="您的供应商申请已提交成功，我们会尽快进行审核，请耐心等待！";
                        }
                        break;
                    case '100':
                        $result[$key]['info']="正在审核您提供的信息真实性！";
                        break;
                    case '101':
                        $result[$key]['info']="信息审核已经完成，正在授权！";
                        break;
                    case '1':
                        if($key==1 || $key==2){
                            $result[$key]['info']="审核通过，您已经是我们的创客用户了！";
                        }else{
                            $result[$key]['info']="审核通过，您已经是我们的供应商用户了！";
                        }
                        break;
                    case '2':
                        $result[$key]['info']="申请不通过，您所提供信息不够完整，请认真审核您的资料，并重新申请！";
                        break;
                    default:
                        if($key==1 || $key==2){
                            $result[$key]['info']="您的创客已被禁用！";
                        }else{
                            $result[$key]['info']="您的供应商已被禁用！";
                        }
                        break;
                }
            }
        }else{
            $result=array();
        }

        return $result;
    }

    public function getSupplierCompany($uid){
        return D('member_info')->where(array('uid'=>$uid,'roleid'=>4,'name'=>'company','status'=>'1'))->getField('value');
    }

    public function getHCoin($uid){
        $return  = array('hcoin' => 0, 'info' => '');
        $res=$this->model->getHCoin($uid);
        if($res){
            $return['hcoin']=intval($res);
        }else{
            $return['info']='慧爱币余额为0或者创客帐户被禁用';
        }
        return $return;
    }

    public function getHCoinRecord($uid){
        $res=D('hcoin_record')->where(array('uid'=>$uid))->order('id desc')->select();
        foreach ($res as $key => &$value) {
            if(intval($value['charge']>0)){
                $value['charge']=intval($value['charge']/100);
                $value['pay']=0;
            }else{
                $value['pay']=intval($value['charge']/100);
                $value['charge']=0;
            }
        }
        return $res;
    }

    public function isMaker($uid){
        $res=D('member_role')->where(array('uid'=>$uid,'roleid'=>array('in','1,2'),'status'=>1))->count();
        return $res;
    }

    public function isSupplier($uid){
        $res=D('member_role')->where(array('uid'=>$uid,'roleid'=>'4','status'=>1))->count();
        return $res;
    }

    public function collect($objectid,$source,$uid,$type,$sourceobjid=0){
        $roleid=D('member_role')->where(array('uid'=>$uid,'status'=>'1'))->max('roleid');
        if(empty($roleid)){
            $roleid=0;
        }
        $res=D('member_collection')->where(array('uid'=>$uid,'objid'=>$objectid,'objtype'=>$type,'source'=>$source,'sourceobjid'=>$sourceobjid))->find();
        if($res && $res['collect']==0){
            D('member_collection')->where(array('id'=>$res['id']))->setField('collect',1);
            $command=1;
        }elseif($res && $res['collect']==1){
            D('member_collection')->where(array('id'=>$res['id']))->setField('collect',0);
            $command=0;
        }else{
            $data['uid']=$uid;
            $data['roleid']=$roleid;
            $data['objid']=$objectid;
            $data['objtype']=$type;
            $data['source']=$source;
            $data['collect']=1;
            $data['sourceobjid']=$sourceobjid;
            $data['created']=time();
            D('member_collection')->add($data);
            $command=1;
        }
        return array('status'=>true,'command'=>$command);
    }

    public function isCollect($objectid,$source,$uid,$type,$sourceobjid=0){
        if($type=='shop'){
            $res=D('member_collection')->where(array('uid'=>$uid,'objid'=>$objectid,'objtype'=>$type,'source'=>$source))->find();
        }else{
            $res=D('member_collection')->where(array('uid'=>$uid,'objid'=>$objectid,'objtype'=>$type,'source'=>$source,'sourceobjid'=>$sourceobjid))->find();
        }
        if($res){
            if($res['collect']){
                return true;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }

    public function favor($objectid,$source,$uid,$type){
        $roleid=D('member_role')->where(array('uid'=>$uid,'status'=>'1'))->max('roleid');
        if(empty($roleid)){
            $roleid=0;
        }
        $res=D('member_collection')->where(array('uid'=>$uid,'objid'=>$objectid,'objtype'=>$type,'source'=>$source))->find();
        if($res){
            D('member_collection')->where(array('id'=>$res['id']))->setField('favor',1);
        }else{
            $data['uid']=$uid;
            $data['roleid']=$roleid;
            $data['objid']=$objectid;
            $data['objtype']=$type;
            $data['source']=$source;
            $data['favor']=1;
            $data['created']=time();
            D('member_collection')->add($data);
        }
        return true;
    }

    public function isFavor($objectid,$source,$uid,$type,$sourceobjid=0){
        $res=D('member_collection')->where(array('uid'=>$uid,'objid'=>$objectid,'objtype'=>$type,'source'=>$source,'sourceobjid'=>$sourceobjid))->find();
        if($res){
            if($res['favor']){
                return true;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }

    public function commentadd($data,$uid){
        foreach ($data as $key => $value) {
            $detail=D('orderdetail')->where(array('id'=>$value['orderdetailid']))->find();
            $source=D('supplier_order')->where(array('id'=>$detail['orderid']))->getField('source');
            $value['source']=$source;
            $value['orderid']=$detail['orderid'];
            $value['product_id']=$detail['product_id'];
            $value['makerid']=$detail['makerid'];
            $value['supplierid']=$detail['supplierid'];
            $value['uid']=$uid;
            $value['status']=1;
            $value['created']=time();
            $res=D('product_comment')->add($value);
            if(!$res){
                break;
            }
        }
        if($res){
            return true;
        }else{
            return false;
        }
    }

    public function getMyBookmarks($uid){
        $res=D('member_collection')->where(array('uid'=>$uid,'collect'=>1))->select();
        $shoplist=array();
        $productlist=array();
        $prodapi=new ProductApi;
        $shopapi=new ShopApi;
        foreach ($res as $key => $value) {
            switch ($value['objtype']) {
                case 'product':
                    $prod=$prodapi->getProductInfo($value['objid'],$value['source'],0,$value['sourceobjid']);
                    $prod['bookmarked']=true;
                    $prod['liked']=$value['favor']='0' ? false : true;
                    $prod['image']=$prod['images'][0];
                    unset($prod['specs']);
                    unset($prod['images']);
                    array_push($productlist, $prod);
                    break;
                case 'shop':
                    $shop=$shopapi->getShopByID($value['objid']);
                    array_push($shoplist, $shop);
                    break;
            }
        }
        $return['shop']=$shoplist;
        $return['product']=$productlist;
        return $return;
    }

    public function getMyBuyShop($uid){
        return array();
    }

    public function getSupplierComments($uid){
        $res=D('product_comment')->order('created')->select();
        $prodapi=new ProductApi;
        foreach ($res as $key => $value) {
            $tmpsup=$prodapi->getProductSupplierID($value['product_id']);
            if($tmpsup!=$uid){
                unset($res[$key]);
            }
        }
        foreach ($res as $key => $value) {
            $res[$key]['imgpath']=$prodapi->getProductDefaultPicture($value['product_id']);
            $res[$key]['nickname']=$this->model->getNickName($value['uid']);
        }
        return $res;
    }

    public function create_member_picclass($data){
        $return=array('status'=>1,'error'=>'');
        if(empty($data) || $data['name']=='' || $data['uid']=='' || $data['uid']==0){
            $return['status']=0;
            $return['error']='创建失败';
        }else{
            $hascnt=D('member_picclass')->where(array('uid'=>$data['uid'],'name'=>$data['name'],'type'=>$data['type']))->count();
            if($hascnt>0){
                $return['status']=0;
                $return['error']='目录已存在';
            }else{
                $res=D('member_picclass')->add($data);
                if(!$res){
                    $return['status']=0;
                    $return['error']='创建失败';
                }
            }
        }
        return $return;
    }

    public function getMakerHcoinCoupon($uid){
        $return  = array('hcoin' => 0, 'coupon'=> 0, 'info' => '');
        $res=$this->model->getHCoin($uid);
        if($res){
            $return['hcoin']=intval($res);
        }else{
            $return['info']='创客帐户被禁用';
        }
        return $return;
    }

    public function shippingaddressoper($uid,$data){
        $return=array('status'=>1,'error'=>'添加收货地址成功');
        if(empty($data['id'])){
            if(empty($data) || $data['province']=='' || $data['city']=='' || $data['district']==''){
                $return['status']=0;
                $return['error']='添加收货地址失败，信息不全';
            }else{
                $data['uid']=$uid;
                $data['created']=time();
                $data['provincename']=getchinacityname($data['province']);
                $data['cityname']=getchinacityname($data['city']);
                $data['districtname']=getchinacityname($data['district']);
                $res=D('member_shippingaddr')->add($data);
                if(!$res){
                    $return['status']=0;
                    $return['error']='添加收货地址失败';
                }
            }
        }else{
            $res=D('member_shippingaddr')->where(array('id'=>$data['id']))->find();
            if($data['status']=='-1'){
                D('member_shippingaddr')->where(array('id'=>$data['id']))->setField('status',-1);
                $return['error']='删除收货地址成功';
            }elseif($res['isdefault']=='0' && $data['isdefault']=='1'){
                D('member_shippingaddr')->where(array('uid'=>$uid,'isdefault'=>'1'))->setField('isdefault',0);
                D('member_shippingaddr')->where(array('id'=>$data['id']))->setField('isdefault',1);
                $return['error']='设置默认地址成功';
            }else{
                if($res){
                    $data['provincename']=getchinacityname($data['province']);
                    $data['cityname']=getchinacityname($data['city']);
                    $data['districtname']=getchinacityname($data['district']);
                    D('member_shippingaddr')->where(array('id'=>$data['id']))->save($data);
                    $return['error']='修改收货地址成功';
                }else{
                    $return['status']=0;
                    $return['error']='修改收货地址失败';
                }
            }
        }

        return $return;
    }

    public function getshippingaddress($uid){
        $shipaddr=D('member_shippingaddr')->where(array('uid'=>$uid,'status'=>'1'))->order('isdefault desc,id desc')->select();
        if($shipaddr){
            $defaultcnt=D('member_shippingaddr')->where(array('uid'=>$uid,'status'=>'1','isdefault'=>'1'))->count();
            if($defaultcnt==0){
                D('member_shippingaddr')->where(array('id'=>$shipaddr[0]['id']))->setField('isdefault',1);
                $shipaddr[0]['isdefault']=1;
            }
        }

        return $shipaddr;
    }
}
