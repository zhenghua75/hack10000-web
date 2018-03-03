<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Home\Controller;
use OT\DataDictionary;
use User\Api\UserApi;
use Common\Api\DocumentApi;
use Common\Api\SystemApi;

/**
 * 前台首页控制器
 * 主要获取首页聚合数据
 */
class IndexController extends HomeController {

	//系统首页
    public function index(){
        $category = D('Category')->getTree();
        $lists    = D('Document')->lists(null);

        $sysapi=new SystemApi;
        $sysapi->writeAccessLog('首页');
        $this->assign('category',$category);//栏目
        $this->assign('lists',$lists);//列表
        $this->assign('page',D('Document')->page);//分页
        $this->assign('pagename','pagehome');
        $this->display();
    }

    function loading(){
        $this->display();
    }

    //关于
    function about(){
        $this->display();
    }

    //许可协议
    function hackuseagreement(){
        $this->display();
    }

    //运营规范
    function hackmgrdoument(){
        $this->display();
    }

    //安全中心
    function safedoument(){
        $this->display();
    }

    //客服中心
    function servicedoument(){
        $this->display();
    }

    //社会实践
    public function practice(){
        if (is_login()) {
            $this->redirect('Store/index');
        }else{
            $sysapi=new SystemApi;
            $sysapi->writeAccessLog('社会实践');
            $this->meta_title = '社会实践';
            $this->display();
        }
    }

    //志愿服务
    public function volunteer(){
        $sysapi=new SystemApi;
        $sysapi->writeAccessLog('志愿服务');
        $this->meta_title = '志愿服务';
        $this->display();
    }

    //勤工助学
    public function workstudy(){
        $sysapi=new SystemApi;
        $sysapi->writeAccessLog('勤工助学');
        $this->meta_title = '勤工助学';
        $this->display();
    }

    //创新实验
    public function innovate($source='web',$token=0){
        $page=D('page')->where(array('url'=>'index/innovate'))->select();
        if($source=='app'){
            $slideshowlist=array();
            $pagelist=array();
            $docapi=new DocumentApi;
            foreach ($page as $key => $value) {
                if($value['itemtype']=='slideshow'){
                    $slideshow['image']=get_picture_path($value['itemid'],'slideshow','600x450');
                    $size=getimagesize($slideshow['image']);
                    $slideshow['imagesize']=array('width'=>$size[0],'height'=>$size[1]);
                    $slideshow['id']=$value['itemid'];
                    $slideshow['linktype']=$value['linkidtype'];
                    $slideshow['content']=intval($docapi->getDocCatelog($value['linkid']));
                    array_push($slideshowlist, $slideshow);
                }else{
                    $pagelist['id']=$value['itemid'];
                    $pagelist['image']=get_picture_path($value['itemid'],'page','w600');
                    $size=getimagesize($pagelist['image']);
                    $pagelist['imagesize']=array('width'=>$size[0],'height'=>$size[1]);
                    $pagelist['linktype']=$value['linkidtype'];
                    $pagelist['content']='';
                }
            }
            $sysapi=new SystemApi;
            $sysapi->writeAccessLog('创新实验','app');
            $this->ajaxReturn(array('success'=>ture,'body'=>array('page'=>$pagelist,'slideshow'=>$slideshowlist)));
        }else{
            if (is_login()) {
                $this->redirect('Store/innovate');
            }else{
                $sysapi=new SystemApi;
                $sysapi->writeAccessLog('创新实验');
                $this->meta_title = '创新实验';
                $this->display();
            }
        }
    }

    //自主创业
    public function selfemployed(){
        if (is_login()) {
            $this->redirect('Store/selfemployed');
        }else{
            $sysapi=new SystemApi;
            $sysapi->writeAccessLog('自主创业');
            $this->meta_title = '自主创业';
            $this->display();
        }
    }

    //论文专利
    public function  patents(){
        $sysapi=new SystemApi;
        $sysapi->writeAccessLog('论文专利');
        $this->meta_title = '论文专利';
        $this->display();
    }

    //创新创业档案
    public function  archives(){
        $sysapi=new SystemApi;
        $sysapi->writeAccessLog('创新创业档案');
        $this->meta_title = '创新创业档案';
        $this->display();
    }

    //注册协议
    public function regagreement(){
        $this->meta_title = '注册协议';
        $this->display();
    }
}