<?php

namespace Admin\Controller;

/**
 * 后台用户控制器
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
class PageController extends AdminController {

    /**
     * 用户管理首页
     * @author 麦当苗儿 <zuojiazi@vip.qq.com>
     */
    public function index(){
        $map['status']=1;

        $list   = $this->lists('page', $map);

        int_to_string($list);
        $this->assign('_list', $list);
        $this->meta_title = '页面配置信息';
        $this->display();
    }

    public function pageadd(){
        if(IS_POST){
            if($Category->update()){
                $this->success('新增成功！', U('catalog'));
            } else {
                $error = $Category->getError();
                $this->error(empty($error) ? '未知错误！' : $error);
            }
        } else {
            $pageitemtype = C('PAGEITEMTYPE');
            $this->assign('pageinfo',null);
            $this->assign('pageitemtype', $pageitemtype);
            $this->meta_title = '新增页面配置';
            $this->display('pageedit');
        }
    }

    public function pageedit($id = null, $parent_id = 0){
        if(IS_POST){ //提交表单
            if($Category->update()){
                $this->success('编辑成功！', U('catalog'));
            } else {
                $error = $Category->getError();
                $this->error(empty($error) ? '未知错误！' : $error);
            }
        } else {
            $cate = '';
            if($parent_id){
                /* 获取上级分类信息 */
                $cate = $Category->info($parent_id, 'id,name,sort,status');
                if(!($cate && 1 == $cate['status'])){
                    $this->error('指定的上级分类不存在或被禁用！');
                }
            }

            /* 获取分类信息 */
            $info = $id ? $Category->info($id) : '';
            $pageitemtype = C('PAGEITEMTYPE');
            $this->assign('info',       $info);
            $this->assign('pageitemtype',   $pageitemtype);
            $this->meta_title = '编辑分类';
            $this->display();
        }
    }

    public function pagetop(){
        $list=D('accesslog')->field('title,channel,count(*) as cnt')->group('title,channel')->order('cnt desc')->select();
        $total      =   $list? count($list) : 1 ;
        $listRows   =   C('LIST_ROWS') > 0 ? C('LIST_ROWS') : 10;
        $page       =   new \Think\Page($total, $listRows);
        $voList     =   array_slice($list, $page->firstRow, $page->listRows);
        $p          =   $page->show();
        $this->assign('_list', $voList);
        $this->assign('_page', $p? $p: '');
        // 记录当前列表页的cookie
        Cookie('__forward__',$_SERVER['REQUEST_URI']);
        $this->meta_title = '页面浏览排行';
        $this->display();
    }
}