<?php

namespace Addons\Crop;
use Common\Controller\Addon;

/**
 * 截图钩子插件
 * @author hack10000
 */

    class CropAddon extends Addon{

        public $info = array(
            'name'=>'Crop',
            'title'=>'截图插件',
            'description'=>'这是一个截图插件，使用了jquery crop',
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

        //实现的ImageCrop钩子方法
        public function ImageCrop($param){
            $this->assign("imagepath",$param['imagepath']);
            $this->display("content");
        }

    }