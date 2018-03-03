<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: yangweijie <yangweijiester@gmail.com> <code-tech.diandian.com>
// +----------------------------------------------------------------------
namespace Addons\SiteStat;
use Common\Controller\Addon;

/**
 * 系统环境信息插件
 * @author hack10000
 */
class SiteStatAddon extends Addon{

    public $info = array(
        'name'=>'SiteStat',
        'title'=>'站点统计信息',
        'description'=>'统计站点的基础信息',
        'status'=>1,
        'author'=>'hack10000',
        'version'=>'0.1'
    );

    public function install(){
        return true;
    }

    public function uninstall(){
        return true;
    }

    //实现的AdminIndex钩子方法
    public function AdminIndex($param){
        $config = $this->getConfig();
        $this->assign('addons_config', $config);
        if($config['display']){
            $begin=strtotime(date('Y-m-d'));
            $end=strtotime('+1 day');
            $info['user']		=	M('Member')->count();
            $info['maker']		=	M('member_role')->where(array('roleid'=>array('in','1,2')))->count('uid');
            $info['supplier']	=	M('member_role')->where(array('roleid'=>'4'))->count('uid');
            $info['todaypv']	=	M('accesslog')->where(array('accesstime'=>array(array('egt',$begin),array('lt',$end))))->count();
            $info['todayip']	=	M('accesslog')->where(array('accesstime'=>array(array('egt',$begin),array('lt',$end))))->count('distinct hostname');
            $info['todayuser']  =   M('accesslog')->where(array('accesstime'=>array(array('egt',$begin),array('lt',$end)),'uid'=>array('neq',0)))->count('distinct uid');
            $this->assign('info',$info);
            $this->display('info');
        }
    }
}