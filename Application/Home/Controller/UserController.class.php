<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Home\Controller;
use User\Api\UserApi;
use Common\Api\CategoryApi;
use Common\Api\ProductApi;
use Common\Api\OrderApi;
use Common\Api\ShopApi;
use Common\Api\MessageApi;
use Common\Api\DocumentApi;
use Common\Api\CouponApi;
use Common\Api\SystemApi;

/**
 * 用户控制器
 * 包括用户中心，用户登录及注册
 */
class UserController extends HomeController {

    protected function _initialize(){
        parent::_initialize();
        $notlimit=array('register','login','verify','logindiag','checkverify','sms_send_verifycode','forgetpass','sms_sendsuer_verifycode','resetpass');
        if (!is_login() && !in_array(strtolower(ACTION_NAME), $notlimit)) {
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

    /* 用户中心首页 */
    public function index($source='web'){
        $uid = is_login();
        $userapi=new UserApi;
        $user=$userapi->info($uid);
        if($source=='app'){
            $userapp=array();
            $auth=session('user_auth');
            $userapp['id']=$auth['uid'];
            $userapp['headImage']=$auth['portrait_path'];
            $userapp['nikeName']=$auth['nickname'];
            $userapp['level']=$auth['level'];
            $userapp['token']=$auth['token'];
            // $orderapi=new OrderApi;
            // $userapp['orders']=$orderapi->getMyOrderSum($auth['uid']);
            if($this->memberstatus[1] || $this->memberstatus[2]){
                if($this->memberstatus[1] && $this->memberstatus[1]['status']=='1'){
                    $userapp['ishack']=true;
                }else{
                    if($this->memberstatus[2] && $this->memberstatus[2]['status']=='1'){
                        $userapp['ishack']=true;
                    }else{
                        $userapp['ishack']=false;
                    }
                }
            }else{
                $userapp['ishack']=false;
            }
            if($this->memberstatus[4]){
                if($this->memberstatus[4] && $this->memberstatus[4]['status']=='1'){
                    $userapp['isvendor']=true;
                }else{
                    $userapp['isvendor']=false;
                }
            }else{
                $userapp['isvendor']=false;
            }
            $sysapi=new SystemApi;
            $sysapi->writeAccessLog('会员中心','app');
            $this->ajaxReturn(array('success'=>true,'body'=>array($userapp)));
        }else{
            $sysapi=new SystemApi;
            $sysapi->writeAccessLog('会员中心');
            $tmp=explode('-', $user['birthday']);
            $birthday['year']=$tmp[0];
            $birthday['month']=$tmp[1];
            $birthday['day']=$tmp[2];
            $param=array('province'=>530000, 'city'=>530100, 'district'=>530102);
            $shippingaddrlist=$userapi->getshippingaddress($uid);
            $this->assign('param',$param);
            $this->assign('userinfo',$user);
            $this->assign('birthday',$birthday);
            $this->assign('shippingaddrlist',$shippingaddrlist);
            $this->assign('pagename','page-userindex');
            $this->display();
        }
    }

    /* 注册页面 */
    public function register($mobile = '', $password = '', $repassword = '', $verify = '', $validcode = ''){
        $source=I('source');
        if(!C('USER_ALLOW_REGISTER')){
            if($source=='app'){
                $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>false,'error'=>'注册已关闭')));
            }else{
                $this->error('注册已关闭');
            }
        }
        if(IS_POST){ //注册用户
            /* 检测验证码 */
            // if(!check_verify($verify)){
            //     $this->error('图形验证码输入错误！');
            // }

            /* 检测密码 */
            if($password != $repassword){
                if($source=='app'){
                    $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>false,'error'=>'密码和确认密码不一致！')));
                }else{
                    $this->error('密码和确认密码不一致！');
                }
            }           

            /* 调用注册接口注册用户 */
            $userapi=new UserApi;
            if(!$userapi->checkVerifyMobile($mobile,$validcode)){
                if($source=='app'){
                    $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>false,'error'=>'手机验证码输入错误！')));
                }else{
                    $this->error('手机验证码输入错误！');
                }
            }

            $uid = $userapi->register('', $password, '', $mobile);
            if(0 < $uid){ //注册成功
                //TODO: 发送验证邮件
                $user=$userapi->info($uid);
                $Member = D('Member');
                if($Member->login($user,$source)){ //登录用户
                    if($source=='app'){
                        $userapp=array();
                        $auth=session('user_auth');
                        $userapp['id']=$auth['uid'];
                        $userapp['headImage']=$auth['portrait_path'];
                        $userapp['nikeName']=$auth['nickname'];
                        $userapp['level']=$auth['level'];
                        $userapp['token']=$auth['token'];
                        if($this->memberstatus[1] || $this->memberstatus[2]){
                            if($this->memberstatus[1] && $this->memberstatus[1]['status']=='1'){
                                $userapp['ishack']=true;
                            }else{
                                if($this->memberstatus[2] && $this->memberstatus[2]['status']=='1'){
                                    $userapp['ishack']=true;
                                }else{
                                    $userapp['ishack']=false;
                                }
                            }
                        }else{
                            $userapp['ishack']=false;
                        }
                        if($this->memberstatus[4]){
                            if($this->memberstatus[4] && $this->memberstatus[4]['status']=='1'){
                                $userapp['isvendor']=true;
                            }else{
                                $userapp['isvendor']=false;
                            }
                        }else{
                            $userapp['isvendor']=false;
                        }
                        $this->ajaxReturn(array('success'=>true,'body'=>array($userapp)));
                    }else{
                        $this->success('注册成功！',U('User/index'));
                    }
                }
            } else { //注册失败，显示错误信息
                if($source=='app'){
                    $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>false,'error'=>$this->showRegError($uid))));
                }else{
                    $this->error($this->showRegError($uid));
                }
            }

        } else { //显示注册表单
            if($source=='app'){
                $this->ajaxReturn(array('success'=>true,'body'=>true));
            }else{
                $docapi=new DocumentApi;
                $docid=$docapi->getRegAgreement();
                $this->assign('regagrid',$docid);
                $this->assign('pagename','page-register');
                $this->display();
            }
        }
    }

    /* 登录页面 */
    public function login($source='web', $username1 = '', $password1 = '', $verify1 = '',$token='0'){
        if(IS_POST){ //登录验证
            /* 检测验证码 */
            if($source=='web'){
                if(!check_verify($verify1)){
                 $this->error('验证码输入错误！');
                }
            }

            /* 调用UC登录接口登录 */
            $userapi=new UserApi;
            $userins = $userapi->login($username1, $password1);
            if($userins){ //UC登录成功
                /* 登录用户 */
                $Member = D('Member');
                if($Member->login($userins,$source,$token)){ //登录用户
                    if($source=='app'){
                        $userapp=array();
                        $auth=session('user_auth');
                        $userapp['id']=$auth['uid'];
                        $userapp['headImage']=$auth['portrait_path'];
                        $userapp['nikeName']=$auth['nickname'];
                        $userapp['level']=$auth['level'];
                        $userapp['token']=$auth['token'];
                        $userapp['sex']=$userins['sex'];
                        $userapp['birthday']=$userins['birthday'];
                        $userapp['email']=$userins['email'];
                        $userapp['mobile']=$userins['mobile'];
                        $userapp['truename']=$userins['truename'];
                        $this->memberstatus=$userapi->registerprocess($auth['uid']);
                        if($this->memberstatus[1] || $this->memberstatus[2]){
                            if($this->memberstatus[1] && $this->memberstatus[1]['status']=='1'){
                                $userapp['isHack']=true;
                            }else{
                                if($this->memberstatus[2] && $this->memberstatus[2]['status']=='1'){
                                    $userapp['isHack']=true;
                                }else{
                                    $userapp['isHack']=false;
                                }
                            }
                        }else{
                            $userapp['isHack']=false;
                        }
                        if($this->memberstatus[4]){
                            if($this->memberstatus[4] && $this->memberstatus[4]['status']=='1'){
                                $userapp['isVendor']=true;
                            }else{
                                $userapp['isVendor']=false;
                            }
                        }else{
                            $userapp['isVendor']=false;
                        }
                        $this->ajaxReturn(array('success'=>true,'body'=>$userapp));
                    }else{
                        $this->success('登录成功！',U('User/index'));
                    }
                } else {
                    if($source=='app'){
                        $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>false,'error'=>'登录失败')));
                    }else{
                        $this->error($Member->getError());
                    }
                }

            } else { //登录失败
                switch($userins) {
                    case -1: $error = '用户不存在或被禁用！'; break; //系统级别禁用
                    case -2: $error = '密码错误！'; break;
                    default: $error = '未知错误！'; break; // 0-接口参数错误（调试阶段使用）
                }
                if($source=='app'){
                    $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>false,'error'=>$error)));
                }else{
                    $this->error($error);
                }
            }

        } else { //显示登录表单
            $this->assign('pagename','page-login');
            $this->display();
        }
    }

    /* 登录弹出页面 */
    public function logindiag($username = '', $password = '', $verify = ''){
        if(IS_POST){ //登录验证
            /* 检测验证码 */
            if(!check_verify($verify)){
             $this->error('验证码输入错误！');
            }

            /* 调用UC登录接口登录 */
            $userapi=new UserApi;
            $userins = $userapi->login($username, $password);
            if($userins){ //UC登录成功
                /* 登录用户 */
                $Member = D('Member');
                if($Member->login($userins)){ //登录用户
                    //TODO:跳转到登录前页面
                    $this->success('登录成功！',U('User/index'));
                } else {
                    $this->error($Member->getError());
                }

            } else { //登录失败
                switch($userins) {
                    case -1: $error = '用户不存在或被禁用！'; break; //系统级别禁用
                    case -2: $error = '密码错误！'; break;
                    default: $error = '未知错误！'; break; // 0-接口参数错误（调试阶段使用）
                }
                $this->error($error);
            }
        }
    }

    /* 退出登录 */
    public function logout($source='web'){
        if(is_login()){
            D('Member')->logout();
            if($source=='app'){
                $this->ajaxReturn(array('success'=>true,'body'=>true));
            }else{
                $this->success('退出成功！', U('User/login'));
            }
        } else {
            if($source=='app'){
                $this->ajaxReturn(array('success'=>true,'body'=>true));
            }else{
                $this->redirect('User/login');
            }
        }
    }

    /* 验证码，用于登录和注册 */
    public function verify(){
        $verify = new \Think\Verify();
        $verify->entry(1);
    }

    /* ajax 图形验证码校验 */
    public function checkverify(){
        $verify=I('code');
        if(!check_verify($verify)){
            $this->ajaxReturn(array('status'=>'0','info'=>'验证码输入错误！'));
        }else{
            $this->ajaxReturn(array('status'=>'1','info'=>''));
        }
    }

    /*发送手机验证码*/
    function sms_send_verifycode() {
        $number=I('mobile');
        $source=I('source');
        $number = preg_replace("/[^0-9]/", '', $number);
        if(empty($number)){
            if($source=='app'){
                $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>false,'error'=>'请输入手机号')));
            }else{
                $this->ajaxReturn(array('success'=>false,'info'=>'请输入手机号'));
            }
        }
        $userapi=new UserApi;
        $resmobile=$userapi->checkMobile($number);
        if(!$resmobile){
            if($source=='app'){
                $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>false,'error'=>'该手机号已经注册过')));
            }else{
                $this->ajaxReturn(array('success'=>false,'info'=>'该手机号已经注册过'));
            }
        }else{
            $res=$userapi->sms_send_verifycode($number);
            if($res['status']){
                if($source=='app'){
                    $this->ajaxReturn(array('success'=>true,'body'=>array()));
                }else{
                    $this->ajaxReturn(array('success'=>true,'info'=>''));
                }
            }else{
                if($source=='app'){
                    $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>false,'error'=>$res['message'])));
                }else{
                    $this->ajaxReturn(array('success'=>false,'info'=>$res['message']));
                }
            }
        }
    }

    /*已有用户发送手机验证码*/
    function sms_sendsuer_verifycode($source='web') {
        $number=I('mobile');
        $number = preg_replace("/[^0-9]/", '', $number);
        if(empty($number)){
            if($source=='app'){
                $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>false,'error'=>'请输入手机号')));
            }else{
                $this->ajaxReturn(array('success'=>false,'info'=>'请输入手机号'));
            }
        }
        $userapi=new UserApi;
        $resmobile=$userapi->checkMobile($number);
        if($resmobile){
            if($source=='app'){
                $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>false,'error'=>'该手机号尚未注册')));
            }else{
                $this->ajaxReturn(array('success'=>false,'info'=>'该手机号尚未注册'));
            }
        }else{
            $res=$userapi->sms_send_verifycode($number);
            if($res['status']){
                if($source=='app'){
                    $this->ajaxReturn(array('success'=>true,'body'=>array()));
                }else{
                    $this->ajaxReturn(array('success'=>true,'info'=>''));
                }
            }else{
                if($source=='app'){
                    $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>false,'error'=>$res['message'])));
                }else{
                    $this->ajaxReturn(array('success'=>false,'info'=>$res['message']));
                }
            }
        }
    }

    //忘记密码
    public function forgetpass($source='web'){
        if(IS_POST){
            $userapi=new UserApi;
            if($source=='app'){
                $mobile = I('mobile');
                $password = I('password');
                $repassword = I('repassword');
                $validcode = I('validcode');
                /* 检测密码 */
                if($password != $repassword){
                    $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>false,'error'=>'密码和确认密码不一致')));
                } 

                /* 调用注册接口注册用户 */
                if(!$userapi->checkVerifyMobile($mobile,$validcode)){
                    $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>false,'error'=>'手机验证码输入错误')));
                }
                $user=$userapi->getMobileUser($mobile);
                if($user){
                    $res = $userapi->updateForgetPass($user['uid'], $password);
                    if($res['status']){
                        $this->ajaxReturn(array('success'=>true,'body'=>array('isLogined'=>false,'error'=>'密码修改成功，请使用新密码登录')));
                    }else{
                        $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>false,'error'=>$res['info'])));
                    }
                }else{
                    $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>false,'error'=>'该手机号尚未注册')));
                }
            }else{
                $number=I('mobile');
                $password = I('password');
                $repassword = I('repassword');
                $validcode = I('validcode');
                /* 检测密码 */
                if($password != $repassword){
                    $this->error('密码和确认密码不一致！');
                } 

                /* 调用注册接口注册用户 */
                if(!$userapi->checkVerifyMobile($number,$validcode)){
                    $this->error('手机验证码输入错误！');
                }
                $user=$userapi->getMobileUser($number);
                if($user){
                    $res = $userapi->updateForgetPass($user['uid'], $password);
                    if($res['status']){
                        $this->success('密码修改成功，请使用新密码登录！',U('user/login'));
                    }else{
                        $this->error($res['info']);
                    }
                }else{
                    $this->error('该手机号尚未注册');
                }
            }
        }else{
            $this->assign('pagename','page-forgetpass');
            $this->display();
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


    /**
     * 修改基本信息
     */
    public function profileedit($source='web',$token=0){
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
        if ( IS_POST ) {
            if($source=='app'){
                $nickname=I('nickname');
                $sex=I('sex');
                $birthday=I('birthday');
                $truename=I('truename');
                if(!empty($nickname)){
                    $data['nickname']=$nickname;
                }
                if(empty($sex)){
                    $data['sex']=0;
                }else{
                    $data['sex']=$sex;
                }
                if(!empty($birthday)){
                    $data['birthday']=$birthday;
                }
                if(!empty($truename)){
                    $data['truename']=$truename;
                }
                if(empty($data)){
                    $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'没有需要修改的数据')));
                }
            }else{
                $data['nickname']  = I('nickname');
                $data['sex']  = I('sex');
                $data['birthday']  = I('birthyear').'-'.I('birthmonth').'-'.I('birthday');
                $data['truename']  = I('truename');
            }
            $userapi=new UserApi;
            $res = $userapi->updateInfo($uid, $data);
            if($source=='app'){
                if($res['status']){
                    $this->ajaxReturn(array('success'=>true,'body'=>array('isLogined'=>true,'error'=>'修改成功')));
                }else{
                    $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>$res['info'])));
                }
            }else{
                if($res['status']){
                    $this->success('基本信息修改成功！');
                }else{
                    $this->error($res['info']);
                }
            }
        }
    }

    /**
     * 修改密码提交
     */
    public function modpass($source='web',$token=0){
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
        if ( IS_POST ) {
            $userapi=new UserApi;
            if($source=='app'){
                $type   =   I('type');
                $flag=true;
                switch ($type) {
                    case '1':
                        $password   =   I('oldpass');
                        $repassword = I('confirmpass');
                        $data['password'] = I('password');
                        if(empty($password)){
                            $flag=false;
                            $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'请输入原密码')));
                        }
                        if(empty($data['password'])){
                            $flag=false;
                            $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'请输入新密码')));
                        }
                        if(empty($repassword)){
                            $flag=false;
                            $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'请输入确认密码')));
                        }
                        if($data['password'] !== $repassword){
                            $flag=false;
                            $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'您输入的新密码与确认密码不一致')));
                        }
                        break;
                    case '2':
                        $password   =   I('oldpass');
                        $data['mobile'] = I('mobile');
                        $data['password'] = $password;
                        $user=$userapi->info($uid);
                        if(strlen($data['mobile'])!=11){
                            $flag=false;
                            $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'手机号格式不正确')));
                        }
                        if($user['mobile'] && !$data['mobile']){
                            $flag=false;
                            $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'请输入要更换的手机号')));
                        }
                        if($user['mobile']==$data['mobile']){
                            $flag=false;
                            $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'手机号无变动，不需要修改')));
                        }else{
                            $resmobile=$userapi->checkMobile($data['mobile']);
                            if(!$resmobile){
                                $flag=false;
                                $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'要更换的手机号已经存在或格式不正确')));
                            }
                        }
                        break;
                    case '3':
                        $password   =   I('oldpass');
                        $data['email'] = I('email');
                        $data['password'] = $password;
                        $user=$userapi->info($uid);
                        if($user['email'] && !$data['email']){
                            $flag=false;
                            $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'请输入要更换的邮箱')));
                        }
                        if($user['email']==$data['email']){
                            $flag=false;
                            $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'邮箱无变动，不需要修改')));
                        }else{
                            $resemail=$userapi->checkEmail($data['email']);
                            if(!$resemail){
                                $flag=false;
                                $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'要更换的邮箱已经存在或格式不正确')));
                            }
                        }
                        break;
                }
                if($flag){
                    $res = $userapi->updatePass($uid, $password, $data);
                    if($res['status']){
                        $this->ajaxReturn(array('success'=>true,'body'=>array('isLogined'=>true,'error'=>'修改成功')));
                    }else{
                        $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>$res['info'])));
                    }
                }
            }else{
                $password  =   I('oldpass');
                $repassword = I('confirmpass');
                $data['password'] = I('password');
                $data['mobile'] = I('mobile');
                $data['email'] = I('email');

                empty($password) && $this->error('请输入原密码');
                empty($data['password']) && $this->error('请输入新密码');
                empty($repassword) && $this->error('请输入确认密码');

                if($data['password'] !== $repassword){
                    $this->error('您输入的新密码与确认密码不一致');
                }
                $user=$userapi->info($uid);
                if($user['mobile'] && !$data['mobile']){
                    $this->error('请输入要更换的手机号');
                }
                if($user['email'] && !$data['email']){
                    $this->error('请输入要更换的邮箱');
                }
                if($user['email']==$data['email']){
                    unset($data['email']);
                }else{
                    $resemail=$userapi->checkEmail($data['email']);
                    if(!$resemail){
                        $this->error('要更换的邮箱已经存在');
                    }
                }
                if($user['mobile']==$data['mobile']){
                    unset($data['mobile']);
                }else{
                    $resmobile=$userapi->checkMobile($data['mobile']);
                    if(!$resmobile){
                        $this->error('要更换的手机号已经存在');
                    }
                }
                
                $res = $userapi->updatePass($uid, $password, $data);
                if($res['status']){
                    $this->success('修改帐号安全信息成功！');
                }else{
                    $this->error($res['info']);
                }
            }
        }else{
            $this->assign('pagename','page-profile');
            $this->display();
        }
    }

    //添加修改收货地址
    public function myshippingaddroper($source='web',$token=0){
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
        if ( IS_POST ) {
            $datatmp=I('data');
            if($source=='app'){
                $data=json_decode($datatmp,true);
            }else{
                $data=$datatmp;
            }
            $userapi=new UserApi;
            $res = $userapi->shippingaddressoper($uid,$data);
            if($res['status']){
                $this->ajaxReturn(array('success'=>true,'body'=>array('isLogined'=>true,'error'=>$res['error'])));
            }else{
                $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>$res['error'])));
            }
        }
    }

    //消息管理
    public function mymessages(){
        $uid=is_login();
        $msgapi=new MessageApi;
        $res=$msgapi->getMessageList($uid,0);
        $cnt=$msgapi->getNewMessageCount($uid,0);
        $sysapi=new SystemApi;
        $sysapi->writeAccessLog('会员消息');
        $this->assign('msg1list',$res[1]);
        $this->assign('msg2list',$res[2]);
        $this->assign('msg3list',$res[3]);
        $this->assign('msgcnt',$cnt);
        $this->assign('pagename','page-mymessages');
        $this->display();
    }

    //购物车
    public function myshopcart($source='web',$token=0){
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
        $orderapi=new OrderApi;
        $list=$orderapi->getShopCart($uid);
        if($source=='app'){
            $applist=array();
            foreach ($list as $key => $value) {
                $newvalue=$value;
                $newvalue['cartid']=$key;
                foreach ($value['specs'] as $key => $value) {
                    $newvalue['specs']=$value;
                }
                array_push($applist, $newvalue);
            }
            $this->ajaxReturn(array('success'=>true,'body'=>$applist));
        }else{
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
                        $list[$key]['selinventory']=$value1['inventory'];
                        if($value1['image']){
                            $list[$key]['selimage']=$value1['image'];
                        }else{
                            if(empty($value['images'])){
                                $list[$key]['selimage']='';
                            }else{
                                $list[$key]['selimage']=$value['images'][0];
                            }
                        }
                    }
                }
            }
            $this->assign('list',$list);
            $this->assign('uid',$uid);
            $this->assign('pagename','page-myshopcart');
            $this->display();
        }
    }

    public function mycartop($source='web',$token=0){
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
        $orderapi=new OrderApi;
        $oper=I('op');
        $cartid=I('ctid');
        $return=$orderapi->shopCartCommand($oper,$cartid);
        if($source=='app'){
            if($return['status']){
                $this->ajaxReturn(array('success'=>true,'body'=>array('amount'=>$return['amount'],'quantity'=>$return['quantity'])));
            }else{
                $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>$return['info'])));
            }
        }else{
            $this->ajaxReturn($return);
        }
    }

    //订单
    public function myorders($source='web',$token=0){
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
        $status=I('status');
        $orderapi=new OrderApi;
        $list=$orderapi->getMyOrder($uid,$status);
        if($source=='app'){
            $sysapi=new SystemApi;
            $sysapi->writeAccessLog('会员订单','app');
            $this->ajaxReturn($list);
        }else{
            $sysapi=new SystemApi;
            $sysapi->writeAccessLog('会员订单');
            $this->assign('list',$list);
            $this->assign('item',$status);
            $this->assign('pagename','page-myorders');
            $this->display();
        }
    }

    //我的收藏
    public function mybookmarks($source='web',$token=0){
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
        $booktype=I('booktype');
        $userapi=new UserApi;
        $list=$userapi->getMyBookmarks($uid);
        if($source=='app'){
            $sysapi=new SystemApi;
            $sysapi->writeAccessLog('会员收藏','app');
            if($booktype=='1'){
                $this->ajaxReturn(array('success'=>true,'body'=>$list['product']));
            }else{
                $this->ajaxReturn(array('success'=>true,'body'=>$list['shop']));
            }
        }else{
            $sysapi=new SystemApi;
            $sysapi->writeAccessLog('会员收藏');
            $this->assign('shoplist',$list['shop']);
            $this->assign('productlist',$list['product']);
            $this->assign('pagename','page-mybookmarks');
            $this->display();
        }
    }

    //购买过的店铺
    public function myshopstores($source='web',$token=0){
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
        $sysapi=new SystemApi;
        $sysapi->writeAccessLog('会员购买过的店铺');
        $userapi=new UserApi;
        $shoplist=$userapi->getMyBuyShop($uid);
        $this->assign('shoplist',$shoplist);
        $this->assign('pagename','page-myshopstores');
        $this->display();
    }

    //评价管理
    public function mycomments(){
        $this->assign('pagename','page-mycomments');
        $this->display();
    }

    //卡券管理
    public function mycoupon($source='web',$token=0){
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
        $couponapi=new CouponApi;
        $res=$couponapi->getUserCouponList($uid,0);
        if($source=='app'){
            $sysapi=new SystemApi;
            $sysapi->writeAccessLog('会员卡券管理','app');
            $this->ajaxReturn(array('success'=>true,'body'=>$res));
        }else{
            $sysapi=new SystemApi;
            $sysapi->writeAccessLog('会员卡券管理');
            $this->assign('list',$res);
            $this->assign('pagename','page-mycoupon');
            $this->display();
        }
    }

    //创客注册
    public function makerregister($source='web',$token='0'){
        $userapi=new UserApi;
        if(IS_POST){
            if($source=='app'){
                $auth=session('user_auth');
                if($auth['token']!=$token){
                    $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'身份验证错误')));
                }
                $data['uid']=$auth['uid'];
            }else{
                $data['uid']=$_POST['uid'];
            }
            if(!$data['uid']){
                if($source=='app'){
                    $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>false,'error'=>'您还没有登陆')));
                }else{
                    $this->error( '您还没有登陆',U('User/login'),false,5);
                }
            }
            $data['truename']=$_POST['truename'];
            $data['idno']=$_POST['idno'];
            $data['schoolname']=$_POST['schoolname'];
            $data['studentid']=$_POST['studentid'];
            $data['qq']=$_POST['qq'];
            $province=$_POST['province'];
            $city=$_POST['city'];
            $district=$_POST['district'];
            $data['province']=getchinacityname($province);
            $data['city']=getchinacityname($city);
            $data['district']=getchinacityname($district);
            $res=$userapi->makerregister($data);
            if($res['status']){
                if($source=='app'){
                    $this->ajaxReturn(array('success'=>true,'body'=>array('isLogined'=>true,'error'=>'申请已提交，请等待审核','status'=>true)));
                }else{
                    $this->success('申请已提交，请等待审核',U('User/makerregister'));
                }
            }else{
                if($source=='app'){
                    $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'申请成为创客失败，'.$res['info'])));
                }else{
                    $this->error('申请成为创客失败，'.$res['info']);
                }
            }
        }else{
            $uid=is_login();
            $user=$userapi->info($uid);
            $meminfo=$userapi->getMemberInfo($uid);
            $param=array('province'=>530000, 'city'=>530100, 'district'=>530102);
            if(array_key_exists(1, $meminfo)){
                $param['province']=getchinacityid($meminfo[1]['province'],1);
                $param['city']=getchinacityid($meminfo[1]['city'],2);
                $param['district']=getchinacityid($meminfo[1]['district'],3);
            }else{
                $param['provinceorg']=getchinacityid($meminfo[2]['province'],1);
                $param['cityorg']=getchinacityid($meminfo[2]['city'],2);
                $param['districtorg']=getchinacityid($meminfo[2]['district'],3);
            }
            $docapi=new DocumentApi;
            $docid=$docapi->getRegAgreement();
            $this->assign('regagrid',$docid);
            $this->assign('param',$param);
            $this->assign('userinfo',$user);
            $this->assign('meminfo',$meminfo);
            $this->assign('pagename','page-makerregister');
            $this->display();
        }
    }

    //机构创客注册
    public function makerorgregister($source='web',$token='0'){
        $userapi=new UserApi;
        if(IS_POST){
            if($source=='app'){
                $auth=session('user_auth');
                if($auth['token']!=$token){
                    $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'身份验证错误')));
                }
                $data['uid']=$auth['uid'];
            }else{
                $data['uid']=$_POST['uidorg'];
            }
            if(!$data['uid']){
                if($source=='app'){
                    $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>false,'error'=>'您还没有登陆')));
                }else{
                    $this->error( '您还没有登陆',U('User/login'),false,5);
                }
            }
            $data['company']=$_POST['company'];
            $data['busilicense']=$_POST['busilicense'];
            $data['busilicpid']=$_POST['busilicpid'];
            $data['orgpid']=$_POST['orgpid'];
            $data['banklicpid']=$_POST['banklicpid'];
            $data['backno']=$_POST['backno'];
            $data['corporatename']=$_POST['corporatename'];
            $data['corporateidno']=$_POST['corporateidno'];
            $data['linkname']=$_POST['linkname'];
            $data['linkphone']=$_POST['linkphone'];

            $province=$_POST['provinceorg'];
            $city=$_POST['cityorg'];
            $district=$_POST['districtorg'];
            $data['province']=getchinacityname($province);
            $data['city']=getchinacityname($city);
            $data['district']=getchinacityname($district);

            if($data['busilicpid']==0 || $data['busilicpid']==""){
                if($source=='app'){
                    $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'营业执照副本照片不能为空，请上传')));
                }else{
                    $this->error('营业执照副本照片不能为空，请上传');
                }
            }elseif($data['orgpid']==0 || $data['orgpid']==""){
                if($source=='app'){
                    $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'组织机构代码证副本照片不能为空，请上传')));
                }else{
                    $this->error('组织机构代码证副本照片不能为空，请上传');
                }
            }elseif($data['banklicpid']==0 || $data['banklicpid']==""){
                if($source=='app'){
                    $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'组织机构代码证副本照片不能为空，请上传')));
                }else{
                    $this->error('组织机构代码证副本照片不能为空，请上传');
                }
            }else{
                $res=$userapi->makerorgregister($data);
                if($res['status']){
                    if($source=='app'){
                        $this->ajaxReturn(array('success'=>true,'body'=>array('isLogined'=>true,'error'=>'申请已提交，请等待审核','status'=>false)));
                    }else{
                        $this->success('申请已提交，请等待审核',U('User/makerregister'));
                    }
                }else{
                    if($source=='app'){
                        $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'申请成为机构创客失败，'.$res['info'])));
                    }else{
                        $this->error('申请成为机构创客失败，'.$res['info']);
                    }
                }
            }
        }
    }

    //供应商注册
    public function supplierregister($source='web',$token='0'){
        $userapi=new UserApi;
        if(IS_POST){
            if($source=='app'){
                $auth=session('user_auth');
                if($auth['token']!=$token){
                    $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'身份验证错误')));
                }
                $data['uid']=$auth['uid'];
            }else{
                $data['uid']=$_POST['uid'];
            }
            if(!$data['uid']){
                if($source=='app'){
                    $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>false,'error'=>'您还没有登陆')));
                }else{
                    $this->error( '您还没有登陆',U('User/login'),false,5);
                }
            }
            $data['company']=$_POST['company'];
            $data['busilicense']=$_POST['busilicense'];
            $data['busilicpid']=$_POST['busilicpid'];
            $data['orgpid']=$_POST['orgpid'];
            $data['banklicpid']=$_POST['banklicpid'];
            $data['backno']=$_POST['backno'];
            $data['corporatename']=$_POST['corporatename'];
            $data['corporateidno']=$_POST['corporateidno'];
            $data['linkname']=$_POST['linkname'];
            $data['linkphone']=$_POST['linkphone'];
            $province=$_POST['province'];
            $city=$_POST['city'];
            $district=$_POST['district'];
            $data['province']=getchinacityname($province);
            $data['city']=getchinacityname($city);
            $data['district']=getchinacityname($district);

            if($data['busilicpid']==0 || $data['busilicpid']==""){
                if($source=='app'){
                    $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'营业执照副本照片不能为空，请上传')));
                }else{
                    $this->error('营业执照副本照片不能为空，请上传');
                }
            }else{
                $res=$userapi->supplierregister($data);
                if($res['status']){
                    if($source=='app'){
                        $this->ajaxReturn(array('success'=>true,'body'=>array('isLogined'=>true,'error'=>'申请已提交，请等待审核')));
                    }else{
                        $this->success('申请已提交，请等待审核',U('User/supplierregister'));
                    }
                }else{
                    if($source=='app'){
                        $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'申请成为供应商失败，'.$res['info'])));
                    }else{
                        $this->error('申请成为供应商失败，'.$res['info']);
                    }
                }
            }
        }else{
            $uid=is_login();
            $user=$userapi->info($uid);
            $meminfo=$userapi->getMemberInfo($uid);
            $param=array('province'=>530000, 'city'=>530100, 'district'=>530102);
            if(array_key_exists(4, $meminfo)){
                $param['province']=getchinacityid($meminfo[4]['province'],1);
                $param['city']=getchinacityid($meminfo[4]['city'],2);
                $param['district']=getchinacityid($meminfo[4]['district'],3);
            }
            $docapi=new DocumentApi;
            $docid=$docapi->getRegAgreement();
            $this->assign('regagrid',$docid);
            $this->assign('param',$param);
            $this->assign('meminfo',$meminfo);
            $this->assign('userinfo',$user);
            $this->assign('pagename','page-supplierregister');
            $this->display();
        }
    }

    public function getMemberExtraInfo(){
        $token=I('token');
        if($token=='' || $token=='0'){
            $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>false,'error'=>'您还没有登陆')));
        }else{
            $auth=session('user_auth');
            if($auth['token']!=$token){
                $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>false,'error'=>'您还没有登陆')));
            }
        }
        $userapi=new UserApi;
        $meminfo=$userapi->getMemberInfo($auth['uid']);
        if(array_key_exists('1', $meminfo)){
            $meminfo['1']['province_id']=getchinacityid($meminfo['1']['province'],1);
            $meminfo['1']['city_id']=getchinacityid($meminfo['1']['city'],2);
            $meminfo['1']['district_id']=getchinacityid($meminfo['1']['district'],3);
        }
        if(array_key_exists('2', $meminfo)){
            $meminfo['2']['province_id']=getchinacityid($meminfo['2']['province'],1);
            $meminfo['2']['city_id']=getchinacityid($meminfo['2']['city'],2);
            $meminfo['2']['district_id']=getchinacityid($meminfo['2']['district'],3);
            unset($meminfo['2']['busilicpidorgname']);
            unset($meminfo['2']['orgpidorgname']);
            unset($meminfo['2']['banklicpidorgname']);
        }
        if(array_key_exists('4', $meminfo)){
            $meminfo['4']['province_id']=getchinacityid($meminfo['4']['province'],1);
            $meminfo['4']['city_id']=getchinacityid($meminfo['4']['city'],2);
            $meminfo['4']['district_id']=getchinacityid($meminfo['4']['district'],3);
            unset($meminfo['4']['busilicpidorgname']);
            unset($meminfo['4']['orgpidorgname']);
            unset($meminfo['4']['banklicpidorgname']);
        }
        $this->ajaxReturn(array('success'=>true,'body'=>$meminfo));
    }

    public function getRegAgreement($source='app'){
        $docapi=new DocumentApi;
        $docid=$docapi->getRegAgreement();
        $Document = D('Document');
        $info = $Document->detail($docid);
        if($source=='app'){
            $this->ajaxReturn(array('success'=>true,'body'=>$info['content']));
        }
    }

    //头像修改
    public function portraitsave($source='web',$token=0){
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
        if($source=='web'){
            $imgbase64=I('cavportrait');
            $imgtype=I('filetype');
            $orgname=I('orgname');
            $files['image_file']=array(
                "name"=>$orgname,
                "type"=>$imgtype,
                "imgbase64"=>$imgbase64,
                );
        }else{
            $data=I('files');
            $files=json_decode($data,true);
        }
        $uid = is_login();
        $userapi=new UserApi;
        $info = $userapi->updatePortrait($uid,$files);
        if($source=='app'){
            if($info['status']){
                $this->ajaxReturn(array('success'=>true,'body'=>$info));
            }else{
                $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>$info['info'])));
            }
        }else{
            if($info['status']){
                $this->success('头像修改成功',U('User/index'));
            }else{
                $this->error('头像修改失败,'.$info['info']);
            }
        }
    }

    //申请成功提示
    // public function registersuccess(){
    //     $userapi=new UserApi;
    //     $user=$userapi->info(UID);
    //     $makerapi=new MakerApi;
    //     $makerreg=$makerapi->registerprocess(UID);
    //     $this->assign('makerreg',$makerreg);
    //     $this->assign('userinfo',$user);
    //     $this->assign('pagename','page-registersuccess');
    //     $this->display();
    // }

    //审核进度
    public function progresssupplier(){
        $this->assign('pagename','page-progresssupplier');
        $this->display();
    }

    //审核进度
    public function progressmaker(){
        $this->assign('pagename','page-progressmaker');
        $this->display();
    }

    //供应商首页
    public function supplierindex(){
        if($this->memberstatus[4]){
            if($this->memberstatus[4] && $this->memberstatus[4]['status']!='1'){
                if($source=='app'){
                    $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'您的供应商申请还未通过，请耐心等待')));
                }else{
                    $this->error('您的供应商申请还未通过，请耐心等待',U('User/supplierregister'));
                }
            }
        }else{
            if($source=='app'){
                $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'您还没有成为供应商，请先申请成为供应商')));
            }else{
                $this->error('您还没有成为供应商，请先申请成为供应商',U('User/supplierregister'));
            }
        }
        $sysapi=new SystemApi;
        $sysapi->writeAccessLog('供应商首页');
        $this->assign('pagename','page-supplierindex');
        $this->display();
    }

    //创客首页
    public function makerindex(){
        if($this->memberstatus[1] || $this->memberstatus[2]){
            if($this->memberstatus[1] && $this->memberstatus[1]['status']!='1'){
                if($source=='app'){
                    $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'您的创客申请还未通过，请耐心等待')));
                }else{
                    $this->error('您的创客申请还未通过，请耐心等待',U('User/makerregister'));
                }
            }
            if($this->memberstatus[2] && $this->memberstatus[2]['status']!='1'){
                if($source=='app'){
                    $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'您的创客申请还未通过，请耐心等待')));
                }else{
                    $this->error('您的创客申请还未通过，请耐心等待',U('User/makerregister'));
                }
            }
        }else{
            if($source=='app'){
                $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'您还没有成为创客，请先申请成为创客')));
            }else{
                $this->error('您还没有成为创客，请先申请成为创客',U('User/makerregister'));
            }
        }
        $sysapi=new SystemApi;
        $sysapi->writeAccessLog('创客首页');
        $this->assign('pagename','page-makerindex');
        $this->display();
    }

    //创客订单
    public function makerorders($source='web',$token=0){
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
        if($this->memberstatus[1] || $this->memberstatus[2]){
            if($this->memberstatus[1] && $this->memberstatus[1]['status']!='1'){
                if($source=='app'){
                    $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'您的创客申请还未通过，请耐心等待')));
                }else{
                    $this->error('您的创客申请还未通过，请耐心等待',U('User/makerregister'));
                }
            }
            if($this->memberstatus[2] && $this->memberstatus[2]['status']!='1'){
                if($source=='app'){
                    $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'您的创客申请还未通过，请耐心等待')));
                }else{
                    $this->error('您的创客申请还未通过，请耐心等待',U('User/makerregister'));
                }
            }
        }else{
            if($source=='app'){
                $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'您还没有成为创客，请先申请成为创客')));
            }else{
                $this->error('您还没有成为创客，请先申请成为创客',U('User/makerregister'));
            }
        }
        $orderapi=new OrderApi;
        $status=I('status');
        $list=$orderapi->getStoreOrder($uid,$status);
        if($source=='app'){
            $sysapi=new SystemApi;
            $sysapi->writeAccessLog('创客订单','app');
            $this->ajaxReturn($list);
        }else{
            $sysapi=new SystemApi;
            $sysapi->writeAccessLog('创客订单');
            $this->assign('list',$list);
            $this->assign('item',$status);
            $this->assign('pagename','page-makerorders');
            $this->display();
        }
    }

    //创客购物车
    public function makercart($source='web'){
        if($this->memberstatus[1] || $this->memberstatus[2]){
            if($this->memberstatus[1] && $this->memberstatus[1]['status']!='1'){
                if($source=='app'){
                    $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'您的创客申请还未通过，请耐心等待')));
                }else{
                    $this->error('您的创客申请还未通过，请耐心等待',U('User/makerregister'));
                }
            }
            if($this->memberstatus[2] && $this->memberstatus[2]['status']!='1'){
                if($source=='app'){
                    $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'您的创客申请还未通过，请耐心等待')));
                }else{
                    $this->error('您的创客申请还未通过，请耐心等待',U('User/makerregister'));
                }
            }
        }else{
            if($source=='app'){
                $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'您还没有成为创客，请先申请成为创客')));
            }else{
                $this->error('您还没有成为创客，请先申请成为创客',U('User/makerregister'));
            }
        }
        $uid = is_login();
        $orderapi=new OrderApi;
        $list=$orderapi->getStoreCart($uid);
        if($source=='app'){
            $applist=array();
            foreach ($list as $key => $value) {
                $newvalue=$value;
                $newvalue['cartid']=$key;
                foreach ($value['specs'] as $key => $value) {
                    $newvalue['specs']=$value;
                }
                array_push($applist, $newvalue);
            }
            $this->ajaxReturn(array('success'=>true,'body'=>$applist));
        }else{
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
                        $list[$key]['selinventory']=$value1['inventory'];
                        if($value1['image']){
                            $list[$key]['selimage']=$value1['image'];
                        }else{
                            if(empty($value['images'])){
                                $list[$key]['selimage']='';
                            }else{
                                $list[$key]['selimage']=$value['images'][0];
                            }
                        }
                    }
                }
            }
            $this->assign('list',$list);
            $this->assign('pagename','page-makercart');
            $this->display();
        }
    }

    public function makercartop($source='web',$token=0){
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
        if($this->memberstatus[1] || $this->memberstatus[2]){
            if($this->memberstatus[1] && $this->memberstatus[1]['status']!='1'){
                if($source=='app'){
                    $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'您的创客申请还未通过，请耐心等待')));
                }else{
                    $this->error('您的创客申请还未通过，请耐心等待',U('User/makerregister'));
                }
            }
            if($this->memberstatus[2] && $this->memberstatus[2]['status']!='1'){
                if($source=='app'){
                    $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'您的创客申请还未通过，请耐心等待')));
                }else{
                    $this->error('您的创客申请还未通过，请耐心等待',U('User/makerregister'));
                }
            }
        }else{
            if($source=='app'){
                $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'您还没有成为创客，请先申请成为创客')));
            }else{
                $this->error('您还没有成为创客，请先申请成为创客',U('User/makerregister'));
            }
        }
        $orderapi=new OrderApi;
        $oper=I('op');
        $cartid=I('ctid');
        $return=$orderapi->storeCartCommand($oper,$cartid);
        if($source=='app'){
            if($return['status']){
                $this->ajaxReturn(array('success'=>true,'body'=>array('amount'=>$return['amount'],'quantity'=>$return['quantity'])));
            }else{
                $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>$return['info'])));
            }
        }else{
            $this->ajaxReturn($return);
        }
    }

    //创客店铺管理
    public function makerstore($source='web',$token='0'){
        if($this->memberstatus[1] || $this->memberstatus[2]){
            if($this->memberstatus[1] && $this->memberstatus[1]['status']!='1'){
                if($source=='app'){
                    $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'您的创客申请还未通过，请耐心等待')));
                }else{
                    $this->error('您的创客申请还未通过，请耐心等待',U('User/makerregister'));
                }
            }
            if($this->memberstatus[2] && $this->memberstatus[2]['status']!='1'){
                if($source=='app'){
                    $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'您的创客申请还未通过，请耐心等待')));
                }else{
                    $this->error('您的创客申请还未通过，请耐心等待',U('User/makerregister'));
                }
            }
        }else{
            if($source=='app'){
                $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'您还没有成为创客，请先申请成为创客')));
            }else{
                $this->error('您还没有成为创客，请先申请成为创客',U('User/makerregister'));
            }
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
        $shopapi=new ShopApi;
        $tpllist=$shopapi->getShopTpl();
        $shop=$shopapi->getMyShop($uid);
        if($source=='app'){
            $sysapi=new SystemApi;
            $sysapi->writeAccessLog('创客店铺管理','app');
            $this->ajaxReturn(array('success'=>true,'body'=>$shop));
        }else{
            $sysapi=new SystemApi;
            $sysapi->writeAccessLog('创客店铺管理');
            $this->assign('uid',$uid);
            $this->assign('tpllist',$tpllist);
            $this->assign('shop',$shop);
            $this->assign('pagename','page-makerstore');
            $this->display();
        }
    }

    //创客设置店铺模板
    public function makerstoretpl($source='web'){
        if($this->memberstatus[1] || $this->memberstatus[2]){
            if($this->memberstatus[1] && $this->memberstatus[1]['status']!='1'){
                if($source=='app'){
                    $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'您的创客申请还未通过，请耐心等待')));
                }else{
                    $this->error('您的创客申请还未通过，请耐心等待',U('User/makerregister'));
                }
            }
            if($this->memberstatus[2] && $this->memberstatus[2]['status']!='1'){
                if($source=='app'){
                    $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'您的创客申请还未通过，请耐心等待')));
                }else{
                    $this->error('您的创客申请还未通过，请耐心等待',U('User/makerregister'));
                }
            }
        }else{
            if($source=='app'){
                $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'您还没有成为创客，请先申请成为创客')));
            }else{
                $this->error('您还没有成为创客，请先申请成为创客',U('User/makerregister'));
            }
        }
        $tplid=I('tplid');
        if($tplid=='' || $tplid=='0'){
            $tplid=1;
        }
        $uid=is_login();
        $shopapi=new ShopApi;
        $res=$shopapi->updateShopTpl($uid,$tplid);
        if($res['status']){
            if($source=='app'){
                $this->ajaxReturn(array('success'=>true,'body'=>array('isLogined'=>true,'error'=>'店铺模板设置成功')));
            }else{
                $this->success('店铺模板设置成功');
            }
        }else{
            if($source=='app'){
                $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'店铺模板设置失败')));
            }else{
                $this->error('店铺模板设置失败');
            }
        }
    }

    //创客店铺设置
    public function makerstoreset($source='web',$token='0'){
        if($this->memberstatus[1] || $this->memberstatus[2]){
            if($this->memberstatus[1] && $this->memberstatus[1]['status']!='1'){
                if($source=='app'){
                    $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'您的创客申请还未通过，请耐心等待')));
                }else{
                    $this->error('您的创客申请还未通过，请耐心等待',U('User/makerregister'));
                }
            }
            if($this->memberstatus[2] && $this->memberstatus[2]['status']!='1'){
                if($source=='app'){
                    $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'您的创客申请还未通过，请耐心等待')));
                }else{
                    $this->error('您的创客申请还未通过，请耐心等待',U('User/makerregister'));
                }
            }
        }else{
            if($source=='app'){
                $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'您还没有成为创客，请先申请成为创客')));
            }else{
                $this->error('您还没有成为创客，请先申请成为创客',U('User/makerregister'));
            }
        }
        $data=array();
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
        if($source=='app'){
            if(!empty(I('name'))){
                $data['name']=I('name');
            }
            if(!empty(I('comment'))){
                $data['comment']=I('comment');
            }
        }else{
            $data['name']=I('name');
            $data['logo']=I('imglogo');
            $data['comment']=I('comment');
            $data['backgroud']=I('imgbackgroup');
        }

        $shopapi=new ShopApi;
        $res=$shopapi->newShop($uid,$data);
        if($res['status']){
            if($source=='app'){
                $this->ajaxReturn(array('success'=>true,'body'=>array('isLogined'=>true,'error'=>'店铺设置成功')));
            }else{
                $this->success('店铺设置成功',U('User/makerstore'));
            }
        }else{
            if($source=='app'){
                $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>$res['info'])));
            }else{
                $this->error($res['info']);
            }
        }
    }

    //创客店铺管理图片列表
    public function makerstorepic(){
        if($this->memberstatus[1] || $this->memberstatus[2]){
            if($this->memberstatus[1] && $this->memberstatus[1]['status']!='1'){
                if($source=='app'){
                    $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'您的创客申请还未通过，请耐心等待')));
                }else{
                    $this->error('您的创客申请还未通过，请耐心等待',U('User/makerregister'));
                }
            }
            if($this->memberstatus[2] && $this->memberstatus[2]['status']!='1'){
                if($source=='app'){
                    $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'您的创客申请还未通过，请耐心等待')));
                }else{
                    $this->error('您的创客申请还未通过，请耐心等待',U('User/makerregister'));
                }
            }
        }else{
            if($source=='app'){
                $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'您还没有成为创客，请先申请成为创客')));
            }else{
                $this->error('您还没有成为创客，请先申请成为创客',U('User/makerregister'));
            }
        }
        $this->assign('pagename','page-makerstorepic');
        $this->display();
    }

    //创客慧爱币
    public function makerhcoin(){
        if($this->memberstatus[1] || $this->memberstatus[2]){
            if($this->memberstatus[1] && $this->memberstatus[1]['status']!='1'){
                if($source=='app'){
                    $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'您的创客申请还未通过，请耐心等待')));
                }else{
                    $this->error('您的创客申请还未通过，请耐心等待',U('User/makerregister'));
                }
            }
            if($this->memberstatus[2] && $this->memberstatus[2]['status']!='1'){
                if($source=='app'){
                    $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'您的创客申请还未通过，请耐心等待')));
                }else{
                    $this->error('您的创客申请还未通过，请耐心等待',U('User/makerregister'));
                }
            }
        }else{
            if($source=='app'){
                $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'您还没有成为创客，请先申请成为创客')));
            }else{
                $this->error('您还没有成为创客，请先申请成为创客',U('User/makerregister'));
            }
        }
        $sysapi=new SystemApi;
        $sysapi->writeAccessLog('创客慧爱币');
        $uid=is_login();
        $userapi=new UserApi;
        $userinfo=$userapi->info($uid);
        $userinfo['hcoin']=$userinfo['hcoin']/100;
        $hcoinrecord=$userapi->getHCoinRecord($uid);
        $this->assign('userinfo',$userinfo);
        $this->assign('hcoinrecord',$hcoinrecord);
        $this->assign('pagename','page-makerhcoin');
        $this->display();
    }

    //创客认证
    public function makerauth(){
        if($this->memberstatus[1] || $this->memberstatus[2]){
            if($this->memberstatus[1] && $this->memberstatus[1]['status']!='1'){
                if($source=='app'){
                    $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'您的创客申请还未通过，请耐心等待')));
                }else{
                    $this->error('您的创客申请还未通过，请耐心等待',U('User/makerregister'));
                }
            }
            if($this->memberstatus[2] && $this->memberstatus[2]['status']!='1'){
                if($source=='app'){
                    $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'您的创客申请还未通过，请耐心等待')));
                }else{
                    $this->error('您的创客申请还未通过，请耐心等待',U('User/makerregister'));
                }
            }
        }else{
            if($source=='app'){
                $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'您还没有成为创客，请先申请成为创客')));
            }else{
                $this->error('您还没有成为创客，请先申请成为创客',U('User/makerregister'));
            }
        }
        $this->assign('pagename','page-makerauth');
        $this->display();
    }

    //创客评价管理
    public function makercomments(){
        if($this->memberstatus[1] || $this->memberstatus[2]){
            if($this->memberstatus[1] && $this->memberstatus[1]['status']!='1'){
                if($source=='app'){
                    $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'您的创客申请还未通过，请耐心等待')));
                }else{
                    $this->error('您的创客申请还未通过，请耐心等待',U('User/makerregister'));
                }
            }
            if($this->memberstatus[2] && $this->memberstatus[2]['status']!='1'){
                if($source=='app'){
                    $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'您的创客申请还未通过，请耐心等待')));
                }else{
                    $this->error('您的创客申请还未通过，请耐心等待',U('User/makerregister'));
                }
            }
        }else{
            if($source=='app'){
                $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'您还没有成为创客，请先申请成为创客')));
            }else{
                $this->error('您还没有成为创客，请先申请成为创客',U('User/makerregister'));
            }
        }
        $sysapi=new SystemApi;
        $sysapi->writeAccessLog('创客评价管理');
        $this->assign('pagename','page-makercomments');
        $this->display();
    }

    //创客消息管理
    public function makermessages($source='web'){
        if($this->memberstatus[1] || $this->memberstatus[2]){
            if($this->memberstatus[1] && $this->memberstatus[1]['status']!='1'){
                if($source=='app'){
                    $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'您的创客申请还未通过，请耐心等待')));
                }else{
                    $this->error('您的创客申请还未通过，请耐心等待',U('User/makerregister'));
                }
            }
            if($this->memberstatus[2] && $this->memberstatus[2]['status']!='1'){
                if($source=='app'){
                    $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'您的创客申请还未通过，请耐心等待')));
                }else{
                    $this->error('您的创客申请还未通过，请耐心等待',U('User/makerregister'));
                }
            }
        }else{
            if($source=='app'){
                $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'您还没有成为创客，请先申请成为创客')));
            }else{
                $this->error('您还没有成为创客，请先申请成为创客',U('User/makerregister'));
            }
        }
        $uid=is_login();
        $msgapi=new MessageApi;
        if(array_key_exists(1, $this->memberstatus)){
            $res=$msgapi->getMessageList($uid,1);
            $cnt=$msgapi->getNewMessageCount($uid,1);
        }else{
            $res=$msgapi->getMessageList($uid,2);
            $cnt=$msgapi->getNewMessageCount($uid,2);
        }
        $sysapi=new SystemApi;
        $sysapi->writeAccessLog('创客消息管理');
        $this->assign('msg1list',$res[1]);
        $this->assign('msg2list',$res[2]);
        $this->assign('msg3list',$res[3]);
        $this->assign('msgcnt',$cnt);
        $this->assign('pagename','page-makermessages');
        $this->display();
    }

    //创客手册
    public function makerhandbook(){
        if($this->memberstatus[1] || $this->memberstatus[2]){
            if($this->memberstatus[1] && $this->memberstatus[1]['status']!='1'){
                if($source=='app'){
                    $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'您的创客申请还未通过，请耐心等待')));
                }else{
                    $this->error('您的创客申请还未通过，请耐心等待',U('User/makerregister'));
                }
            }
            if($this->memberstatus[2] && $this->memberstatus[2]['status']!='1'){
                if($source=='app'){
                    $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'您的创客申请还未通过，请耐心等待')));
                }else{
                    $this->error('您的创客申请还未通过，请耐心等待',U('User/makerregister'));
                }
            }
        }else{
            if($source=='app'){
                $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'您还没有成为创客，请先申请成为创客')));
            }else{
                $this->error('您还没有成为创客，请先申请成为创客',U('User/makerregister'));
            }
        }
        $sysapi=new SystemApi;
        $sysapi->writeAccessLog('创客手册');
        $docapi=new DocumentApi;
        $doc=$docapi->getDocumentByName('helpmaker');
        $this->assign('content',$doc['content']);
        $this->assign('pagename','page-makerhandbook');
        $this->display();
    }

    //创客卡券管理
    public function makercoupon($source='web',$token=0){
        if($this->memberstatus[1] || $this->memberstatus[2]){
            if($this->memberstatus[1] && $this->memberstatus[1]['status']!='1'){
                if($source=='app'){
                    $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'您的创客申请还未通过，请耐心等待')));
                }else{
                    $this->error('您的创客申请还未通过，请耐心等待',U('User/makerregister'));
                }
            }
            if($this->memberstatus[2] && $this->memberstatus[2]['status']!='1'){
                if($source=='app'){
                    $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'您的创客申请还未通过，请耐心等待')));
                }else{
                    $this->error('您的创客申请还未通过，请耐心等待',U('User/makerregister'));
                }
            }
        }else{
            if($source=='app'){
                $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'您还没有成为创客，请先申请成为创客')));
            }else{
                $this->error('您还没有成为创客，请先申请成为创客',U('User/makerregister'));
            }
        }
        $uid=0;
        $auth=session('user_auth');
        if($source=='app'){
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
        $couponapi=new CouponApi;
        if(IS_POST){
            $data['couponvalue']=I('couponvalue');
            $couponcount=I('couponcount');
            $data['begindate']=I('begindate');
            $data['enddate']=I('enddate');
            $data['tplid']=I('tplid');
            $data['sourceid']=I('shopid');
            if(empty($data['couponvalue']) || intval($data['couponvalue'])<=0){
                $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'优惠金额无效')));
            }
            if(empty($couponcount) || intval($couponcount)<=0){
                $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'优惠卷数量无效')));
            }elseif($couponcount>200){
                $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'一次最多制作200张优惠卷')));
            }
            if(empty($data['begindate']) || empty($data['enddate'])){
                $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'优惠卷有效期无效')));
            }
            if(empty($data['tplid']) || empty($data['tplid'])){
                $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'请选择优惠卷模板')));
            }
            if(empty($data['sourceid'])){
                $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'店铺信息错误，请先设置店铺信息')));
            }
            $data['uid']=$uid;
            $data['sourcetype']='shop';
            $data['couponmethod']='fix';
            $data['status']='1';
            $data['created']=time();
            $data['couponvalue']=$data['couponvalue']*100;
            if($couponapi->newShopCoupon($data,$couponcount)){
                $this->ajaxReturn(array('success'=>true,'body'=>array('isLogined'=>true,'error'=>'优惠卷制作成功')));
            }else{
                $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'优惠卷制作失败')));
            }
        }else{
            $list=$couponapi->getShopCouponList($auth['shopid']);
            if($source=='app'){
                $sysapi=new SystemApi;
                $sysapi->writeAccessLog('创客卡券管理','app');
                $this->ajaxReturn(array('success'=>true,'body'=>$list));
            }else{
                $sysapi=new SystemApi;
                $sysapi->writeAccessLog('创客卡券管理');
                $this->assign('list',$list);
                $this->assign('shopid',$auth['shopid']);
                $this->assign('pagename','page-makercoupon');
                $this->display();
            }
        }
    }

    //创客发送优惠券给用户管理
    public function makercouponsend($source='web',$token=0){
        if($this->memberstatus[1] || $this->memberstatus[2]){
            if($this->memberstatus[1] && $this->memberstatus[1]['status']!='1'){
                if($source=='app'){
                    $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'您的创客申请还未通过，请耐心等待')));
                }else{
                    $this->error('您的创客申请还未通过，请耐心等待',U('User/makerregister'));
                }
            }
            if($this->memberstatus[2] && $this->memberstatus[2]['status']!='1'){
                if($source=='app'){
                    $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'您的创客申请还未通过，请耐心等待')));
                }else{
                    $this->error('您的创客申请还未通过，请耐心等待',U('User/makerregister'));
                }
            }
        }else{
            if($source=='app'){
                $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'您还没有成为创客，请先申请成为创客')));
            }else{
                $this->error('您还没有成为创客，请先申请成为创客',U('User/makerregister'));
            }
        }
        $uid=0;
        $auth=session('user_auth');
        if($source=='app'){
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
        $couponapi=new CouponApi;
        $shopapi=new ShopApi;
        $couponid=I('cupid');
        $couponinfo=$couponapi->getShopCouponinfo($couponid);
        if(IS_POST){
            $selfans=I('selfans');
            if(empty($couponid)){
                if($source=='app'){
                    $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'优惠卷错误')));
                }else{
                    $this->error('优惠卷错误');
                }
            }
            if(empty($selfans) || count($selfans)==0){
                if($source=='app'){
                    $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'请选择粉丝')));
                }else{
                    $this->error('请选择粉丝');
                }
            }
            if($couponinfo['unget']<count($selfans)){
                if($source=='app'){
                    $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'优惠卷数量不足')));
                }else{
                    $this->error('优惠卷数量不足');
                }
            }
            if($couponapi->sendShopCouponToUser($couponid,$selfans)){
                if($source=='app'){
                    $this->ajaxReturn(array('success'=>true,'body'=>array('isLogined'=>true,'error'=>'优惠卷发放成功')));
                }else{
                    $this->success('优惠卷发放成功',U('user/makercoupon'));
                }
            }else{
                if($source=='app'){
                    $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'优惠卷发放失败')));
                }else{
                    $this->success('优惠卷发放失败');
                }
            }
        }else{
            $fans=$shopapi->getShopFans($auth['shopid']);
            if($couponinfo['unget']<=0){
                if($source=='app'){
                    $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>false,'error'=>'该优惠卷已经发完')));
                }else{
                    $this->error('该优惠卷已经发完');
                }
            }
            if($source=='app'){
                $this->ajaxReturn(array('success'=>true,'body'=>$list));
            }else{
                $this->assign('couponinfo',$couponinfo);
                $this->assign('fans',$fans);
                $this->assign('pagename','page-makercouponsend');
                $this->display();
            }
        }
    }

    //创客活动管理
    public function makerevent($source='web',$token=0){
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
        if($this->memberstatus[1] || $this->memberstatus[2]){
            if($this->memberstatus[1] && $this->memberstatus[1]['status']!='1'){
                if($source=='app'){
                    $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'您的创客申请还未通过，请耐心等待')));
                }else{
                    $this->error('您的创客申请还未通过，请耐心等待',U('User/makerregister'));
                }
            }
            if($this->memberstatus[2] && $this->memberstatus[2]['status']!='1'){
                if($source=='app'){
                    $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'您的创客申请还未通过，请耐心等待')));
                }else{
                    $this->error('您的创客申请还未通过，请耐心等待',U('User/makerregister'));
                }
            }
        }else{
            if($source=='app'){
                $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'您还没有成为创客，请先申请成为创客')));
            }else{
                $this->error('您还没有成为创客，请先申请成为创客',U('User/makerregister'));
            }
        }
        $docapi=new DocumentApi;
        $category=$docapi->getEventCategory('hackevent');
        $list = $docapi->getDocumentList($uid,$category);
        if($source=='app'){
            $applist=array();
            foreach ($category as $key => $value) {
                $tmpcate=$value;
                $tmpcate['id']=intval($tmpcate['id']);
                $tmpcate['event']=array();
                foreach ($list[$value['id']] as $key1 => &$value1) {
                    if($value1['signup']=='0'){
                        $value1['signup']=false;
                    }else{
                        $value1['signup']=true;
                    }
                    array_push($tmpcate['event'],$value1);
                }
                array_push($applist,$tmpcate);
            }
            $sysapi=new SystemApi;
            $sysapi->writeAccessLog('创客活动管理','app');
            $this->ajaxReturn(array('success'=>true,'body'=>$applist));
        }else{
            $sysapi=new SystemApi;
            $sysapi->writeAccessLog('创客活动管理');
            $this->assign('category', $category);
            $this->assign('list', $list);
            $this->assign('pagename','page-makerevent');
            $this->display();
        }
    }

    //创客成就管理
    public function makerachievement(){
        if($this->memberstatus[1] || $this->memberstatus[2]){
            if($this->memberstatus[1] && $this->memberstatus[1]['status']!='1'){
                if($source=='app'){
                    $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'您的创客申请还未通过，请耐心等待')));
                }else{
                    $this->error('您的创客申请还未通过，请耐心等待',U('User/makerregister'));
                }
            }
            if($this->memberstatus[2] && $this->memberstatus[2]['status']!='1'){
                if($source=='app'){
                    $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'您的创客申请还未通过，请耐心等待')));
                }else{
                    $this->error('您的创客申请还未通过，请耐心等待',U('User/makerregister'));
                }
            }
        }else{
            if($source=='app'){
                $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'您还没有成为创客，请先申请成为创客')));
            }else{
                $this->error('您还没有成为创客，请先申请成为创客',U('User/makerregister'));
            }
        }
        $sysapi=new SystemApi;
        $sysapi->writeAccessLog('创客成就管理');
        $this->assign('pagename','page-makerachievement');
        $this->display();
    }

    //创客特权管理
    public function makerprivilege(){
        if($this->memberstatus[1] || $this->memberstatus[2]){
            if($this->memberstatus[1] && $this->memberstatus[1]['status']!='1'){
                if($source=='app'){
                    $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'您的创客申请还未通过，请耐心等待')));
                }else{
                    $this->error('您的创客申请还未通过，请耐心等待',U('User/makerregister'));
                }
            }
            if($this->memberstatus[2] && $this->memberstatus[2]['status']!='1'){
                if($source=='app'){
                    $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'您的创客申请还未通过，请耐心等待')));
                }else{
                    $this->error('您的创客申请还未通过，请耐心等待',U('User/makerregister'));
                }
            }
        }else{
            if($source=='app'){
                $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'您还没有成为创客，请先申请成为创客')));
            }else{
                $this->error('您还没有成为创客，请先申请成为创客',U('User/makerregister'));
            }
        }
        $sysapi=new SystemApi;
        $sysapi->writeAccessLog('创客特权管理');
        $this->assign('pagename','page-makerprivilege');
        $this->display();
    }

    //供应商消息管理
    public function suppliermessages(){
        if($this->memberstatus[4]){
            if($this->memberstatus[4] && $this->memberstatus[4]['status']!='1'){
                if($source=='app'){
                    $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'您的供应商申请还未通过，请耐心等待')));
                }else{
                    $this->error('您的供应商申请还未通过，请耐心等待',U('User/supplierregister'));
                }
            }
        }else{
            if($source=='app'){
                $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'您还没有成为供应商，请先申请成为供应商')));
            }else{
                $this->error('您还没有成为供应商，请先申请成为供应商',U('User/supplierregister'));
            }
        }
        $sysapi=new SystemApi;
        $sysapi->writeAccessLog('供应商消息管理');
        $uid=is_login();
        $msgapi=new MessageApi;
        $res=$msgapi->getMessageList($uid,4);
        $cnt=$msgapi->getNewMessageCount($uid,4);
        $this->assign('msg1list',$res[1]);
        $this->assign('msg2list',$res[2]);
        $this->assign('msg3list',$res[3]);
        $this->assign('msgcnt',$cnt);
        $this->assign('pagename','page-suppliermessages');
        $this->display();
    }

    //供应商订单管理
    public function supplierorders(){
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
        if($this->memberstatus[4]){
            if($this->memberstatus[4] && $this->memberstatus[4]['status']!='1'){
                if($source=='app'){
                    $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'您的供应商申请还未通过，请耐心等待')));
                }else{
                    $this->error('您的供应商申请还未通过，请耐心等待',U('User/supplierregister'));
                }
            }
        }else{
            if($source=='app'){
                $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'您还没有成为供应商，请先申请成为供应商')));
            }else{
                $this->error('您还没有成为供应商，请先申请成为供应商',U('User/supplierregister'));
            }
        }
        $status=I('status');
        $curpage=I('p');
        $listRows = C('ORDER_LIST_ROWS') > 0 ? C('ORDER_LIST_ROWS') : 20;
        $orderapi=new OrderApi;
        $list=$orderapi->getSupplierOrder($uid,$status,$curpage,$listRows);
        if($source=='app'){
            unset($list['totalcount']);
            $sysapi=new SystemApi;
            $sysapi->writeAccessLog('供应商订单管理','app');
            $this->ajaxReturn($list);
        }else{
            $total      =   $list['totalcount'];
            unset($list['totalcount']);
            $page       =   new \Think\Page($total, $listRows);
            $voList     =   $list;
            $p          =   $page->show();
            $this->assign('_list', $voList);
            $this->assign('_page', $p? $p: '');
            // 记录当前列表页的cookie
            Cookie('__forward__',$_SERVER['REQUEST_URI']);
            $sysapi=new SystemApi;
            $sysapi->writeAccessLog('供应商订单管理');
            $this->assign('list',$list);
            $this->assign('item',$status);
            $this->assign('supuid',$uid);
            $this->assign('pagename','page-supplierorders');
            $this->display();
        }
    }

    //供应商商品规格管理
    // public function supplierproductspec(){
    //     if ( !is_login() ) {
    //         $this->error( '您还没有登陆',U('User/login') );
    //     }
    //     $uid = is_login();
    //     $prodapi=new ProductApi;
    //     $speclist=$prodapi->getAllProductSpec();
    //     $supapi=new SupplierApi;
    //     $supspec=$supapi->getsupplierprodspecid($uid);
    //     if(IS_POST){
    //         $specsel=I('prodspec');
    //         if($supapi->updatesupplierprodspec($uid,$specsel)){
    //             $this->success('选择商品规格成功',U('supplierproductspec'));
    //         }else{
    //             $this->error('选择商品规格失败');
    //         }
    //     }else{
    //         $this->assign('speclist',$speclist);
    //         $this->assign('supspec',$supspec);
    //         $this->assign('pagename','page-supplierproductspec');
    //         $this->display();
    //     }
    // }

    //供应商商品管理
    public function supplierproducts(){
        if($this->memberstatus[4]){
            if($this->memberstatus[4] && $this->memberstatus[4]['status']!='1'){
                if($source=='app'){
                    $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'您的供应商申请还未通过，请耐心等待')));
                }else{
                    $this->error('您的供应商申请还未通过，请耐心等待',U('User/supplierregister'));
                }
            }
        }else{
            if($source=='app'){
                $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'您还没有成为供应商，请先申请成为供应商')));
            }else{
                $this->error('您还没有成为供应商，请先申请成为供应商',U('User/supplierregister'));
            }
        }
        $productname=I('prodnname');
        $sysapi=new SystemApi;
        $sysapi->writeAccessLog('供应商商品管理');
        $uid=is_login();
        $prodapi=new ProductApi;
        $list=$prodapi->getSupplierPorductList($uid,$productname);
        $total      =   $list? count($list) : 1 ;
        $listRows   =   C('SUPPROD_LIST_ROWS') > 0 ? C('SUPPROD_LIST_ROWS') : 20;
        $page       =   new \Think\Page($total, $listRows);
        $voList     =   array_slice($list, $page->firstRow, $page->listRows);
        $p          =   $page->show();
        $this->assign('_list', $voList);
        $this->assign('_page', $p? $p: '');
        // 记录当前列表页的cookie
        Cookie('__forward__',$_SERVER['REQUEST_URI']);
        $this->assign('list',$voList);
        $this->assign('pagename','page-supplierproducts');
        $this->display();
    }

    //供应商添加商品选择分类
    public function supplierproductclass(){
        if($this->memberstatus[4]){
            if($this->memberstatus[4] && $this->memberstatus[4]['status']!='1'){
                if($source=='app'){
                    $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'您的供应商申请还未通过，请耐心等待')));
                }else{
                    $this->error('您的供应商申请还未通过，请耐心等待',U('User/supplierregister'));
                }
            }
        }else{
            if($source=='app'){
                $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'您还没有成为供应商，请先申请成为供应商')));
            }else{
                $this->error('您还没有成为供应商，请先申请成为供应商',U('User/supplierregister'));
            }
        }
        $productid=I('pid');
        if(empty($productid)){
            $productid=0;
        }
        $cataapi=new CategoryApi;
        $list=$cataapi->getProductCatalogTree();
        $this->assign('catalog',$list);
        $this->assign('productid',$productid);
        $this->assign('pagename','page-supplierproductclass');
        $this->display();
    }

    //供应商添加商品
    public function supplierproductadd(){
        if($this->memberstatus[4]){
            if($this->memberstatus[4] && $this->memberstatus[4]['status']!='1'){
                if($source=='app'){
                    $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'您的供应商申请还未通过，请耐心等待')));
                }else{
                    $this->error('您的供应商申请还未通过，请耐心等待',U('User/supplierregister'));
                }
            }
        }else{
            if($source=='app'){
                $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'您还没有成为供应商，请先申请成为供应商')));
            }else{
                $this->error('您还没有成为供应商，请先申请成为供应商',U('User/supplierregister'));
            }
        }
        if ( !is_login() ) {
            $this->error( '您还没有登陆',U('User/login'),false,5);
        }
        $uid = is_login();
        $classid=I('cid');
        if(empty($classid) || $classid==0){
            $this->error( '请选择商品分类',U('supplierproductclass') );
        }
        $cataapi=new CategoryApi;
        $classify=$cataapi->getCatalogByID($classid);
        $prodapi=new ProductApi;
        $catespec=$cataapi->getSpecByCatalogID($classid);
        $sid=array();
        foreach ($catespec as $key => $value) {
            array_push($sid, $value['sid']);
        }
        $speclist=$prodapi->getProductSpecBySID($uid,$sid);

        if(IS_POST){
            $product['uid']=I('uid');
            $product['name']=I('name');
            $product['catalog_id']=I('cid');
            $simtmp=I('simcomment');
            $product['simcomment']=str_replace('\n','|',$simtmp);
            $product['detailpid']=I('detailimgid');
            $product['supprice']=I('supprice');
            $product['inventory']=I('inventory');
            $weight=I('weight');
            $volume=I('volume');
            $product['weight']=intval(($weight*100))/100;
            $product['volume']=intval(($volume*100))/100;
            $product['supprice']=floor($product['supprice']*100);

            $prodsinventory=I('sinventory');
            $prodsprice=I('sprice');
            $mainpic=I('mainpid');
            $prodspecgroup=I('specgroupprice');
            $prodspid=I('pidprice');

            if(count($prodsprice)>0){
                $product['supprice']==0;
                $product['inventory']==0;
                foreach ($prodsprice as $key => $value) {
                    $prodsprice[$key]=floor($value*100);
                }
            }
            $data['product']=$product;
            $data['sprice']=$prodsprice;
            $data['sinventory']=$prodsinventory;
            $data['mainpic']=$mainpic;
            $data['prodspecgroup']=$prodspecgroup;
            $data['prodspid']=$prodspid;

            $info=$prodapi->productInsert($data);
            if($info['status']){
                $this->success('发布商品成功！',U('supplierproducts'));
            }else{
                $this->error('发布商品失败，'.$info['info']);
            }
        }else{
            $this->assign('classify',$classify);
            $this->assign('speclist',$speclist);
            $this->assign('uid',$uid);
            $this->assign('pagename','page-supplierproductadd');
            $this->display();
        }
    }

    //供应商编辑商品
    public function supplierproductedit(){
        if($this->memberstatus[4]){
            if($this->memberstatus[4] && $this->memberstatus[4]['status']!='1'){
                if($source=='app'){
                    $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'您的供应商申请还未通过，请耐心等待')));
                }else{
                    $this->error('您的供应商申请还未通过，请耐心等待',U('User/supplierregister'));
                }
            }
        }else{
            if($source=='app'){
                $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'您还没有成为供应商，请先申请成为供应商')));
            }else{
                $this->error('您还没有成为供应商，请先申请成为供应商',U('User/supplierregister'));
            }
        }
        if ( !is_login() ) {
            $this->error( '您还没有登陆',U('User/login'),false,5);
        }
        $uid = is_login();
        $classid=I('cid');
        $productid=I('pid');
        if(empty($classid) || $classid==0){
            $this->error( '请选择商品分类',U('supplierproductclass') );
        }
        $cataapi=new CategoryApi;
        $classify=$cataapi->getCatalogByID($classid);
        $catespec=$cataapi->getSpecByCatalogID($classid);
        $prodapi=new ProductApi;
        $info=$prodapi->getProductInfoEdit($productid);
        $emptyimgcnt=10-count($info['images'])+1;
        $sid=array();
        foreach ($catespec as $key => $value) {
            array_push($sid, $value['sid']);
        }
        $speclist=$prodapi->getProductSpecBySID($uid,$sid);

        if(IS_POST){
            $product['product_id']=I('productid');
            $product['name']=I('name');
            $product['catalog_id']=I('cid');
            $simtmp=I('simcomment');
            $product['simcomment']=str_replace('\n','|',$simtmp);
            $product['detailpid']=I('detailimgid');
            $product['supprice']=I('supprice');
            $product['inventory']=I('inventory');
            $weight=I('weight');
            $volume=I('volume');
            $product['weight']=intval(($weight*100))/100;
            $product['volume']=intval(($volume*100))/100;
            $product['supprice']=floor($product['supprice']*100);

            $prodsinventory=I('sinventory');
            $prodsprice=I('sprice');
            $mainpic=I('mainpid');
            $prodspecgroup=I('specgroupprice');
            $prodspid=I('pidprice');

            if(count($prodsprice)>0){
                $product['supprice']==0;
                $product['inventory']==0;
                foreach ($prodsprice as $key => $value) {
                    $prodsprice[$key]=floor($value*100);
                }
            }
            $data['product']=$product;
            $data['sprice']=$prodsprice;
            $data['sinventory']=$prodsinventory;
            $data['mainpic']=$mainpic;
            $data['prodspecgroup']=$prodspecgroup;
            $data['prodspid']=$prodspid;

            $info=$prodapi->productEdit($data);
            if($info['status']){
                $this->success('修改商品成功！',U('supplierproducts'));
            }else{
                $this->error('修改商品失败，'.$info['info'],U('supplierproducts'));
            }
        }else{
            $this->assign('classify',$classify);
            $this->assign('speclist',$speclist);
            $this->assign('info',$info);
            $this->assign('prodspecs',$info['specs']);
            $this->assign('prodspecslist',$info['specslist']);
            $this->assign('emptyimgcnt',$emptyimgcnt);
            $this->assign('uid',$uid);
            $this->assign('productid',$productid);
            $this->assign('pagename','page-supplierproductedit');
            $this->display();
        }
    }

    //供应商认证
    public function supplierauth(){
        if($this->memberstatus[4]){
            if($this->memberstatus[4] && $this->memberstatus[4]['status']!='1'){
                if($source=='app'){
                    $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'您的供应商申请还未通过，请耐心等待')));
                }else{
                    $this->error('您的供应商申请还未通过，请耐心等待',U('User/supplierregister'));
                }
            }
        }else{
            if($source=='app'){
                $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'您还没有成为供应商，请先申请成为供应商')));
            }else{
                $this->error('您还没有成为供应商，请先申请成为供应商',U('User/supplierregister'));
            }
        }
        $this->assign('pagename','page-supplierauth');
        $this->display();
    }

    //供应商评价管理
    public function suppliercomments($source='web',$token=0){
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
        if($this->memberstatus[4]){
            if($this->memberstatus[4] && $this->memberstatus[4]['status']!='1'){
                if($source=='app'){
                    $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'您的供应商申请还未通过，请耐心等待')));
                }else{
                    $this->error('您的供应商申请还未通过，请耐心等待',U('User/supplierregister'));
                }
            }
        }else{
            if($source=='app'){
                $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'您还没有成为供应商，请先申请成为供应商')));
            }else{
                $this->error('您还没有成为供应商，请先申请成为供应商',U('User/supplierregister'));
            }
        }
        $sysapi=new SystemApi;
        $sysapi->writeAccessLog('供应商评价管理');
        $userapi=new UserApi;
        $list=$userapi->getSupplierComments($uid);
        $this->assign('list',$list);
        $this->assign('pagename','page-suppliercomments');
        $this->display();
    }

    //商品图片上传
    public function picture_upload(){
        $data=I('data');
        $prodapi=new ProductApi;
        $info=$prodapi->picture_upload($data);
        $this->ajaxReturn($info);
    }

    //注册图片上传
    public function reg_picture_upload($source='web',$token=0){
        $datatmp=I('data');
        if($source=='app'){
            $data=json_decode($datatmp,true);
        }else{
            $data=$datatmp;
        }
        $userapi=new UserApi;
        $info=$userapi->picture_upload($data);
        if($source=='app'){
            if($info['status']){
                $this->ajaxReturn(array('success'=>true,'body'=>$info));
            }else{
                $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>$info['info'])));
            }
            
        }else{
            $this->ajaxReturn($info);
        }
    }

    //店铺图片上传
    public function store_picture_upload($source='web',$token=0){
        $datatmp=I('data');
        if($source=='app'){
            $data=json_decode($datatmp,true);
        }else{
            $data=$datatmp;
        }
        $shopapi=new ShopApi;
        $info=$shopapi->picture_upload($data);
        if($source=='app'){
            if($info['status']){
                if(strtoupper($data['imgtype'])=='SHOPLOGO'){
                    $shopdata['logo']=$info['id'];
                }
                if(strtoupper($data['imgtype'])=='SHOPBACKGP'){
                    $shopdata['backgroud']=$info['id'];
                }
                $res=$shopapi->newShop($data['uid'],$shopdata);
                $this->ajaxReturn(array('success'=>true,'body'=>$info));
            }else{
                $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>$info['info'])));
            }
        }else{
            $this->ajaxReturn($info);
        }
    }

    //获取用户图片库
    public function get_user_picture(){
        $uid=I('uid');
        $imgtype=I('imgtype');
        $page=empty(I('p')) ? 1 : I('p');
        $page=($page==0) ? 1 : $page;
        $pcid=empty(I('pcid')) ? 0 : I('pcid');
        $prodapi=new ProductApi;
        $list=$prodapi->get_user_product_pic($uid,$imgtype,$pcid);
        $rowlist=20;
        $index=0;
        $disinfo=array();
        foreach ($list as $key => $value) {
            if($index<($page*$rowlist) && $index>=(($page-1)*$rowlist)){
                $disinfo[$key]=$value;
            }
            $index=$index+1;
        }
        $info['page']=$page;
        $info['pagecnt']=ceil(count($list)/$rowlist);
        $info['list']=$disinfo;
        $this->ajaxReturn($info);
    }

    //获取用户图片库目录
    public function get_user_picture_class(){
        $uid=I('uid');
        $type=I('type');
        $page=empty(I('p')) ? 1 : I('p');
        $classname=I('cln');
        $prodapi=new ProductApi;
        $list=$prodapi->get_user_product_pic_class($uid,$type,$classname);
        $rowlist=20;
        $index=0;
        $disinfo=array();
        foreach ($list as $key => $value) {
            if($index<($page*$rowlist) && $index>=(($page-1)*$rowlist)){
                $disinfo[$key]=$value;
            }
            $index=$index+1;
        }
        $info['page']=$page;
        $info['pagecnt']=ceil(count($list)/$rowlist);
        $info['list']=$disinfo;
        $this->ajaxReturn($info);
    }

    //获取用户店铺图片库
    public function store_get_user_picture(){
        $uid=I('uid');
        $imgtype=I('imgtype');
        $shopapi=new ShopApi;
        $info=$shopapi->get_user_product_pic($uid,$imgtype);
        $this->ajaxReturn($info);
    }

    //创建目录
    public function create_member_picclass(){
        $data=I('data');
        $userapi=new UserApi;
        $info=$userapi->create_member_picclass($data);
        $this->ajaxReturn($info);
    }

    //自定义规格值
    public function cust_specval(){
        $data=I('data');
        if(empty($data['sid'])){
            $return  = array('status' => 0, 'vid' => '', 'info' => '您要添加的规格不存在，不能添加规格值');
            $this->ajaxReturn($return);
        }else{
            $prodapi=new ProductApi;
            $info=$prodapi->custSpecval($data);
            $this->ajaxReturn($info);
        }
    }

    public function commentadd($source='web',$token=0){
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
        if($source=='app'){
            $data=json_decode(I('data'),true);
        }else{
            $data=array();
            $tmpcomm['orderdetailid']=I('orderdetailid');
            $tmpcomm['comment']=I('commenttext');
            $tmpcomm['score']=I('score');
            array_push($data, $tmpcomm);
        }
        foreach ($data as $key => $value) {
            if($value['orderdetailid']==0 || $value['orderdetailid']==""){
                $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'参数错误')));
            }
            if($source=='app'){
                $data[$key]['comment']=$value['commenttext'];
                unset($data[$key]['commenttext']);
            }
        }

        $userapi=new UserApi;
        if($userapi->commentadd($data,$uid)){
            $this->ajaxReturn(array('success'=>true,'body'=>array('isLogined'=>true,'error'=>'评价成功')));
        }else{
            $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'评价失败')));
        }
    }

    //查找所有订单 app用
    public function userorders($source='app',$token=0){
        $uid=0;
        $orderclass=I('orderclass');
        if($source=='app'){
            $auth=session('user_auth');
            if($auth['token']!=$token){
                $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'身份验证错误')));
            }
            $uid=$auth['uid'];
        }
        if(!$uid){
            if($source=='app'){
                $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>false,'error'=>'您还没有登陆')));
            }
        }
        $status=I('status');
        $orderapi=new OrderApi;
        switch ($orderclass) {
            case '1':
                $list=$orderapi->getMyOrder($uid,$status);
                $list=array_values($list);
                break;
            case '2':
                $list=$orderapi->getStoreOrder($uid,$status);
                $list=array_values($list);
                break;
            case '3':
                $list=$orderapi->getSupplierOrder($uid,$status);
                $list=array_values($list);
                break;
        }        
        if($source=='app'){
            $sysapi=new SystemApi;
            $sysapi->writeAccessLog('用户所有订单','app');
            $this->ajaxReturn(array('success'=>true,'body'=>$list));
        }
    }

    public function basememberinfo($source='app',$token=0){
        $uid=0;
        if($source=='app'){
            $auth=session('user_auth');
            if($auth['token']!=$token){
                $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'身份验证错误')));
            }
            $uid=$auth['uid'];
        }
        if(!$uid){
            if($source=='app'){
                $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>false,'error'=>'您还没有登陆')));
            }
        }
        if($source=='app'){
            $userapp=array();
            $auth=session('user_auth');
            $userapp['id']=$auth['uid'];
            $userapp['headImage']=$auth['portrait_path'];
            $userapp['nikeName']=$auth['nickname'];
            $userapp['level']=$auth['level'];
            $userapp['token']=$auth['token'];
            $orderapi=new OrderApi;
            $userapp['orders']=$orderapi->getMyOrderSum($uid);
            if($this->memberstatus[1] || $this->memberstatus[2]){
                if($this->memberstatus[1] && $this->memberstatus[1]['status']=='1'){
                    $userapp['isHack']=true;
                }else{
                    if($this->memberstatus[2] && $this->memberstatus[2]['status']=='1'){
                        $userapp['isHack']=true;
                    }else{
                        $userapp['isHack']=false;
                    }
                }
            }else{
                $userapp['isHack']=false;
            }
            if($this->memberstatus[4]){
                if($this->memberstatus[4] && $this->memberstatus[4]['status']=='1'){
                    $userapp['isVendor']=true;
                }else{
                    $userapp['isVendor']=false;
                }
            }else{
                $userapp['isVendor']=false;
            }
            $this->ajaxReturn(array('success'=>true,'body'=>array($userapp)));
        }
    }

    public function sumorders($source='app',$token=0){
        $uid=0;
        $orderclass=I('orderclass');
        if($source=='app'){
            $auth=session('user_auth');
            if($auth['token']!=$token){
                $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'身份验证错误')));
            }
            $uid=$auth['uid'];
        }
        if(!$uid){
            if($source=='app'){
                $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>false,'error'=>'您还没有登陆')));
            }
        }
        if($source=='app'){
            $orderapi=new OrderApi;
            switch ($orderclass) {
                case '1':
                    $orders=$orderapi->getMyOrderSum($uid);
                    break;
                case '2':
                    $orders=$orderapi->getMakerOrderSum($uid);
                    break;
                // case '3':
                //     $orders=$orderapi->getMyOrderSum($uid);
                //     break;
            }
            $this->ajaxReturn(array('success'=>true,'body'=>$orders));
        }
    }

    //app获取消息
    public function getMessages($source='app',$token=0){
        $uid=0;
        if($source=='app'){
            $auth=session('user_auth');
            if($auth['token']!=$token){
                $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'身份验证错误')));
            }
            $uid=$auth['uid'];
        }
        if(!$uid){
            if($source=='app'){
                $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>false,'error'=>'您还没有登陆')));
            }
        }
        $msgapi=new MessageApi;
        $res=$msgapi->getAppMessageList($uid,0);
        if($source=='app'){
            $sysapi=new SystemApi;
            $sysapi->writeAccessLog('用户所有消息','app');
            $this->ajaxReturn(array('success'=>true,'body'=>$res));
        }
    }

    //app请求慧爱币和优惠券总数量
    public function getMakerHcoinCoupon($source='app',$token=0){
        $uid=0;
        if($source=='app'){
            $auth=session('user_auth');
            if($auth['token']!=$token){
                $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'身份验证错误')));
            }
            $uid=$auth['uid'];
        }
        if(!$uid){
            if($source=='app'){
                $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>false,'error'=>'您还没有登陆')));
            }
        }
        $userapi=new UserApi;
        $res=$userapi->getMakerHcoinCoupon($uid);
        if($source=='app'){
            $sysapi=new SystemApi;
            $sysapi->writeAccessLog('慧爱币和优惠券','app');
            $this->ajaxReturn(array('success'=>true,'body'=>$res));
        }
    }

    //app请求慧爱币收支明细
    public function getMakerHcoinDetail($source='app',$token=0){
        $uid=0;
        if($source=='app'){
            $auth=session('user_auth');
            if($auth['token']!=$token){
                $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'身份验证错误')));
            }
            $uid=$auth['uid'];
        }
        if(!$uid){
            if($source=='app'){
                $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>false,'error'=>'您还没有登陆')));
            }
        }
        $userapi=new UserApi;
        $res=$userapi->getHCoinRecord($uid);
        foreach ($res as $key => &$value) {
            $value['created']=date('Y-m-d H:i',$value['created']);
        }
        if($source=='app'){
            $this->ajaxReturn(array('success'=>true,'body'=>$res));
        }
    }

    //app获取收货地址列表
    public function getShippingAddress($source='web',$token=0){
        $uid=0;
        if($source=='app'){
            $auth=session('user_auth');
            if($auth['token']!=$token){
                $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'身份验证错误')));
            }
            $uid=$auth['uid'];
        }
        if(!$uid){
            if($source=='app'){
                $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>false,'error'=>'您还没有登陆')));
            }
        }
        $userapi=new UserApi;
        $list=$userapi->getshippingaddress($uid);
        foreach ($list as $key => &$value) {
            $value['created']=date('Y-m-d H:i',$value['created']);
            if($value['isdefault']=='0'){
                $value['isdefault']=false;
            }else{
                $value['isdefault']=true;
            }
            
        }
        if($source=='app'){
            $sysapi=new SystemApi;
            $sysapi->writeAccessLog('收货地址','app');
            $this->ajaxReturn(array('success'=>true,'body'=>$list));
        }
    }

    public function picture_thumb(){
        $uid = is_login();
        if($uid==1){
            $curdir=I('dirname');
            $filtertype=I('filtertype');
            if(!empty($curdir)){
                $filelist=hostdirscan($curdir);
            }else{
                $curdir='';
                $filelist=array();
            }
            $filelist1=array();
            $filelist2=array();
            $filelist3=array();
            foreach ($filelist as $key => $value) {
                $filelist1[$value['name']]=array('name'=>$value['name'],'type'=>$value['type'],'size'=>$value['size']);
                if(is_array($value['child'])){
                    $filelist2[$value['name']]=array();
                    foreach ($value['child'] as $key1 => $value1) {
                        array_push($filelist2[$value['name']], array('name'=>$value1['name'],'type'=>$value1['type'],'keyw'=>$value['name'].$value1['name'],'size'=>$value1['size']));
                        if(is_array($value1['child'])){
                            $filelist3[$value['name'].$value1['name']]=array();
                            foreach ($value1['child'] as $key2 => $value2) {
                                array_push($filelist3[$value['name'].$value1['name']], array('name'=>$value2['name'],'type'=>$value2['type'],'size'=>$value2['size']));
                            }
                        }
                    }
                }
            }

            $imgserver=C('IMAGE_SERVER');
            $driver = C('PICTURE_UPLOAD_DRIVER');
            $config=C("UPLOAD_{$driver}_CONFIG");
            $picconfigname=$imgserver[$config['host']].'-'.strtoupper('HOME');
            $sizeconfig=C($picconfigname);
            if(substr($curdir, -1)=='/'){
                $curdir=substr($curdir, 0, strlen($curdir)-1);
            }
            $type=explode('/', $curdir);
            $cnt=count($type);
            $typesize=$sizeconfig[strtoupper($type[$cnt-1])];
            $size=explode('|', $typesize);
            if(count($size)<=1){
                $dealcnt=0;
            }else{
                $dealcnt=0;
                for($i=1;$i<count($size);$i++){
                    foreach ($filelist as $key => $value) {
                        foreach ($value['child'] as $key1 => $value1) {
                            if($value1['name']==$size[0]){
                                foreach ($value1['child'] as $key2 => $value2) {
                                    $filenamenew=$curdir.'/'.$value['name'].'/'.$size[$i].'/'.$value2['name'];
                                    if(!file_exists($filenamenew)){
                                        $dealcnt=$dealcnt+1;
                                    }
                                    switch ($filtertype) {
                                        case '1':
                                            if(file_exists($filenamenew)){
                                                unset($filelist1[$value['name']]);
                                                foreach ($filelist2[$value['name']] as $key3 => $value3) {
                                                    unset($filelist3[$value['name'].$value3['name']]);
                                                }
                                                unset($filelist2[$value['name']]);
                                            }
                                            break;
                                        case '2':
                                            if($value2['size']<200 || $value2['size']>=500){
                                                foreach ($filelist2[$value['name']] as $key3 => $value3) {
                                                    unset($filelist3[$value['name'].$value3['name']]);
                                                }
                                            }
                                            break;
                                        case '3':
                                            if($value2['size']<500 || $value2['size']>=1024){
                                                foreach ($filelist2[$value['name']] as $key3 => $value3) {
                                                    unset($filelist3[$value['name'].$value3['name']]);
                                                }
                                            }
                                            break;
                                        case '4':
                                            if($value2['size']<1024){
                                                foreach ($filelist2[$value['name']] as $key3 => $value3) {
                                                    unset($filelist3[$value['name'].$value3['name']]);
                                                }
                                            }
                                            break;
                                    }
                                }
                            }
                        }
                    }
                }
            }
            foreach ($filelist1 as $key => $value) {
                foreach ($filelist2[$value['name']] as $key1 => $value1) {
                    $tmpkey=$value['name'].$value1['name'];
                    if(!array_key_exists($tmpkey, $filelist3)){
                        unset($filelist2[$value['name']]);
                        unset($filelist1[$value['name']]);
                    }
                }
            }
            foreach ($filelist3 as $key => $value) {
                foreach ($value as $key1 => $value1) {
                    if(!empty($filelist3[$key][$key1]['size'])){
                        $filelist3[$key][$key1]['size']=$filelist3[$key][$key1]['size'].'KB';
                    }
                }
            }

            unset($filelist);
            $this->assign('typesize',$typesize);
            $this->assign('curdir',$curdir);
            $this->assign('dealcnt',$dealcnt);
            $this->assign('filtertype',$filtertype);
            $this->assign('filelist1',$filelist1);
            $this->assign('filelist2',$filelist2);
            $this->assign('filelist3',$filelist3);
            $this->display();
        }else{
            $this->error('对不起，你无权访问该网页');
        }
    }

    public function picture_thumb_refresh(){
        if(IS_POST){
            $curdir=I('dirnamerefresh');
            $typesize=I('typesize');
            $dealunit=I('dealunit');
            if(!is_numeric($dealunit)){
                $this->error('目录和目录配置不存在');
            }elseif($dealunit>50){
                $this->error('每次最大处理数为50');
            }
            if(substr($curdir, -1)=='/'){
                $curdir=substr($curdir, 0, strlen($curdir)-1);
            }
            if(empty($curdir) || empty($typesize)){
                $this->error('目录和目录配置不存在');
            }else{
                $filelist=hostdirscan($curdir);
                if(empty($filelist)){
                    $this->error('目录为空');
                }
                $imgserver=C('IMAGE_SERVER');
                $driver = C('PICTURE_UPLOAD_DRIVER');
                $config=C("UPLOAD_{$driver}_CONFIG");
                $picconfigname=$imgserver[$config['host']].'-'.strtoupper('HOME');
                $sizeconfig=C($picconfigname);
                $type=explode('/', $curdir);
                $cnt=count($type);
                $typesize=$sizeconfig[strtoupper($type[$cnt-1])];
                if(empty($typesize)){
                    $this->error('目录配置为空');
                }
                $size=explode('|', $typesize);
                if(count($size)<=1){
                    $this->error('该目录图片只存在一种尺寸，不能刷新');
                }
                $filesizenew=array();
                $filesizenewdir=array();
                for($i=1;$i<count($size);$i++){
                    $filesizenew[$size[$i]]=array();
                    $filesizenewdir[$size[$i]]=array();
                    foreach ($filelist as $key => $value) {
                        foreach ($value['child'] as $key1 => $value1) {
                            if($value1['name']==$size[0]){
                                foreach ($value1['child'] as $key2 => $value2) {
                                    $filenamenew=$curdir.'/'.$value['name'].'/'.$size[$i].'/'.$value2['name'];
                                    $filedirnew=$curdir.'/'.$value['name'].'/'.$size[$i];
                                    if(!in_array($filenamenew, $filesizenew[$size[$i]])){
                                        if(!file_exists($filenamenew)){
                                            array_push($filesizenew[$size[$i]], $filenamenew);
                                        }
                                    }
                                    if(!in_array($filedirnew, $filesizenewdir[$size[$i]])){
                                        if(!file_exists($filedirnew)){
                                            array_push($filesizenewdir[$size[$i]], $filedirnew);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                unset($filelist);
                for($i=1;$i<count($size);$i++){
                    foreach ($filesizenewdir[$size[$i]] as $key => $value) {
                        if(!file_exists($value)){
                            mkdir($value);
                        }
                    }
                }
                $org=$size[0];
                $totalcnt=0;
                for($i=1;$i<count($size);$i++){
                    foreach ($filesizenew[$size[$i]] as $key => $value) {
                        if($totalcnt>=$dealunit){
                            break;
                        }else{
                            $orgfile=str_replace($size[$i], $org, $value);
                            if(file_exists($orgfile)){
                                $sizewh=explode('x', $size[$i]);
                                if(count($sizewh)==2){
                                    $image = new \Think\Image();
                                    $image->open($orgfile);
                                    $width = $image->width();
                                    $height = $image->height();
                                    $rate = $width/$sizewh[0];
                                    $height = ceil($height/$rate);
                                    $image->thumb($sizewh[0], $height)->save($value);
                                    $totalcnt=$totalcnt+1;
                                    unset($image);
                                }
                            }
                        }
                    }
                    if($totalcnt>=$dealunit){
                        break;
                    }
                }

                $this->success('刷新成功，已处理图片数：'.$totalcnt);
            }
        }
    }

    public function userReadMessage(){
        $msgid=I('id');
        $msgapi=new MessageApi;
        $msg=$msgapi->readGetMessageByID($msgid);
        $this->ajaxReturn($msg);
    }

    public function userOrderDel($source='web',$token=0){
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
        $orderid=I('orderid');
        $orderapi=new OrderApi;
        if($orderapi->UserOrderDel($orderid,$uid)){
            $this->ajaxReturn(array('success'=>true,'body'=>array('isLogined'=>true,'error'=>'删除成功')));
        }else{
            $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'删除失败')));
        }
    }

    public function userOrderCancel($source='web',$token=0){
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
        $orderid=I('orderid');
        $orderapi=new OrderApi;
        if($orderapi->UserOrderCancel($orderid,$uid)){
            $this->ajaxReturn(array('success'=>true,'body'=>array('isLogined'=>true,'error'=>'撤消成功')));
        }else{
            $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'撤消失败')));
        }
    }

    public function userOrderAfter($source='web',$token=0){
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
        $orderid=I('orderid');
        $after=I('after');
        if($after=='1'){
            $aftercode='10';
            $msg='申请退款成功，请等待，我们会尽快处理';
            $msgerror='申请退款失败';
        }elseif($after=='2'){
            $aftercode='20';
            $msg='申请退货成功，请等待，我们会尽快处理';
            $msgerror='申请退货失败';
        }else{
            $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'参数错误')));
        }
        $orderapi=new OrderApi;
        if($orderapi->UserOrderAfterUpdate($orderid,$uid,$aftercode)){
            $this->ajaxReturn(array('success'=>true,'body'=>array('isLogined'=>true,'error'=>$msg)));
        }else{
            $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>$msgerror)));
        }
    }

    public function userOrderConfirm($source='web',$token=0){
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
        $orderid=I('orderid');
        $orderapi=new OrderApi;
        if($orderapi->UserOrderConfirm($orderid,$uid)){
            $this->ajaxReturn(array('success'=>true,'body'=>array('isLogined'=>true,'error'=>'交易完成')));
        }else{
            $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'确认失败')));
        }
    }

    public function getZTOshipinfo(){
        $orderid=I('orderid');
        $shiporders=D('order_shipping')->where(array('id'=>$orderid))->field('shiptplid,shiporderid')->select();
        $result['orderships']=array();
        foreach ($shiporders as $keyshod => $valueshod) {
            $code=D('shippingtpl')->where(array('id'=>$valueshod['shiptplid']))->getField('shipcorpcode');
            if(empty($valueshod['shiporderid'])){
                $shiporderid='';
            }else{
                if(stripos($valueshod['shiporderid'], ',')){
                    $shiporderid=explode(',', $valueshod['shiporderid']);
                }else{
                    $shiporderid=array();
                    array_push($shiporderid, $valueshod['shiporderid']);
                }
            }

            $tmpship['id']=$shiporderid;
            $tmpship['name']=C('SHIPPINGTYPE')[$code];
            $tmpship['code']=$code;
            array_push($result['orderships'], $tmpship);
        }

        $shipaddr=D('order_shipaddress')->where(array('orderid'=>$orderid,'status'=>1))->find();
        foreach ($result['orderships'] as $key => $value) {
            if($value['code']=='zto' && !empty($value['id'])){
                $data=json_encode($value['id']);
                $sign=$data.'872D39C30CA1462E6D3FAF490FC0CF2B';
                $md5check = md5($sign,true);
                $strbase64=base64_encode($md5check);
                $compid='50781bbbf83746d3b4b798c3d2c1b050';

                $fielddata=array(
                    'data'=>$data,
                    'data_digest'=>$strbase64,
                    'msg_type'=>'NEW_TRACES',
                    'company_id'=>$compid,
                    );
                $postdata=http_build_query($fielddata);
                $url='http://japi.zto.cn/zto/api_utf8/traceInterface';
                $ch = curl_init();
                $timeout = 5;
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
                $reszto = curl_exec($ch);
                curl_close($ch);

                $zto=json_decode($reszto,true);
                $result['orderships'][$key]['data']=$zto;
            }else{
                $otherdata['data']=array();
                $traces['billCode']='无';
                $traces['traces']=array();
                array_push($traces['traces'], array('desc'=>'暂无物流信息','scanDate'=>''));
                array_push($otherdata['data'], $traces);
                $result['orderships'][$key]['data']=$otherdata;
            }
        }
        $result['address']=$shipaddr;
        $this->ajaxReturn($result);
    }

    public function getOrderShipping($orderid,$uid){
        $shiporders=D('order_shipping')->where(array('id'=>$orderid,'uid'=>$uid))->field('shiptplid,shiporderid')->select();
        $result['orderships']=array();
        foreach ($shiporders as $keyshod => $valueshod) {
            $shiptpl=D('shippingtpl')->where(array('id'=>$valueshod['shiptplid']))->field('name,shipcorpcode')->find();
            $corpname=C('SHIPPINGTYPE')[$shiptpl['shipcorpcode']];
            if(!array_key_exists($valueshod['shiptplid'], $result['ordershiptpl'])){
                $result['ordershiptpl'][$valueshod['shiptplid']]=array('corp'=>$corpname,'name'=>$shiptpl['name']);
            }
            if(!empty($valueshod['shiporderid'])){
                if(stripos($valueshod['shiporderid'], ',')){
                    $shiporderid=explode(',', $valueshod['shiporderid']);
                    foreach ($shiporderid as $key => $value) {
                        $tmpship['corp']=$corpname;
                        $tmpship['name']=$shiptpl['name'];
                        $tmpship['num']=$value;
                        array_push($result['orderships'], $tmpship);
                    }
                }else{
                    $tmpship['corp']=$corpname;
                    $tmpship['name']=$shiptpl['name'];
                    $tmpship['num']=$valueshod['shiporderid'];
                    array_push($result['orderships'], $tmpship);
                }
            }
        }

        $shipaddr=D('order_shipaddress')->where(array('orderid'=>$orderid,'status'=>1))->find();
        $result['address']=$shipaddr;
        $orderdate=D('supplier_order')->where(array('id'=>$orderid,'uid'=>$uid))->field('created,shipstatus')->find();
        $result['orderdate']=date('Y-m-d',$orderdate['created']);
        $result['shipstatus']=$orderdate['shipstatus'];
        $this->ajaxReturn($result);
    }

    public function addOrderShipNum($orderid,$uid,$tplid,$num){
        $cnt=D('order_shipping')->where(array('id'=>$orderid,'uid'=>$uid,'shiporderid'=>array('like','%'.$num.'%')))->count();
        if($cnt>0){
            $result=array('status'=>false,'info'=>'该订单中已经含有运单号：'.$num);
            $this->ajaxReturn($result);
        }else{
            $shiporder=D('order_shipping')->where(array('id'=>$orderid,'uid'=>$uid,'shiptplid'=>$tplid))->find();
            if($shiporder){
                if(empty($shiporder['shiporderid'])){
                    D('order_shipping')->where(array('id'=>$orderid,'uid'=>$uid,'shiptplid'=>$tplid))->setField('shiporderid',$num);
                }else{
                    $data['shiporderid']=$shiporder['shiporderid'].','.$num;
                    D('order_shipping')->where(array('id'=>$orderid,'uid'=>$uid,'shiptplid'=>$tplid))->save($data);
                }
            }

            $shiporders=D('order_shipping')->where(array('id'=>$orderid,'uid'=>$uid))->field('shiptplid,shiporderid')->select();
            $result=array();
            foreach ($shiporders as $keyshod => $valueshod) {
                $shiptpl=D('shippingtpl')->where(array('id'=>$valueshod['shiptplid']))->field('name,shipcorpcode')->find();
                $corpname=C('SHIPPINGTYPE')[$shiptpl['shipcorpcode']];
                if(!empty($valueshod['shiporderid'])){
                    if(stripos($valueshod['shiporderid'], ',')){
                        $shiporderid=explode(',', $valueshod['shiporderid']);
                        foreach ($shiporderid as $key => $value) {
                            $tmpship['corp']=$corpname;
                            $tmpship['name']=$shiptpl['name'];
                            $tmpship['num']=$value;
                            array_push($result, $tmpship);
                        }
                    }else{
                        $tmpship['corp']=$corpname;
                        $tmpship['name']=$shiptpl['name'];
                        $tmpship['num']=$valueshod['shiporderid'];
                        array_push($result, $tmpship);
                    }
                }
            }
            $res=array('status'=>true,'info'=>$result);
            $this->ajaxReturn($res);
        }
    }

    public function outOrderShip($orderid,$uid){
        D('supplier_order')->where(array('id'=>$orderid,'uid'=>$uid))->setField('shipstatus',1);
        $cnt=D('supplier_order')->where(array('id'=>$orderid,'shipstatus'=>0))->count();
        if($cnt==0){
            D('order_status')->where(array('orderid'=>$orderid))->setField('status',2);
        }
        $res=array('status'=>true);
        $this->ajaxReturn($res);
    }
}
