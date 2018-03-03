<?php

namespace Common\Api;
use Common\Model\SupplierModel;

class SupplierApi {

    protected $model;

    public function __construct(){
        $this->model = new SupplierModel();
    }

    public function register($data,$files){
        $return  = array('status' => 1, 'info' => '申请成功', 'data' => '');
        if($this->model->register($data,$files)){
            $return['status'] = 1;
        } else {
            $return['status'] = 0;
            $return['info']   = $this->model->getError();
        }
        
        return $return;
    }

    public function registerprocess($uid){
        return $this->model->registerprocess($uid);
    }

    public function updatesupplierprodspec($uid,$selspec){
        $data=array();
        for($i=0;$i<count($selspec);$i++) {
            array_push($data, array('uid'=>$uid,'sid'=>$selspec[$i]));
        }
        M('supplier_spec')->where(array('uid'=>$uid))->delete();
        $res=M('supplier_spec')->addAll($data);

        return $res;
    }
}