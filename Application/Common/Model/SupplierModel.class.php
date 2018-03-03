<?php

namespace Common\Model;
use Think\Model;
/**
 * 会员模型
 */
class SupplierModel extends Model{
	/* 用户模型自动验证 */
	protected $_validate = array(
		array('uid', 'require', '未登录'),
		array('company', 'require', '公司名称必须填写'),
		array('province', 'require', '公司所在区域必须填写'),
		array('city', 'require', '公司所在区域必须填写'),
		array('district', 'require', '公司所在区域必须填写'),
		array('busilicense', 'require', '营业执照注册号必须填写'),
		array('busilicpid', 'require', '营业执照照片必须填写'),
		array('orgpid', 'require', '组织机构证照片必须填写'),
		array('banklicpid', 'require', '银行许可照片必须填写'),
		array('backno', 'require', '银行帐号必须填写'),
		array('corporatename', 'require', '法人代表姓名必须填写'),
		array('corporateidno', 'require', '法人身份证号必须填写'),
		array('linkname', 'require', '联系人必须填写'),
		array('linkphone', 'require', '联系电话必须填写'),
		array('uid', '', '不能重复申请', self::MUST_VALIDATE, 'unique'),
	);

	/* 用户模型自动完成 */
	protected $_auto = array(
		array('status', 0, self::MODEL_INSERT),
	);

	public function register($data,$files){
        foreach ($files as $key => $value) {
            $sTempFileName = '../Runtime/Temp/' . md5(time().rand()).'.jpg';
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

        $picmodel = new PictureModel();
        $info = $picmodel->upload('SUPPLIER',$files);

        foreach ($files as $key => $value) {
            @unlink($value['tmp_name']);
            foreach ($info as $key1 => $value1) {
        		if($key=='file-busilicpid' && $value1['org_filename']==$value['name']){
        			$data['busilicpid']=$value1['id'];
        		}
        		if($key=='file-orgpid' && $value1['org_filename']==$value['name']){
        			$data['orgpid']=$value1['id'];
        		}
        		if($key=='file-banklicpid' && $value1['org_filename']==$value['name']){
        			$data['banklicpid']=$value1['id'];
        		}
        	}
        }

		if($this->create($data)){
			$result = $this->add();
			return $result;
		} else {
          return false;
		}
	}

	public function registerprocess($uid){
		$map['uid']=$uid;
		$res=$this->where($map)->field('status,company')->find();
		if($res){
			$result['status']=$res['status'];
			$result['company']=$res['company'];
			switch ($result['status']) {
				case '0':
					$result['info']="您的供应商申请已提交成功，我们会尽快进行审核，请耐心等待！";
					break;
				case '100':
					$result['info']="正在审核您提供的信息真实性！";
					break;
				case '101':
					$result['info']="信息审核已经完成，正在授权！";
					break;
				case '1':
					$result['info']="审核通过，您已经是我们的供应商用户了！";
					break;
				default:
					$result['info']="您的供应商已被禁用";
					break;
			}
		}else{
			$result=null;
		}

		return $result;
	}

	public function getSupplierCompany($uid){
		return $this->where(array('uid'=>$uid))->getField('company');
	}
}
