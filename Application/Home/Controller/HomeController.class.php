<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Home\Controller;
use Think\Controller;
use User\Api\UserApi;

/**
 * 前台公共控制器
 * 为防止多分组Controller名称冲突，公共Controller名称统一使用分组名称
 */
class HomeController extends Controller {

    public $memberstatus;

	/* 空操作，用于输出404页面 */
	public function _empty(){
		$this->redirect('Index/index');
	}

    protected function _initialize(){
        if(session('user_auth')){
            $userapi=new UserApi;
            $this->memberstatus=$userapi->registerprocess(session('user_auth.uid'));
            $this->assign('memberstatus',$this->memberstatus);
        }
        if(defined('UID')) return ;

        $uid=is_login();
        if($uid){
        	define('UID',is_login());
        }

        /* 读取站点配置 */
        $config = api('Config/lists');
        C($config); //添加配置

        if(!C('WEB_SITE_CLOSE')){
            if(strtolower(ACTION_NAME)!='login'){
                if(UID!=1){
                    $this->error('站点正在维护中，请稍后访问~', '#');
                }
            }
        }
    }

	/* 用户登录检测 */
	protected function login(){
		/* 用户登录检测 */
		is_login() || $this->error('您还没有登录，请先登录！', U('User/login'));
	}
}
