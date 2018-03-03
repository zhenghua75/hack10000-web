<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: huajie <banhuajie@163.com>
// +----------------------------------------------------------------------

namespace Common\Model;
use Think\Model;
use Think\Upload;

/**
 * 图片模型
 * 负责图片的上传
 */

class PictureModel extends Model{
    /**
     * 自动完成
     * @var array
     */
    protected $_auto = array(
        array('status', 1, self::MODEL_INSERT),
        array('create_time', NOW_TIME, self::MODEL_INSERT),
    );

    /**
     * 文件上传
     * @param  array  $files   要上传的文件列表（通常是$_FILES数组）
     * @param  array  $setting 文件上传配置
     * @param  string $driver  上传驱动名称
     * @param  array  $config  上传驱动配置
     * @return array           文件上传成功后的信息
     */
    public function upload($uid,$imgtype,$files,$pcid=0,$ischekform=0){
      $app_name='HOME';
      $imgserver=C('IMAGE_SERVER');
      $imgserverdomain=C('IMAGE_SERVER_DOMAIN');
      $setting=C('PICTURE_UPLOAD');
      $driver = C('PICTURE_UPLOAD_DRIVER');
      $config=C("UPLOAD_{$driver}_CONFIG");
      $picconfigname=$imgserver[$config['host']].'-'.strtoupper($app_name);
      $size=C($picconfigname);

      /* 上传文件 */
      $setting['callback'] = array($this, 'isFile');
	    $setting['removeTrash'] = array($this, 'removeTrash');
      $setting['savePath'] = strtolower($imgtype).'/';

      $ext='.jpg';
      foreach ($files as $key => $value) {
          switch ($value['type']) {
            case 'image/jpeg':
              $ext='.jpg';
              break;
            case 'image/jpg':
              $ext='.jpg';
              break;
            case 'image/png':
              $ext='.png';
              break;
          }
          $sTempFileName = '../Runtime/Temp/' . md5(time().rand()).$ext;
          $database64=base64_decode($value['imgbase64']);
          file_put_contents($sTempFileName, $database64);
          if (file_exists($sTempFileName) && filesize($sTempFileName) > 0) {
              $aSize = getimagesize($sTempFileName);
              if (!$aSize) {
                  @unlink($sTempFileName);
              }
              $newsize=filesize($sTempFileName);
              $files[$key]['tmp_name']=$sTempFileName;
              $files[$key]['size']=$newsize;
          }
          unset($files[$key]['imgbase64']);
      }

      $Upload = new Upload($setting, $driver, $config, $size, $imgtype, $ischekform);
      $info   = $Upload->upload($files,$imgtype);

      if(empty($pcid)){
          $pcid=0;
      }
      if($info){ //文件上传成功，记录文件信息
          foreach ($info as $key => &$value) {
              /* 已经存在文件记录 */
              if(isset($value['id']) && is_numeric($value['id'])){
                  $mempic=D('member_picture');
                  $res=$mempic->where(array('uid'=>$uid,'pid'=>$value['id'],'imgtype'=>strtolower($imgtype),'pcid'=>$pcid))->find();
                  if(!$res){
                    $mempic->add(array('uid'=>$uid,'pid'=>$value['id'],'imgtype'=>strtolower($imgtype),'pcid'=>$pcid));
                  }
                  continue;
              }

              /* 记录文件信息 */
              $value['configname'] = $picconfigname;
              $value['org_filename'] = $value['name'];
              $value['new_filename'] = $value['savename'];
              $value['imgtype'] = strtolower($imgtype);
              if($this->create($value) && ($id = $this->add())){
                  $value['id'] = $id;
                  $mempic=D('member_picture');
                  $res=$mempic->where(array('uid'=>$uid,'pid'=>$id,'imgtype'=>strtolower($imgtype),'pcid'=>$pcid))->find();
                  if(!$res){
                    $mempic->add(array('uid'=>$uid,'pid'=>$id,'imgtype'=>strtolower($imgtype),'pcid'=>$pcid));
                  }
              } else {
                  //TODO: 文件上传成功，但是记录文件信息失败，需记录日志
                  unset($info[$key]);
              }
          }
          return $info; //文件上传成功
      } else {
          $this->error = $Upload->getError();
          return false;
      }
    }

    public function qrcodeupload($uid,$imgtype,$guid,$ischekform=0){
      $app_name=MODULE_NAME;
      $imgserver=C('IMAGE_SERVER');
      $imgserverdomain=C('IMAGE_SERVER_DOMAIN');
      $setting=C('PICTURE_UPLOAD');
      $driver = C('PICTURE_UPLOAD_DRIVER');
      $config=C("UPLOAD_{$driver}_CONFIG");
      $picconfigname=$imgserver[$config['host']].'-'.strtoupper($app_name);
      $size=C($picconfigname);

      /* 上传文件 */
      $setting['callback'] = array($this, 'isFile');
      $setting['removeTrash'] = array($this, 'removeTrash');
      $setting['savePath'] = strtolower($imgtype).'/';

      $strrand=md5(time().rand());
      $sTempFileName = '../Runtime/Temp/' . $strrand.'.png';

      Vendor("phpqrcode.phpqrcode");
      $url = 'http://www.hack10000.com/Shop/index?gi='.$guid;
      $level = 'H';
      $csize = 4;
      $model = new \QRcode();
      $model::png($url, $sTempFileName, $level, $csize, 2);

      $files['qrcode']['name']=$strrand.'.png';
      $files['qrcode']['type']='image\/png';
      if (file_exists($sTempFileName) && filesize($sTempFileName) > 0) {
          $aSize = getimagesize($sTempFileName);
          if (!$aSize) {
              @unlink($sTempFileName);
          }
          $newsize=filesize($sTempFileName);
          $files['qrcode']['tmp_name']=$sTempFileName;
          $files['qrcode']['size']=$newsize;
      }

      $Upload = new Upload($setting, $driver, $config, $size, $imgtype, $ischekform);
      $info   = $Upload->upload($files);

      if($info){ //文件上传成功，记录文件信息
          foreach ($info as $key => &$value) {
              /* 已经存在文件记录 */
              if(isset($value['id']) && is_numeric($value['id'])){
                  $mempic=D('member_picture');
                  $res=$mempic->where(array('uid'=>$uid,'pid'=>$value['id'],'imgtype'=>strtolower($imgtype)))->find();
                  if(!$res){
                    $mempic->add(array('uid'=>$uid,'pid'=>$value['id'],'imgtype'=>strtolower($imgtype)));
                  }
                  continue;
              }

              /* 记录文件信息 */
              $value['configname'] = $picconfigname;
              $value['org_filename'] = $value['name'];
              $value['new_filename'] = $value['savename'];
              $value['imgtype'] = strtolower($imgtype);
              if($this->create($value) && ($id = $this->add())){
                  $value['id'] = $id;
                  $mempic=D('member_picture');
                  $res=$mempic->where(array('uid'=>$uid,'pid'=>$id,'imgtype'=>strtolower($imgtype)))->find();
                  if(!$res){
                    $mempic->add(array('uid'=>$uid,'pid'=>$id,'imgtype'=>strtolower($imgtype)));
                  }
              } else {
                  //TODO: 文件上传成功，但是记录文件信息失败，需记录日志
                  unset($info[$key]);
              }
          }
          return $info; //文件上传成功
      } else {
          $this->error = $Upload->getError();
          return false;
      }
    }

    /**
     * 下载指定文件
     * @param  number  $root 文件存储根目录
     * @param  integer $id   文件ID
     * @param  string   $args     回调函数参数
     * @return boolean       false-下载失败，否则输出下载文件
     */
    public function download($root, $id, $callback = null, $args = null){
        /* 获取下载文件信息 */
        $file = $this->find($id);
        if(!$file){
            $this->error = '不存在该文件！';
            return false;
        }

        /* 下载文件 */
        switch ($file['location']) {
            case 0: //下载本地文件
                $file['rootpath'] = $root;
                return $this->downLocalFile($file, $callback, $args);
            case 1: //TODO: 下载远程FTP文件
                break;
            default:
                $this->error = '不支持的文件存储类型！';
                return false;

        }

    }

    /**
     * 检测当前上传的文件是否已经存在
     * @param  array   $file 文件上传数组
     * @return boolean       文件信息， false - 不存在该文件
     */
    public function isFile($file,$imgtype){
        if(empty($file['md5'])){
            return false;
        }
        /* 查找文件 */
		    $map = array('md5' => $file['md5'],'sha1'=>$file['sha1'],'imgtype'=>strtolower($imgtype));
        return $this->field(true)->where($map)->find();
    }

    /**
     * 下载本地文件
     * @param  array    $file     文件信息数组
     * @param  callable $callback 下载回调函数，一般用于增加下载次数
     * @param  string   $args     回调函数参数
     * @return boolean            下载失败返回false
     */
    private function downLocalFile($file, $callback = null, $args = null){
        if(is_file($file['rootpath'].$file['savepath'].$file['savename'])){
            /* 调用回调函数新增下载数 */
            is_callable($callback) && call_user_func($callback, $args);

            /* 执行下载 */ //TODO: 大文件断点续传
            header("Content-Description: File Transfer");
            header('Content-type: ' . $file['type']);
            header('Content-Length:' . $file['size']);
            if (preg_match('/MSIE/', $_SERVER['HTTP_USER_AGENT'])) { //for IE
                header('Content-Disposition: attachment; filename="' . rawurlencode($file['name']) . '"');
            } else {
                header('Content-Disposition: attachment; filename="' . $file['name'] . '"');
            }
            readfile($file['rootpath'].$file['savepath'].$file['savename']);
            exit;
        } else {
            $this->error = '文件已被删除！';
            return false;
        }
    }

	/**
	 * 清除数据库存在但本地不存在的数据
	 * @param $data
	 */
	public function removeTrash($data){
		$this->where(array('id'=>$data['id'],))->delete();
	}

  /**
   * 获取图片数据
   * @param $id
   */
  public function getPictureData($id){
    $map = array('id' => $id,);
    return $this->field(true)->where($map)->find();
  }
}
