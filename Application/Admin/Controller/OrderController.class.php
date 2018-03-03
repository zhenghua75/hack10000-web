<?php

namespace Admin\Controller;

/**
 * 后台分类管理控制器
 * @author klong
 */
class OrderController extends AdminController {


    /**
     * 消费者订单
     */
    public function cashorder(){
        $map['settletype']=array('in','cash,coupon');
        $paystatus=I('payst');
        $qid=I('qid');
        switch ($paystatus) {
            case '1':
                $map['paid']=1;
                break;
            case '2':
                $map['paid']=0;
                $map['thirdseq']='';
                break;
            case '3':
                $map['paid']=0;
                $map['thirdseq']=array('neq','');
                $map['thirdbackpaid']=0;
                break;
            default:
                # code...
                break;
        }
        if($qid){
            $map['orderid']=$qid;
        }
        $list=D('order_settle')->where($map)->select();
        foreach ($list as $key => &$value) {
            $value['nickname']=D('Member')->getNickName($value['uid']);
            switch ($value['settletype']) {
                case 'hcoin':
                    $value['settletypename']='慧爱币';
                    break;
                case 'cash':
                    $value['settletypename']='现金';
                    break;
                case 'coupon':
                    $value['settletypename']='优惠券';
                    break;
            }
            if($value['paid']=='0'&&$value['thirdseq']==''){
                $value['paystatusname']='未支付';
                $value['paystatus']='2';
            }elseif($value['paid']=='0'&&$value['thirdseq']!=''){
                $value['paystatusname']='支付失败';
                $value['paystatus']='3';
            }elseif($value['paid']=='0'){
                $value['paystatusname']='未支付';
                $value['paystatus']='2';
            }else{
                $value['paystatusname']='已支付';
                $value['paystatus']='1';
            }
            $value['amount']=$value['amount']/100;
            $value['thirdtime']=substr($value['thirdtime'],0,4).'-'.substr($value['thirdtime'],4,2).'-'.substr($value['thirdtime'],6,2).' '.substr($value['thirdtime'],8,2).':'.substr($value['thirdtime'],10,2).':'.substr($value['thirdtime'],12,2);
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
        $this->assign('payst', $paystatus);
        $this->meta_title = '消费者订单';
        $this->display();
    }

    /**
     * 创客订单
     */
    public function hcoinorder(){
        $map['settletype']='hcoin';
        $paystatus=I('payst');
        switch ($paystatus) {
            case '1':
                $map['paid']=1;
                break;
            case '2':
                $map['paid']=0;
                break;
            default:
                # code...
                break;
        }
        $list=D('order_settle')->where($map)->select();
        foreach ($list as $key => &$value) {
            $value['nickname']=D('Member')->getNickName($value['uid']);
            switch ($value['settletype']) {
                case 'hcoin':
                    $value['settletypename']='慧爱币';
                    break;
                case 'cash':
                    $value['settletypename']='现金';
                    break;
                case 'coupon':
                    $value['settletypename']='优惠券';
                    break;
            }
            if($value['paid']=='0'){
                $value['paystatusname']='未支付';
            }else{
                $value['paystatusname']='已支付';
            }
            $value['amount']=$value['amount']/100;
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
        $this->assign('payst', $paystatus);
        $this->meta_title = '消费者订单';
        $this->display();
    }
}
