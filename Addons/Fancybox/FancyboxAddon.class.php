<?php

namespace Addons\Fancybox;
use Common\Controller\Addon;

/**
 * 图片弹出播放插件
 * @author che1988  zhuxiulai@qq.com
 */

    class FancyboxAddon extends Addon{

        public $info = array(
            'name'=>'Fancybox',
            'title'=>'图片弹出播放',
            'description'=>'让文章内容页的图片有弹出图片播放的效果',
            'status'=>1,
            'author'=>'che1988',
            'version'=>'0.1'
        );

        public function install(){
            /* 先判断插件需要的钩子是否存在 */
            $this->getisHook('J_Fancybox', $this->info['name'], $this->info['description']);
            return true;
        }

        public function uninstall(){
            return true;
        }

        //获取插件所需的钩子是否存在
        public function getisHook($str, $addons, $msg=''){
            $hook_mod = M('Hooks');
            $where['name'] = $str;
            $gethook = $hook_mod->where($where)->find();
            if(!$gethook || empty($gethook) || !is_array($gethook)){
                $data['name'] = $str;
                $data['description'] = $msg;
                $data['type'] = 1;
                $data['update_time'] = NOW_TIME;
                $data['addons'] = $addons;
                if( false !== $hook_mod->create($data) ){
                    $hook_mod->add();
                }
            }
        }
        
        //实现的documentDetailAfter钩子方法
        public function documentDetailAfter($param){
			$this->assign('addons_config', $this->getConfig());
			$this->display('content');
        }

        public function J_Fancybox($param){
            $this->assign('addons_config', $this->getConfig());
            $this->display('content');
        }
    }