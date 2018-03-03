<?php

namespace Home\Controller;

/**
 * 前台商城控制器
 * 主要获取商城聚合数据
 */
class StoreConsController extends HomeController {

	//商城首页
    public function index(){

        // $category = D('Category')->getTree();
        // $lists    = D('Document')->lists(null);

        // $this->assign('category',$category);//栏目
        // $this->assign('lists',$lists);//列表
        // $this->assign('page',D('Document')->page);//分页

    	$info='测试信息';
        trace($info,'提示','DEBUG');
        $this->assign('pagename','page-storeconsindex');
        $this->display();
    }

}