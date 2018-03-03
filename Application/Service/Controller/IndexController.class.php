<?php
// +----------------------------------------------------------------------
// | Hack10000
// +----------------------------------------------------------------------
// | Copyright (c) 2015 http://www.kmdx.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: zhh <87023264@qq.com>
// +----------------------------------------------------------------------

namespace Service\Controller;

class IndexController extends ServiceController {
	public function index(){
		$this->ajaxReturn('欢迎使用hack1000应用接口模块');
	}

}
