<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Home\Controller;
use Common\Api\DocumentApi;

/**
 * 文档模型控制器
 * 文档模型列表和详情
 */
class ArticleController extends HomeController {

    /* 文档模型频道页 */
	public function index(){
		/* 分类信息 */
		$category = $this->category();

		//频道页只显示模板，默认不读取任何内容
		//内容可以通过模板标签自行定制

		/* 模板赋值并渲染模板 */
		$this->assign('category', $category);
		$this->display($category['template_index']);
	}

	/* 文档模型列表页 */
	public function lists($p = 1){
		/* 分类信息 */
		$category = $this->category();

		/* 获取当前分类列表 */
		$Document = D('Document');
		$list = $Document->page($p, $category['list_row'])->lists($category['id']);
		if(false === $list){
			$this->error('获取列表数据失败！');
		}

		/* 模板赋值并渲染模板 */
		$this->assign('category', $category);
		$this->assign('list', $list);
		$this->display($category['template_lists']);
	}

	/* 文档模型详情页 */
	public function detail($id = 0, $p = 1, $source='web'){
		/* 标识正确性检测 */
		if(!($id && is_numeric($id))){
			if($source=='app'){
				$this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'文档ID错误！')));
			}else{
				$this->error('文档ID错误！');
			}
		}

		/* 页码检测 */
		$p = intval($p);
		$p = empty($p) ? 1 : $p;

		/* 获取详细信息 */
		$Document = D('Document');
		$info = $Document->detail($id);
		if(!$info){
			if($source=='app'){
				$this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>$Document->getError())));
			}else{
				$this->error($Document->getError());
			}
		}else{
			if($source=='app'){
				$this->ajaxReturn(array('success'=>true,'body'=>$info['content']));
			}else{
				/* 分类信息 */
				$category = $this->category($info['category_id']);
				$parentcate = $this->category($category['pid']);
				$uid=is_login();
				if($parentcate['name']=='hackevent'){
			        if (!$uid) {
			            if($source=='app'){
			                $token=I('token');
			                if($token=='' || $token=='0'){
			                    $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>false,'error'=>'您还没有登陆')));
			                }else{
			                    $Member = D('Member');
			                    if(!$Member->applogin($token)){ //登录用户
			                        $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>false,'error'=>'您还没有登陆')));
			                    }
			                }
			            }else{
			                $this->error( '您还没有登陆',U('User/login'),false,5);
			            }
			        }
				}

				/* 获取模板 */
				if(!empty($info['template'])){//已定制模板
					$tmpl = $info['template'];
				} elseif (!empty($category['template_detail'])){ //分类已定制模板
					$tmpl = $category['template_detail'];
				} else { //使用默认模板
					$tmpl = 'Article/'. get_document_model($info['model_id'],'name') .'/detail';
				}

				/* 获取表单 */
				if(!empty($info['formtype'])){
					$formconfig=C($info['formtype']);
					$formlist=json_decode($formconfig,true);
				}

				/* 更新浏览数 */
				$map = array('id' => $id);
				$Document->where($map)->setInc('view');

				$signup='0';
				if($uid){
					$res=D('event_signup')->where(array('uid'=>$uid,'event_id'=>$info['id']))->find();
					if($res){
						$signup=$res['status'];
					}
				}
				$formmid=floor(count($formlist)/2);
				/* 模板赋值并渲染模板 */
				$this->assign('category', $category);
				$this->assign('info', $info);
				$this->assign('formlist', $formlist);
				$this->assign('formmid', $formmid);
				$this->assign('signup', $signup);
				$this->assign('page', $p); //页码
		        $param=array('province'=>530000, 'city'=>530100, 'district'=>530102);
		        $this->assign('param',$param);
				$this->assign('pagename','page-article');
				$this->display($tmpl);
			}
		}
	}

	public function formconfig($source='web'){
		$id=I('id');
		/* 标识正确性检测 */
		if(!($id && is_numeric($id))){
			if($source=='app'){
				$this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'文档ID错误！')));
			}else{
				$this->error('文档ID错误！');
			}
		}

		/* 获取详细信息 */
		$Document = D('Document');
		$info = $Document->detail($id);
		if(!$info){
			if($source=='app'){
				$this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>$Document->getError())));
			}
		}else{
			if($source=='app'){
				if(!empty($info['formtype'])){//已定制模板
					$form=C($info['formtype']);
				}
				$this->ajaxReturn(array('success'=>true,'body'=>$form));
			}
		}
	}

	/* 文档分类检测 */
	private function category($id = 0){
		/* 标识正确性检测 */
		$id = $id ? $id : I('get.category', 0);
		if(empty($id)){
			$this->error('没有指定文档分类！');
		}

		/* 获取分类信息 */
		$category = D('Category')->info($id);
		if($category && 1 == $category['status']){
			switch ($category['display']) {
				case 0:
					$this->error('该分类禁止显示！');
					break;
				//TODO: 更多分类显示状态判断
				default:
					return $category;
			}
		} else {
			$this->error('分类不存在或被禁用！');
		}
	}

	public function eventsignup(){
		$uid=is_login();
		$source=I('source');
		$token=I('token');
        if (!$uid) {
            if($source=='app'){
                if($token=='' || $token=='0'){
                    $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>false,'error'=>'您还没有登陆')));
                }else{
                    $Member = D('Member');
                    if(!$Member->applogin($token)){ //登录用户
                        $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>false,'error'=>'您还没有登陆')));
                    }
                }
            }else{
                $this->error( '您还没有登陆',U('User/login'),false,5);
            }
        }
        $authtype=I('authtype');
        if($authtype=='maker'){
	        if($this->memberstatus[1] || $this->memberstatus[2]){
	            if($this->memberstatus[1] && $this->memberstatus[1]['status']!='1'){
	                if($source=='app'){
	                    $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'您的创客申请还未通过，请耐心等待')));
	                }else{
	                    $this->error('您的创客申请还未通过，请耐心等待',U('User/makerregister'));
	                }
	            }
	            if($this->memberstatus[2] && $this->memberstatus[2]['status']!='1'){
	                if($source=='app'){
	                    $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'您的创客申请还未通过，请耐心等待')));
	                }else{
	                    $this->error('您的创客申请还未通过，请耐心等待',U('User/makerregister'));
	                }
	            }
	        }else{
	            if($source=='app'){
	                $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'您还没有成为创客，请先申请成为创客')));
	            }else{
	                $this->error('您还没有成为创客，请先申请成为创客',U('User/makerregister'));
	            }
	        }
        }
        $province=I('province');
        $city=I('city');
        $district=I('district');
        $truename=I('truename');
        $schoolname=I('schoolname');
        $schooladdress=I('schooladdress');
        $sex=I('sex');
        $mobile=I('mobile');
        $qq=I('qq');
        $wxid=I('wxid');
        $idaddress=I('idaddress');
        $checkcondition=I('checkcondition');
        if($source=='app'){
        	$checkcondition=json_decode($checkcondition,true);
        }
        $area=convert_province_string($province,$city,$district);
        $data['uid']=$uid;
        $data['event_id']=I('eventid');
        $data['status']='1';
        $data['created']=time();

        $extra=array();
        $field=array('field'=>1,'name'=>'家所在地区','value'=>$area);
        array_push($extra, $field);
        $field=array('field'=>2,'name'=>'姓名','value'=>$truename);
        array_push($extra, $field);
        $field=array('field'=>3,'name'=>'所读大学名称','value'=>$schoolname);
        array_push($extra, $field);
        $field=array('field'=>4,'name'=>'所读大学地址','value'=>$schooladdress);
        array_push($extra, $field);
        $field=array('field'=>5,'name'=>'手机号','value'=>$mobile);
        array_push($extra, $field);
        $field=array('field'=>6,'name'=>'QQ号','value'=>$qq);
        array_push($extra, $field);
        $field=array('field'=>7,'name'=>'微信号','value'=>$wxid);
        array_push($extra, $field);
        $field=array('field'=>8,'name'=>'身份证地址','value'=>$idaddress);
        array_push($extra, $field);
        $field=array('field'=>9,'name'=>'性别','value'=>$sex);
        array_push($extra, $field);
        $field=array('field'=>10,'name'=>'满足的条件','value'=>$checkcondition);
        array_push($extra, $field);
        $data['extra']=json_encode($extra);

        $docapi=new DocumentApi;
        if($docapi->eventSignup($data)){
        	if($source=='app'){
        		$this->ajaxReturn(array('success'=>true,'body'=>array('isLogined'=>true,'error'=>'报名成功')));
        	}else{
				$this->success('报名成功',U('User/makerevent'));
        	}
        }else{
        	if($source=='app'){
        		$this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'报名失败')));
        	}else{
				$this->error('报名失败');
        	}
        }
	}

	public function eventsignupcom(){
		$source=I('source');
		$token=I('token');
        $uid=0;
        if($source=='app'){
            $auth=session('user_auth');
            if(empty($auth) || $auth['token']!=$token){
                $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>false,'error'=>'您还没有登陆')));
            }
            $uid=$auth['uid'];
        }else{
            $uid=is_login();
        }
        if($uid==0){
            if($source=='app'){
                $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>false,'error'=>'您还没有登陆')));
            }else{
                $this->error( '您还没有登陆',U('User/login'),false,5);
            }
        }
        if($this->memberstatus[1] || $this->memberstatus[2]){
            if($this->memberstatus[1] && $this->memberstatus[1]['status']!='1'){
                if($source=='app'){
                    $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'您的创客申请还未通过，请耐心等待')));
                }else{
                    $this->error('您的创客申请还未通过，请耐心等待',U('User/makerregister'));
                }
            }
            if($this->memberstatus[2] && $this->memberstatus[2]['status']!='1'){
                if($source=='app'){
                    $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'您的创客申请还未通过，请耐心等待')));
                }else{
                    $this->error('您的创客申请还未通过，请耐心等待',U('User/makerregister'));
                }
            }
        }else{
            if($source=='app'){
                $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'您还没有成为创客，请先申请成为创客')));
            }else{
                $this->error('您还没有成为创客，请先申请成为创客',U('User/makerregister'));
            }
        }
        $data['uid']=$uid;
        $data['event_id']=I('eventid');
        $data['status']='1';
        $data['created']=time();
        if($source=='app'){
        	$data['extra']=I('data');
        }else{
			$Document = D('Document');
			$info = $Document->detail($data['event_id']);
			$formlist=array();
			if(!empty($info['formtype'])){
				$formconfig=C($info['formtype']);
				$formlist=json_decode($formconfig,true);
			}

			foreach ($formlist as $key => &$value) {
				if($value['type']=='checkboxlink'){
					if(I($value['field'])){
						$value['value']=$value['option'][1];
					}else{
						$value['value']=$value['option'][0];
					}
				}else{
					$value['value']=I($value['field']);
				}
			}
			$data['extra']=json_encode($formlist);
        }

        $checkflag=true;
        $checkdata=json_decode($data['extra'],true);
        foreach ($checkdata as $key => $value) {
        	if(empty($value['value'])){
        		$checkflag=false;
        		break;
        	}
        }
        if($checkflag){
	        $docapi=new DocumentApi;
	        if($docapi->eventSignup($data)){
	        	if($source=='app'){
	        		$this->ajaxReturn(array('success'=>true,'body'=>array('isLogined'=>true,'error'=>'报名成功')));
	        	}else{
					$this->success('报名成功',U('User/makerevent'));
	        	}
	        }else{
	        	if($source=='app'){
	        		$this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'报名失败')));
	        	}else{
					$this->error('报名失败');
	        	}
	        }
        }else{
        	if($source=='app'){
        		$this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'报名失败，请填写相关信息')));
        	}else{
				$this->error('报名失败，请填写相关信息');
        	}
        }
	}

	public function eventindex($source='web'){
        $uid=is_login();
        if($uid==0){
            $this->error( '您还没有登陆',U('User/login'),false,5);
        }
        if($this->memberstatus[1] || $this->memberstatus[2]){
            if($this->memberstatus[1] && $this->memberstatus[1]['status']!='1'){
                $this->error('您的创客申请还未通过，请耐心等待',U('User/makerregister'));
            }
            if($this->memberstatus[2] && $this->memberstatus[2]['status']!='1'){
                $this->error('您的创客申请还未通过，请耐心等待',U('User/makerregister'));
            }
        }else{
            $this->error('您还没有成为创客，请先申请成为创客',U('User/makerregister'));
        }
        $cate=I('ca');
        $docapi=new DocumentApi;
        $category=$docapi->getEventCategory('hackevent');
        $list = $docapi->getDocumentListByCate($uid,$cate);
        foreach ($category as $key => $value) {
        	if($cate==$value['name']){
        		$categoryname=$value['title'];
        	}
        }
        $this->assign('category', $category);
        $this->assign('categoryname', $categoryname);
        $this->assign('list', $list);
        $this->assign('cate', $cate);
        $this->meta_title = '慧爱活动';
        $this->assign('pagename','page-eventindex');
        $this->display();
	}
}
