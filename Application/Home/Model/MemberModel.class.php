<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Home\Model;
use Think\Model;
use User\Api\UserApi;

/**
 * 文档基础模型
 */
class MemberModel extends Model{

    /* 用户模型自动完成 */
    protected $_auto = array(
        array('login', 0, self::MODEL_INSERT),
        array('reg_ip', 'get_client_ip', self::MODEL_INSERT, 'function', 1),
        array('reg_time', NOW_TIME, self::MODEL_INSERT),
        array('last_login_ip', 0, self::MODEL_INSERT),
        array('last_login_time', 0, self::MODEL_INSERT),
        array('status', 1, self::MODEL_INSERT),
    );

    /**
     * 登录指定用户
     * @param  integer $uid 用户ID
     * @return boolean      ture-登录成功，false-登录失败
     */
    public function login($user,$source,$guid='0'){
        /* 检测是否在当前应用注册 */
        //$user = $this->field(true)->find($uid);
        if(!$user){ //未注册
            /* 在当前应用中注册用户 */
        	// $Api = new UserApi();
        	// $info = $Api->info($uid);
         //    $user = $this->create(array('nickname' => $info[1], 'status' => 1));
         //    $user['uid'] = $uid;
         //    if(!$this->add($user)){
         //        $this->error = '前台用户信息注册失败，请重试！';
         //        return false;
         //    }
            $this->error = '用户不存在！';
            return false;
        }elseif(1 != $user['status']) {
            $this->error = '用户未激活或已禁用！'; //应用级别禁用
            return false;
        }else{
            if($source=='app'){
                if($user['guid']=='' || $guid=='0'){
                    $guidnew = strtoupper(md5(uniqid(mt_rand(), true)));
                    $data = array(
                        'guid'=> $guidnew,
                    );
                    $this->where(array('uid'=>$user['uid']))->save($data);
                    $user['guid']=$guidnew;
                }else{
                    if($user['guid']!=$guid){
                        return false;
                    }
                }
            }

            /* 登录用户 */
            $this->autoLogin($user);

            //记录行为
            action_log('user_login', 'member', $user['uid'], $user['uid']);
        }

        return true;
    }

    public function applogin($guid){
        $user=$this->where(array('guid'=>$guid))->find();

        if($user){
            /* 登录用户 */
            $this->autoLogin($user);

            //记录行为
            action_log('user_login', 'member', $user['uid'], $user['uid']);

            return true;
        }else{
            return false;
        }
    }

    /**
     * 注销当前用户
     * @return void
     */
    public function logout(){
        session('user_auth', null);
        session('user_auth_sign', null);
    }

    /**
     * 自动登录用户
     * @param  integer $user 用户信息数组
     */
    private function autoLogin($user){
        /* 更新登录信息 */
        $data = array(
            'login'           => array('exp', '`login`+1'),
            'last_login_time' => NOW_TIME,
            'last_login_ip'   => get_client_ip(1),
        );
        $this->where(array('uid'=>$user['uid']))->save($data);

        $portpath=get_picture_path($user['portrait'],'portrait','150x150');
        if($portpath=='0' || $portpath==''){
            $portpath='http://img.hack10000.com/portrait/default.png';
        }

        /* 记录登录SESSION和COOKIES */
        $auth = array(
            'uid'             => $user['uid'],
            'username'        => $user['username'],
            'nickname'        => $user['nickname'],
            'last_login_time' => $user['last_login_time'],
            'portrait_path'   => $portpath,
            'token'   => $user['guid'],
            'level'   => $user['level'],
            'shopid'   => D('maker_store')->where(array('uid'=>$user['uid']))->getField('id'),
        );
        if($auth['nickname']==''){
            $auth['nickname']='匿名';
        }

        session('user_auth', $auth);
        session('user_auth_sign', data_auth_sign($auth));

    }

}
