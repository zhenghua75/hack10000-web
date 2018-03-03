<?php
/**
 * Created by PhpStorm.
 * User: sunny
 * Date: 2015/11/10
 * Time: 11:43
 */
namespace Addons\Crop\Controller;
use Home\Controller\AddonsController;

class CropController extends AddonsController
{
    public function crop(){
        $filename=$_POST['filename'];
        //除去第一个/
        $filename=substr($filename,1);
        //获取第一个以及最后一个/的位置
        $last=strrpos($filename,'/');
        $first=strpos($filename,'/');

        //截取字符串
        //定义常量，用来进行进一步处理
        define('UPLOAD_URL', __ROOT__.substr($filename,$first,($last-$first+1)));
        define('UPLOAD_PATH', '.'.substr($filename,$first,($last-$first+1)));
        $targ_w = $_POST['w'];
        $targ_h = $_POST['h'];
        $jpeg_quality = 90;
        //随机生成缩率图文件名字
        $randnum=rand(1,100);

        if(isset($_POST['thumbnail'])){
            $thumbnail = str_ireplace(UPLOAD_URL, '', $_POST['thumbnail']); //将URL转化为本地地址
            unlink(UPLOAD_PATH.$thumbnail);
        }

        $filename = str_ireplace(substr(__ROOT__,1).substr($filename,$first,($last-$first+1)), '', $filename); //将URL转化为本地地址

        $src = UPLOAD_PATH.$filename;

        $img_r = imagecreatefromjpeg($src);
        $dst_r = ImageCreateTrueColor( $targ_w, $targ_h );

        imagecopyresampled($dst_r,$img_r,0,0,$_POST['x'],$_POST['y'],
            $targ_w,$targ_h,$_POST['w'],$_POST['h']);

        $thumbname="thumbnail_".$targ_h."X".$targ_w."_".$randnum."_".$filename;

        $thumblocal=UPLOAD_PATH.$thumbname;

        imagejpeg($dst_r,$thumblocal,$jpeg_quality);

        $data=array(
            'img'=>UPLOAD_URL.$thumbname,
            'msg'=>"success",
        );
        $this->ajaxReturn($data);
    }
}