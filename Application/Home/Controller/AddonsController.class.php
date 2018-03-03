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

/**
 * 扩展控制器
 * 用于调度各个扩展的URL访问需求
 */
class AddonsController extends Controller{

	public function _initialize(){
		/* 读取数据库中的配置 */
        $config = S('DB_CONFIG_DATA');
        if(!$config){
            $config = api('Config/lists');
            S('DB_CONFIG_DATA',$config);
        }
        C($config); //添加配置
	}

	protected $addons = null;

	public function execute($_addons = null, $_controller = null, $_action = null){
		if(C('URL_CASE_INSENSITIVE')){
			$_addons = ucfirst(parse_name($_addons, 1));
			$_controller = parse_name($_controller,1);
		}

	 	$TMPL_PARSE_STRING = C('TMPL_PARSE_STRING');
        $TMPL_PARSE_STRING['__ADDONROOT__'] = __ROOT__ . "/Addons/{$_addons}";
        C('TMPL_PARSE_STRING', $TMPL_PARSE_STRING);

		if(!empty($_addons) && !empty($_controller) && !empty($_action)){
			$Addons = A("Addons://{$_addons}/{$_controller}")->$_action();
		} else {
			$this->error('没有指定插件名称，控制器或操作！');
		}
	}

	//获取中国省份信息
	public function getChinaCity(){
		$data = D('chinacity')->order('id')->select();
		foreach ($data as $key => &$value) {
			$value['id']=intval($value['id']);
			$value['level']=intval($value['level']);
			$value['upid']=intval($value['upid']);
		}
		$this->ajaxReturn(array('success'=>true,'body'=>$data));
	}

    /*
     *获取省市区
     */
    public function getChinacityList(){
        $list = D('chinacity')->order('id')->select();
        $dataprov=array();
        foreach ($list as $key => $value) {
            if($value['level']==1){
                if(!array_key_exists($value['id'], $dataprov)){
                    $dataprov[$value['id']]=array('name'=>$value['name'],'data'=>array());
                }
            }elseif($value['level']==2){
                array_push($dataprov[$value['upid']]['data'], array('id'=>$value['id'],'name'=>$value['name']));
            }
        }

        $datacity=array();
        foreach ($list as $key => $value) {
            if($value['level']==2){
                if(!array_key_exists($value['id'], $datacity)){
                    $datacity[$value['id']]=array();
                }
            }elseif($value['level']==3){
                array_push($datacity[$value['upid']], array('id'=>$value['id'],'name'=>$value['name']));
            }
        }

        $data['prov']=$dataprov;
        $data['city']=$datacity;
        $this->ajaxReturn($data);
    }
}
