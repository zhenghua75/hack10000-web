<?php
// +----------------------------------------------------------------------
// | Hack10000
// +----------------------------------------------------------------------
// | Copyright (c) 2015 http://www.kmdx.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: zhh <87023264@qq.com>
// +----------------------------------------------------------------------

namespace Service\Controller;
use Think\Controller;

/**
 * 前台公共控制器
 * 为防止多分组Controller名称冲突，公共Controller名称统一使用分组名称
 */
class ServiceController extends Controller {
	/* 空操作，用于输出404页面 */
	public function _empty(){
		$this->redirect('Index/index');
	}

    protected function _initialize(){
    	/* 读取站点配置 */
        $config = api('Config/lists');
        C($config); //添加配置
        if(!C('WEB_SITE_CLOSE')){
            $this->ajaxReturn('站点已经关闭，请稍后访问~');
        }
    }

	/* 用户登录检测 */
	protected function login(){
		/* 用户登录检测 */
		is_login() || $this->ajaxReturn(array('error'=>'您还没有登录，请先登录！','code'=>-1));
	}

}
