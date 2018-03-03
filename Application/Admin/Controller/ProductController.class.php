<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Admin\Controller;

/**
 * 后台分类管理控制器
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
class ProductController extends AdminController {

    /**
     * 分类管理列表
     */
    public function catalog(){
        $id=empty(I('id')) ? 0 : I('id');
        $tree = $this->getTree($id,'id,name,sort,parent_id,status');
        $level1list=D('product_catalog')->where(array('parent_id'=>0))->field('id,name')->select();
        $this->assign('tree', $tree);
        $this->assign('level1list', $level1list);
        $this->assign('selid', $id);
        C('_SYS_GET_CATEGORY_TREE_', true); //标记系统获取分类树模板
        $this->meta_title = '分类管理';
        $this->display();
    }

    /**
     * 规格管理列表
     */
    public function specdef(){
        $tbspecdef=D('product_specdef');
        $tbspecval=D('product_specval');
        $specdef=$tbspecdef->where(array('status'=>'1'))->order('sort')->select();
        $list=$tbspecval->field('sid,name')->where(array('status'=>'1'))->order('sid,sort')->select();
        foreach ($specdef as $key => $value) {
            $vallist=array();
            foreach ($list as $key1 => $value1) {
                if($value['sid']==$value1['sid']){
                    array_push($vallist, $value1['name']);
                }
            }
            $specdef[$key]['val']=$vallist;
        }
        $this->assign('specdef', $specdef);
        $this->meta_title = '规格管理';
        $this->display();
    }

    /**
     * 分类对应规格管理列表
     */
    public function catalogspec(){
        $tbcatspec=D('product_catalogspec');
        $tbspecdef=D('product_specdef');
        $tbcate=D('product_catalog');
        $catspec=$tbcatspec->where(array('status'=>'1'))->order('cid,sort')->select();
        $list=$tbspecdef->field('sid,name,comment')->order('sid,sort')->select();
        $cate=$tbcate->getCatalogByLevel(3);
        foreach ($cate as $key => $value) {
            $vallist=array();
            foreach ($catspec as $key1 => $value1) {
                if($value['id']==$value1['cid']){
                    foreach ($list as $key2 => $value2) {
                        if($value1['sid']==$value2['sid']){
                            if(empty($value2['comment'])){
                                array_push($vallist, $value2['name']);
                            }else{
                                array_push($vallist, $value2['name'].'['.$value2['comment'].']');
                            }
                        }
                    }
                }
            }
            $cate[$key]['specname']=$vallist;
        }
        $this->assign('catespec', $cate);
        $this->meta_title = '分类对应规格管理';
        $this->display();
    }

    /**
     * 商品列表
     */
    public function productlist(){
        $sel1id=I('lv1');
        $sel2id=I('lv2');
        $sel3id=I('lv3');
        $name=I('name');
        $company=I('company');
        $prodmod = D('Product');
        $list=$prodmod->getProductList($sel1id,$sel2id,$sel3id,$name);
        $statusinfo=C('PRODUCTSTATUS');
        $productpart=C('PRODUCTPART');
        $level1list=D('product_catalog')->getCatalogByLevel(1);
        $level2list=D('product_catalog')->getCatalogByLevel(2);
        $level3list=D('product_catalog')->getCatalogByLevel(3);
        foreach ($list as $key => $value) {
            $resname=D('Member')->getSupplierCompany($value['uid'],$company);
            if($resname['exist']){
                $list[$key]['company']=$resname['name'];
                $cate=D('product_catalog')->getCatalogByID($value['catalog_id']);
                $list[$key]['cate']=$cate[0]['name'].'-'.$cate[1]['name'].'-'.$cate[2]['name'];
                $list[$key]['statusname']=$statusinfo[$value['status']];
                if($value['productpart']<1){
                    $list[$key]['prodpartname']='无';
                }else{
                    $list[$key]['prodpartname']=$productpart[$value['productpart']];
                }
            }else{
                unset($list[$key]);
            }
        }
        $total      =   $list? count($list) : 1 ;
        $listRows   =   C('LIST_ROWS') > 0 ? C('LIST_ROWS') : 10;
        $page       =   new \Think\Page($total, $listRows);
        $voList     =   array_slice($list, $page->firstRow, $page->listRows);
        $p          =   $page->show();
        $this->assign('_list', $voList);
        $this->assign('_page', $p? $p: '');
        // 记录当前列表页的cookie
        Cookie('__forward__',$_SERVER['REQUEST_URI']);
        $this->assign('level1list', $level1list);
        $this->assign('level2list', $level2list);
        $this->assign('level3list', $level3list);
        $this->assign('selid1', $sel1id);
        $this->assign('selid2', $sel2id);
        $this->assign('selid3', $sel3id);
        $this->meta_title = '商品列表';
        $this->display();
    }

    /**
     * 获取分类树，指定分类则返回指定分类极其子分类，不指定则返回所有分类树
     * @param  integer $id    分类ID
     * @param  boolean $field 查询字段
     * @return array          分类树
     * @author 麦当苗儿 <zuojiazi@vip.qq.com>
     */
    public function getTree($id = 0, $field = true){
        /* 获取当前分类信息 */
        $prodcatalog=M('product_catalog');

        /* 获取所有分类 */
        $map  = array('status' => array('gt', 0));
        $list = $prodcatalog->field($field)->where($map)->order('parent_id,sort')->select();
        
        if($id==0){
            $list = list_to_tree($list, $pk = 'id', $parent_id = 'parent_id', $child = '_', $root = $id);
        }else{
            $list = list_to_tree_haveroot($list, $pk = 'id', $parent_id = 'parent_id', $child = '_', $root = $id);
        }
        
        /* 获取返回数据 */
        if(isset($info)){ //指定分类则返回当前分类极其子分类
            $info['_'] = $list;
        } else { //否则返回所有分类
            $info = $list;
        }

        return $info;
    }

    /**
     * 显示分类树，仅支持内部调
     * @param  array $tree 分类树
     * @author 麦当苗儿 <zuojiazi@vip.qq.com>
     */
    public function catalogtree($tree = null){
        C('_SYS_GET_CATEGORY_TREE_') || $this->_empty();
        $this->assign('tree', $tree);
        $this->display('tree');
    }

    /* 编辑分类 */
    public function catalogedit($id = null, $parent_id = 0){
        $Category = D('product_catalog');

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

            $this->assign('info',       $info);
            $this->assign('category',   $cate);
            $this->meta_title = '编辑分类';
            $this->display();
        }
    }

    /* 新增分类 */
    public function catalogadd($parent_id = 0){
        $Category = D('product_catalog');

        if(IS_POST){ //提交表单
            if($Category->update()){
                $this->success('新增成功！', U('catalog'));
            } else {
                $error = $Category->getError();
                $this->error(empty($error) ? '未知错误！' : $error);
            }
        } else {
            $cate = array();
            if($parent_id){
                /* 获取上级分类信息 */
                $cate = $Category->info($parent_id, 'id,name,sort,status');
                if(!($cate && 1 == $cate['status'])){
                    $this->error('指定的上级分类不存在或被禁用！');
                }
            }

            /* 获取分类信息 */
            $this->assign('info',null);
            $this->assign('category', $cate);
            $this->meta_title = '新增分类';
            $this->display('catalogedit');
        }
    }

    public function setCatalogStatus(){
        $ids    =   I('request.ids');
        $status =   I('request.status');
        if(empty($ids)){
            $this->error('请选择要操作的数据');
        }
        $Category = D('product_catalog');
        $map['id'] = array('in',$ids);
        switch ($status){
            case -1 :
                $cate_id = $ids;
                if(empty($cate_id)){
                    $this->error('参数错误!');
                }

                //判断该分类下有没有子分类，有则不允许删除
                $child = $Category->where(array('parent_id'=>$cate_id,'status'=>array('neq','-1')))->field('id')->select();
                if(!empty($child)){
                    $this->error('请先删除该分类下的子分类');
                }

                //判断该分类下有没有内容
                $product_list = M('product')->where(array('catalog_id'=>$cate_id))->field('product_id')->select();
                if(!empty($product_list)){
                    $this->error('该分类下还有商品，不能删除');
                }
                $Category->where($map)->save(array('status'=>$status));
                $this->success('删除成功', U('catalog'));
                break;
            case 0  :
                $Category->where($map)->save(array('status'=>$status));
                $this->success('禁用成功', U('catalog'));
                break;
            case 1  :
                $Category->where($map)->save(array('status'=>$status));
                $this->success('启用成功', U('catalog'));
                break;
            default :
                $this->error('参数错误');
                break;
        }
    }


    /**
     * 操作分类初始化
     * @param string $type
     * @author huajie <banhuajie@163.com>
     */
    public function operate($type = 'move'){
        //检查操作参数
        if(strcmp($type, 'move') == 0){
            $operate = '移动';
        }elseif(strcmp($type, 'merge') == 0){
            $operate = '合并';
        }else{
            $this->error('参数错误！');
        }
        $from = intval(I('get.from'));
        empty($from) && $this->error('参数错误！');

        //获取分类
        $map = array('status'=>1, 'id'=>array('neq', $from));
        $list = M('product_catalog')->where($map)->field('id,parent_id,name')->select();


        //移动分类时增加移至根分类
        if(strcmp($type, 'move') == 0){
        	//不允许移动至其子孙分类
        	$list = tree_to_list(list_to_tree($list));

        	$parent_id = M('product_catalog')->getFieldById($from, 'parent_id');
        	$parent_id && array_unshift($list, array('id'=>0,'name'=>'根分类'));
        }

        $this->assign('type', $type);
        $this->assign('operate', $operate);
        $this->assign('from', $from);
        $this->assign('list', $list);
        $this->meta_title = $operate.'分类';
        $this->display();
    }

    /**
     * 移动分类
     * @author huajie <banhuajie@163.com>
     */
    public function move(){
        $to = I('post.to');
        $from = I('post.from');

        $res = M('product_catalog')->where(array('id'=>$from))->setField('parent_id',$to);
        if($res !== false){
            $this->success('分类移动成功！', U('catalog'));
        }else{
            $this->error('分类移动失败！');
        }
    }

    /* 编辑分类 */
    public function specdefedit(){
        $tbspecdef=D('product_specdef');
        $tbspecval=D('product_specval');

        $sid=I('sid');
        if(IS_POST){ //提交表单
            $data['name']=I('name');
            $data['comment']=I('comment');
            $data['sort']=I('sort');
            if(empty($data['name']) || empty($data['sort'])){
                $this->error('规格名称和排序不能为空');
            }
            if(false !== $tbspecdef->where(array('sid'=>$sid))->save($data)){
                $this->success('编辑成功！', U('specdef'));
            } else {
                $error = $tbspecdef->getError();
                $this->error(empty($error) ? '未知错误！' : $error);
            }
        } else {
            $info=$tbspecdef->where(array('sid'=>$sid))->find();
            $list=$tbspecval->where(array('status'=>'1','sid'=>$sid))->order('sort')->select();

            $this->assign('info', $info);
            $this->assign('list', $list);
            $this->meta_title = '编辑规格';
            $this->display();
        }
    }

    /* 新增分类 */
    public function specdefadd(){
        $specdef = D('product_specdef');

        if(IS_POST){ //提交表单
            $data['name']=I('name');
            $data['comment']=I('comment');
            $data['sort']=I('sort');
            $data['status']='1';
            if(empty($data['name']) || empty($data['sort'])){
                $this->error('规格名称和排序不能为空');
            }
            if(false !== $specdef->add($data)){
                $this->success('新增成功！', U('specdef'));
            } else {
                $error = $specdef->getError();
                $this->error(empty($error) ? '未知错误！' : $error);
            }
        } else {
            $this->assign('info',null);
            $this->meta_title = '新增规格';
            $this->display('specdefedit');
        }
    }

    public function specdefdel(){
        $tbspecdef=D('product_specdef');
        $tbspecval=D('product_specval');
        $tbcatespec=D('product_catalogspec');

        $sid=I('sid');
        if(false !== $tbspecdef->where(array('sid'=>$sid))->setField('status','-1')){
            $tbspecval->where(array('sid'=>$sid))->setField('status','-1');
            $tbcatespec->where(array('sid'=>$sid))->setField('status','-1');
            $this->success('删除成功！', U('specdef'));
        } else {
            $error = $tbspecdef->getError();
            $this->error(empty($error) ? '未知错误！' : $error);
        }
    }

    /* 编辑分类值 */
    public function specvaladd(){
        $tbspecval=D('product_specval');

        $data['sid']=I('defsid');
        $data['vid']=I('vid');
        $data['name']=I('valname');
        $data['sort']=I('valsort');
        $data['status']='1';
        if(empty($data['sid']) || $data['sid']=='0'){
            $this->error('规格不存在');
        }
        if($data['sort']==''){
            $data['sort']=='0';
        }
        if(empty($data['name'])){
            $this->error('规格值名称和排序不能为空');
        }
        if(false !== $tbspecval->add($data)){
            $this->success('添加成功！', U('specdefedit','sid='.$data['sid']));
        } else {
            $error = $tbspecval->getError();
            $this->error(empty($error) ? '未知错误！' : $error);
        }
    }

    public function specvaledit(){
        $tbspecval=D('product_specval');

        $vid=I('refvid');
        $sid=I('refsid');
        if(IS_POST){ //提交表单
            $data['name']=I('refname');
            $data['sort']=I('refsort');
            if(empty($sid) || $sid=='0'){
                $this->error('规格不存在');
            }
            if(empty($data['sort'])){
                $data['sort']='0';
            }
            if(empty($data['name'])){
                $this->error('规格值名称和排序不能为空');
            }
            if(false !== $tbspecval->where(array('vid'=>$vid))->save($data)){
                $this->success('编辑成功！', U('specdefedit','sid='.$sid));
            } else {
                $error = $tbspecval->getError();
                $this->error(empty($error) ? '未知错误！' : $error);
            }
        }
    }

    public function specvaldel(){
        $tbspecval=D('product_specval');

        $vid=I('vid');
        $sid=I('sid');
        if(empty($sid) || $sid=='0'){
            $this->error('规格不存在');
        }
        if(false !== $tbspecval->where(array('vid'=>$vid))->setField('status','-1')){
            $this->success('删除成功！', U('specdefedit','sid='.$sid));
        } else {
            $error = $tbspecval->getError();
            $this->error(empty($error) ? '未知错误！' : $error);
        }
    }

    public function catalogspecedit(){
        $tbcatespec=D('product_catalogspec');
        $tbcate=D('product_catalog');
        $tbspec=D('product_specdef');

        $cid=I('cid');
        if(IS_POST){ //提交表单
            $data['cid']=I('cid');
            $data['sid']=I('sid');
            $data['sort']=I('sort');
            $data['status']='1';
            if($data['cid']==0 || $data['sid']==0){
                $this->error('分类ID或规格ID错误', U('catalogspec'));
            }
            $cnt=$tbcatespec->where(array('cid'=>$data['cid'],'sid'=>$data['sid']))->count();
            if($cnt>0){
                $tbcatespec->where(array('cid'=>$data['cid'],'sid'=>$data['sid']))->setField('status','1');
                $this->success('添加成功！', U('catalogspecedit',array('cid'=>$cid)));
            }else{
                if(false !== $tbcatespec->add($data)){
                    $this->success('添加成功！', U('catalogspecedit',array('cid'=>$cid)));
                } else {
                    $error = $tbcatespec->getError();
                    $this->error(empty($error) ? '未知错误！' : $error);
                }
            }
        } else {
            $info=$tbcate->where(array('id'=>$cid))->find();
            $list=$tbcatespec->where(array('cid'=>$cid,'status'=>'1'))->order('sort')->select();
            $sidlist='';
            foreach ($list as $key => $value) {
                $sidlist=$sidlist.','.$value['sid'];
            }
            substr($sidlist, 1);
            if($sidlist==''){
                $speclist=$tbspec->where('1=2')->order('sort')->select();
                $specunuse=$tbspec->where(array('status'=>'1'))->order('sort')->select();
            }else{
                $map['sid']=array('in',$sidlist);
                $speclist=$tbspec->where($map)->order('sort')->select();
                $map['sid']=array('not in',$sidlist);
                $map['status']='1';
                $specunuse=$tbspec->where($map)->order('sort')->select();
            }
            $this->assign('info', $info);
            $this->assign('speclist', $speclist);
            $this->assign('specunuse', $specunuse);
            $this->meta_title = '编辑分类对应规格';
            $this->display();
        }
    }

    public function catalogspecdel(){
        $tbcatespec=D('product_catalogspec');

        $cid=I('cid');
        $sid=I('sid');
        if(false !== $tbcatespec->where(array('cid'=>$cid,'sid'=>$sid))->setField('status','-1')){
            $this->success('删除成功！', U('catalogspecedit',array('cid'=>$cid)));
        } else {
            $error = $tbspecval->getError();
            $this->error(empty($error) ? '未知错误！' : $error);
        }
    }

    public function productedit(){
        $tbproduct=D('product');
        $tbspecval=D('product_specval');
        $tbspecdef=D('product_specdef');
        if(IS_POST){ //提交表单
            $product_id=I('product_id');
            $pricemode=I('pricemode');
            $status=I('status');
            $productpart=I('productpart');
            $hackprice=I('hackprice');
            $marketprice=I('marketprice');
            $price=I('price');
            $data=array();
            if($pricemode==0){
                $data['product_id']=$product_id;
                $data['status']=$status;
                $data['productpart']=$productpart;
                $data['hackprice']=floor($hackprice*100);
                $data['marketprice']=floor($marketprice*100);
                $data['price']=floor($price*100);
                if(empty($data['hackprice']) || empty($data['marketprice']) || empty($data['price'])){
                    $this->error('所有价格均不能为空');
                }
            }else{
                $groupid=I('groupid');
                $data['product']['product_id']=$product_id;
                $data['product']['status']=$status;
                $data['product']['productpart']=$productpart;
                $data['prodspec']=array();
                foreach ($groupid as $key => $value) {
                    $tmp['group_id']=$value;
                    $tmp['hackprice']=floor($hackprice[$key]*100);
                    $tmp['marketprice']=floor($marketprice[$key]*100);
                    $tmp['price']=floor($price[$key]*100);
                    array_push($data['prodspec'], $tmp);
                }
                foreach ($data['prodspec'] as $key => $value) {
                    if(empty($value['hackprice']) || empty($value['marketprice']) || empty($value['price'])){
                        $this->error('所有价格均不能为空');
                    }
                }
            }
            if($tbproduct->updateProductInfo($pricemode,$data)){
                $this->success('修改成功！', U('productlist'));
            } else {
                $error = $tbproduct->getError();
                $this->error(empty($error) ? '无变更！' : $error);
            }
        }else{
            $product_id=I('product_id');
            $info=$tbproduct->getProductInfo($product_id);
            $specval=$tbspecval->getField('vid,name');
            $specdef=$tbspecdef->getField('sid,name');
            $statusinfo=C('PRODUCTSTATUS');
            $usespec=array();
            foreach ($info['prodspec'] as $key => $value) {
                $specg=explode('-', $value['specgroup']);
                $tmp="";
                foreach ($specg as $key1 => $value1) {
                    $sid_vid=explode('_', $value1);
                    $tmp=$tmp.$specval[$sid_vid[1]].'-';
                    if(!array_key_exists($sid_vid[0], $usespec)){
                        $usespec[$sid_vid[0]]['name']=$specdef[$sid_vid[0]];
                        $usespec[$sid_vid[0]]['subs'][$sid_vid[1]]=$specval[$sid_vid[1]];
                    }
                    if(!array_key_exists($sid_vid[1], $usespec[$sid_vid[0]]['subs'])){
                        $usespec[$sid_vid[0]]['subs'][$sid_vid[1]]=$specval[$sid_vid[1]];
                    }
                }
                $tmp=substr($tmp,0,-1);
                $info['prodspec'][$key]['groupname']=$tmp;
                $info['prodspec'][$key]['supprice']=$value['supprice']/100;
                $info['prodspec'][$key]['hackprice']=$value['hackprice']/100;
                $info['prodspec'][$key]['marketprice']=$value['marketprice']/100;
                $info['prodspec'][$key]['price']=$value['price']/100;
            }
            if(count($info['prodspec'])==0){
                $info['product']['supprice']=$info['product']['supprice']/100;
                $info['product']['hackprice']=$info['product']['hackprice']/100;
                $info['product']['marketprice']=$info['product']['marketprice']/100;
                $info['product']['price']=$info['product']['price']/100;
            }
            $this->assign('info',$info['product']);
            $this->assign('prodspec',$info['prodspec']);
            $this->assign('statusinfo',$statusinfo);
            $this->assign('usespec',$usespec);
            $this->meta_title = '编辑商品信息';
            $this->display();
        }
    }
}
