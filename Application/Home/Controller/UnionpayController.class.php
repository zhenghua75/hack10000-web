<?php
// 银联支付测试

namespace Home\Controller;
use Common\Api\LogApi;
use Common\Api\OrderApi;
use Common\Api\ProductApi;

/**
 * 前台首页控制器
 * 主要获取首页聚合数据
 */
class UnionpayController extends HomeController {

	//银联支付测试首页
    public function index(){
        $this->display();
    }

    public function consume(){
  //       $config = C('UNIONPAY_SDKCONFIG');
  //       $params = C('UNIONPAY');
  //       $params['certId']  = $this->getSignCertId();
  //       $params['frontUrl']  = $config['FRONT_NOTIFY_URL'];
  //       $params['backUrl']  = $config['BACK_NOTIFY_URL'];

  //       $params['orderId'] = I('orderId');
  //       $params['txnTime'] = date('YmdHis');
  //       $params['txnAmt']  = I('txnAmt');
			
		// $this->sign($params);
		// $uri = $config['FRONT_TRANS_URL'];
		// $html_form = $this->create_html ( $params, $uri );
        $this->display();
    }

    public function frontconsume(){
		// $params = array(
		// 		//以下信息非特殊情况不需要改动
		// 		'version' => '5.0.0',                 //版本号
		// 		'encoding' => 'utf-8',				  //编码方式
		// 		'certId' => getSignCertId (),	      //证书ID
		// 		'txnType' => '01',				      //交易类型
		// 		'txnSubType' => '01',				  //交易子类
		// 		'bizType' => '000201',				  //业务类型
		// 		'frontUrl' =>  SDK_FRONT_NOTIFY_URL,  //前台通知地址
		// 		'backUrl' => SDK_BACK_NOTIFY_URL,	  //后台通知地址
		// 		'signMethod' => '01',	              //签名方法
		// 		'channelType' => '08',	              //渠道类型，07-PC，08-手机
		// 		'accessType' => '0',		          //接入类型
		// 		'currencyCode' => '156',	          //交易币种，境内商户固定156
				
		// 		//TODO 以下信息需要填写
		// 		'merId' => $_POST["merId"],		//商户代码，请改自己的测试商户号，此处默认取demo演示页面传递的参数
		// 		'orderId' => $_POST["orderId"],	//商户订单号，8-32位数字字母，不能含“-”或“_”，此处默认取demo演示页面传递的参数，可以自行定制规则
		// 		'txnTime' => $_POST["txnTime"],	//订单发送时间，格式为YYYYMMDDhhmmss，取北京时间，此处默认取demo演示页面传递的参数
		// 		'txnAmt' => $_POST["txnAmt"],	//交易金额，单位分，此处默认取demo演示页面传递的参数
		// // 		'reqReserved' =>'透传信息',        //请求方保留域，透传字段，查询、通知、对账文件中均会原样出现，如有需要请启用并修改自己希望透传的数据

		// 		//TODO 其他特殊用法请查看 special_use_purchase.php
		// 	);

    	$orderapi=new OrderApi;
    	$orderid=I('orderId');
    	$res=$orderapi->shopOrderRequest($orderid,'web');
    	if($res['status']){
	        $config = C('UNIONPAY_SDKCONFIG');
	        $params = C('UNIONPAY');
	        $params['certId']  = $this->getSignCertId();
	        $params['frontUrl']  = $config['FRONT_NOTIFY_URL'];
	        $params['backUrl']  = $config['BACK_NOTIFY_URL'];

	        $params['orderId'] = $orderid;
	        $params['txnTime'] = date('YmdHis');
	        $params['txnAmt']  = $res['amount'];
			
			$this->sign($params);
			$uri = $config['FRONT_TRANS_URL'];
			$html_form = $this->create_html ( $params, $uri );
			echo $html_form;
    	}else{
    		$this->error($res['info']);
    	}
    }

    //从购物车直接支付
    public function appconsume($source='web',$token=0){
        $uid=0;
        if($source=='app'){
            $auth=session('user_auth');
            if($auth['token']!=$token){
                $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'身份验证错误')));
            }
            $uid=$auth['uid'];
        }else{
            $uid=is_login();
        }
        if(!$uid){
            if($source=='app'){
                $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>false,'error'=>'您还没有登陆')));
            }else{
                $this->error( '您还没有登陆',U('User/login'),false,5);
            }
        }
        if($source=='app'){
	        $cartid=I('cartlist');
	        $comments=I('comment');
	        $realamount=I('realamount');
	        $shipmethod=I('shippingmethod');
	        $selshipaddr=I('selshipaddr');
	        $shopid=I('shopid');
	        $coupon=I('coupon');

            if(stripos($cartid,',')){
                $cartid=explode(',', $cartid);
                $shipmethod=explode(',', $shipmethod);
            }else{
                $cartid=array(0=>$cartid);
                $shipmethod=array(0=>$shipmethod);
            }
            if(stripos($shopid,',')){
                $shopid=explode(',', $shopid);
                $coupon=explode(',', $coupon);
            }else{
                $shopid=array(0=>$shopid);
                $coupon=array(0=>$coupon);
            }
            $comments=json_decode($comments,true);

	        $shopcoupon=array();
	        foreach ($shopid as $key => $value) {
	            $shopcoupon[$value]=$coupon[$key];
	        }

	        $shipcart=array();
	        foreach ($cartid as $key => $value) {
	            $shipcart[$value]=$shipmethod[$key];
	        }

	        if(empty(I('cartlist')) || empty($cartid)){
	            if($source=='app'){
	                $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'没有需要结算的商品，请先选择需要需要结算的商品')));
	            }else{
	                $this->error('没有需要结算的商品，请先选择需要需要结算的商品');
	            }
	        }
	        if(empty(I('shippingmethod')) || empty($shipmethod)){
	            if($source=='app'){
	                $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'运输方式参数不正确')));
	            }else{
	                $this->error('运输方式参数不正确');
	            }
	        }
	        if(empty($shopcoupon)){
	            if($source=='app'){
	                $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'优惠券参数不正确')));
	            }else{
	                $this->error('优惠券参数不正确');
	            }
	        }
	        if(empty($selshipaddr)){
	            $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'收货地址错误')));
	        }else{
	            $memaddress=D('member_shippingaddr')->where(array('id'=>$selshipaddr))->find();
	            if($memaddress['provincename']==''||$memaddress['cityname']==''||$memaddress['districtname']==''||$memaddress['detailaddr']==''||$memaddress['receivename']==''||$memaddress['linkphone']==''){
	                $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'收货地址错误')));
	            }
	        }

	        $orderapi=new OrderApi;
	        //检查价格是否有变，如果有变，则重新确认
	        $rescheck=$orderapi->checkShopPrice($selcartid,$realamount);
	        if(!$rescheck['invent']['status']){
	            $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>$rescheck['invent']['info'])));
	        }else{
	            if(!$rescheck['cash']['status']){
	                $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>$rescheck['cash']['info'])));
	            }
	        }

	        $prodapi=new ProductApi;
	        $shipping=$prodapi->calcShippingAmount($shipcart,$selshipaddr);
	        $issettle=1;
	        $messhiperror='';
	        foreach ($shipping as $key => $value) {
	            if($value['isempty']=='1'){
	                $issettle=0;
	                $messhiperror=$value['error'];
	            }
	        }
	        if(!$issettle){
	            $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>$messhiperror)));
	            return false;
	        }

	        //生成订单，并支付
	        $res=$orderapi->newShopOrder($uid,$shipcart,$comments,$source,$shipping,$shopcoupon,$selshipaddr);
	        if($res['status']){
		        $config = C('UNIONPAY_SDKCONFIG');
		        $params = C('UNIONPAY');
		        $params['certId']  = $this->getSignCertId();
		        $params['frontUrl']  = $config['FRONT_NOTIFY_URL'];
		        $params['backUrl']  = $config['BACK_NOTIFY_URL'];
		        $params['channelType']='08';
		        $params['orderId'] = $res['orderid'];
		        $params['txnTime'] = date('YmdHis');
		        $params['txnAmt']  = $res['amount'];
					
				$this->sign($params);
				$url = $config['App_Request_Url'];

				$logapi=new LogApi;
				$logapi->LogInfo ("后台请求地址为>" . $url);
				$result = $this->post($params, $url, $errMsg);
				if (! $result) { //没收到200应答的情况
					$this->printResult ( $url, $params, "" );
					$reserrormsg= "POST请求失败：" . $errMsg;
					$this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>$reserrormsg)));
				}
				$logapi->LogInfo ( "后台返回结果为>" . $result );
				$result_arr = $this->convertStringToArray ( $result );

				$this->printResult ( $url, $params, $result ); //页面打印请求应答数据

				if ( !$this->verify ( $result_arr ) ){
					$reserrormsg= "应答报文验签失败<br>\n";
					$this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>$reserrormsg)));
				}

				$logapi->LogInfo ("应答报文验签成功<br>\n");
				if ($result_arr["respCode"] == "00"){
				    //成功
				    //TODO
					$tncode=$result_arr["tn"];
					$res=$orderapi->shopOrderUpdateTN($res['orderid'],$tncode);
					$logapi->LogInfo ("成功接收tn：" . $result_arr["tn"] . "<br>\n");
					if($res['status']){
						$this->ajaxReturn(array('success'=>true,'body'=>array('isLogined'=>true,'error'=>$res['info'],'istn'=>true,'tn'=>$tncode)));
					}else{
						$this->ajaxReturn(array('success'=>true,'body'=>array('isLogined'=>true,'error'=>$res['info'],'istn'=>false,'tn'=>'')));
					}
				} else {
				    //其他应答码做以失败处理
				     //TODO
					$reserrormsg= "失败：" . $result_arr["respMsg"] . "。<br>\n";
					$this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>$reserrormsg)));
				}
	        }else{
	            $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'生成订单失败')));
	        }
        }else{
        	$this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'非法渠道调用')));
        }
    }

    //未支付状态下进行支付
    public function appconsumepay($source='web',$token=0){
        $uid=0;
        if($source=='app'){
            $auth=session('user_auth');
            if($auth['token']!=$token){
                $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'身份验证错误')));
            }
            $uid=$auth['uid'];
        }else{
            $uid=is_login();
        }
        if(!$uid){
            if($source=='app'){
                $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>false,'error'=>'您还没有登陆')));
            }
        }
        if($source=='app'){
	    	$orderapi=new OrderApi;
	    	$orderid=I('orderId');
	    	$res=$orderapi->shopOrderRequest($orderid,$source);
	    	if($res['status']){
		        $config = C('UNIONPAY_SDKCONFIG');
		        $params = C('UNIONPAY');
		        $params['certId']  = $this->getSignCertId();
		        $params['frontUrl']  = $config['FRONT_NOTIFY_URL'];
		        $params['backUrl']  = $config['BACK_NOTIFY_URL'];
		        $params['channelType']='08';
		        $params['orderId'] = $orderid;
		        $params['txnTime'] = date('YmdHis');
		        $params['txnAmt']  = $res['amount'];
					
				$this->sign($params);
				$url = $config['App_Request_Url'];

				$logapi=new LogApi;
				$logapi->LogInfo ("后台请求地址为>" . $url);
				$result = $this->post($params, $url, $errMsg);
				if (! $result) { //没收到200应答的情况
					$this->printResult ( $url, $params, "" );
					$reserrormsg= "POST请求失败：" . $errMsg;
					$this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>$reserrormsg)));
				}
				$logapi->LogInfo ( "后台返回结果为>" . $result );
				$result_arr = $this->convertStringToArray ( $result );

				$this->printResult ( $url, $params, $result ); //页面打印请求应答数据

				if ( !$this->verify ( $result_arr ) ){
					$reserrormsg= "应答报文验签失败<br>\n";
					$this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>$reserrormsg)));
				}

				$logapi->LogInfo ("应答报文验签成功<br>\n");
				if ($result_arr["respCode"] == "00"){
				    //成功
				    //TODO
				    $tncode=$result_arr["tn"];
					$res=$orderapi->shopOrderUpdateTN($orderid,$tncode);
					$logapi->LogInfo ("成功接收tn：" . $result_arr["tn"] . "<br>\n");
					if($res['status']){
						$this->ajaxReturn(array('success'=>true,'body'=>array('isLogined'=>true,'error'=>$res['info'],'istn'=>true,'tn'=>$tncode)));
					}else{
						$this->ajaxReturn(array('success'=>true,'body'=>array('isLogined'=>true,'error'=>$res['info'],'istn'=>false,'tn'=>'')));
					}
				} else {
				    //其他应答码做以失败处理
				     //TODO
					$reserrormsg= "失败：" . $result_arr["respMsg"] . "。<br>\n";
					$this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>$reserrormsg)));
				}
	        }else{
	            $this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>$res['info'])));
	        }
        }else{
        	$this->ajaxReturn(array('success'=>false,'body'=>array('isLogined'=>true,'error'=>'非法渠道调用')));
        }
    }

    function usernotifyinfo(){
		$unpresp=$_POST;
		
		if (isset ( $_POST ['signature'] )) {
			if($this->verify($_POST)){
				$issignstatus='验签成功';
			}else{
				$issignstatus='验签失败';
			}
		} else {
			$issignstatus='签名为空';
		}
		$this->assign('unpresp',$unpresp);
		$this->assign('issignstatus',$issignstatus);
		$this->display();
    }

    function usernotify(){// 付款后返回商家
		$unpresp=$_POST;
		
		if (isset ( $_POST ['signature'] )) {
			if($this->verify($_POST)){
				$issignstatus='验签成功';
			}else{
				$issignstatus='验签失败';
			}
		} else {
			$issignstatus='签名为空';
		}

		if($unpresp['respCode']=='00'){
			$payedinfo['success']='1';
			$data['thdfrontpaid']=1;
		}else{
			$payedinfo['success']='0';
			$data['thdfrontpaid']=0;
		}
		$data['orderid']=$unpresp['orderId'];
		$data['thdparty']='unionpay';
    	$data['thdseq']=$unpresp['queryId'];
    	$data['thdmsg']=$unpresp['respMsg'];
    	$data['time']=$unpresp['txnTime'];
    	$orderapi=new OrderApi;
    	$orderapi->shopOrderFrontPay($data);

		$payedinfo['orderid']=$unpresp['orderId'];
		$payedinfo['payqueryid']=$unpresp['queryId'];
		$payedinfo['amount']=intval($unpresp['txnAmt'])/100;
		$paytime=$unpresp['txnTime'];
		$payedinfo['paytime']=substr($paytime,0,4).'-'.substr($paytime,4,2).'-'.substr($paytime,6,2).' '.substr($paytime,8,2).':'.substr($paytime,10,2).':'.substr($paytime,12,2);
		$payedinfo['errorinfo']=$unpresp['respMsg'];

		$this->assign('payedinfo',$payedinfo);
		$this->assign('issignstatus',$issignstatus);
		$this->assign('pagename','page-usernotify');
		$this->display();
    }

    function notify(){
		//$orderId = $_POST ['orderId']; //其他字段也可用类似方式获取
		//$respCode = $_POST ['respCode']; //判断respCode=00或A6即可认为交易成功
		//如果卡号我们业务配了会返回且配了需要加密的话，请按此方法解密
   		//if(array_key_exists ("accNo", $_POST)){
			//	$accNo = decryptData($_POST["accNo"]);
			//}
    	$logapi=new LogApi;
    	$logapi->LogInfo (json_encode($_POST));

    	/*付款后业务逻辑代码  */
    	$respCode = $_POST ['respCode'];
    	if($respCode=='00'){
        	$data['thdpaid']=1;
    	}else{
    		$data['thdpaid']=0;
    	}
    	$data['orderid']=$_POST['orderId'];
    	$data['thdparty']='unionpay';
    	$data['thdseq']=$_POST['queryId'];
    	$data['thdmsg']=$_POST['respMsg'];
    	$data['amount']=$_POST['txnAmt'];
    	$data['time']=$_POST['txnTime'];
    	$orderapi=new OrderApi;
    	$orderapi->shopOrderPay($data);
    }

    public function ajaxConsumeQuery(){
    	$orderid=I('ordid');
    	$res=$this->consumeQueryApi($orderid);
    	$this->ajaxReturn($res);
    }

    public function consumeQueryApi($orderid){
    	$orderapi=new OrderApi;
    	$order=$orderapi->getOrderSettle($orderid);
    	if($order){
	    	$logapi=new LogApi;
			$logapi->LogInfo ( "===========处理后台请求开始============" );

	        $config = C('UNIONPAY_SDKCONFIG');
	        $params = C('UNIONPAY');
	        unset($params['currencyCode']);
	        $params['certId']  = $this->getSignCertId();
	        $params['txnType']  = '00';
	        $params['txnSubType']  = '00';
	        $params['bizType']  = '000000';

	        $params['orderId'] = $orderid;
	        $params['txnTime'] = $order['thirdtime'];
	        $params['queryId'] = $order['thirdseq'];
			
			$this->sign($params);
			$url = $config['SINGLE_QUERY_URL'];
			$logapi->LogInfo ( "后台请求地址为>" . $url );
			$result = $this->post( $params, $url, $errMsg );
			if (! $result) { //没收到200应答的情况
				$this->printResult( $url, $params, "" );
				return array('reqstatus'=>0,'paystatus'=>0,'info'=>'POST请求失败：' . $errMsg);
			}
			$logapi->LogInfo ( "后台返回结果为>" . $result );
			$result_arr = $this->convertStringToArray($result);

			$this->printResult( $url, $params, $result ); //页面打印请求应答数据

			if (!$this->verify($result_arr)){
				$logapi->LogInfo ( "===========处理后台请求结束============" );
			    return array('reqstatus'=>0,'paystatus'=>0,'info'=>'应答报文验签失败');
			}

			$logapi->LogInfo ( "===========处理后台请求结束============" );

			if ($result_arr["respCode"] == "00"){
			  if ($result_arr["origRespCode"] == "00"){
				    //交易成功
				    //TODO
				    return array('reqstatus'=>1,'paystatus'=>1,'info'=>'交易成功。');
				} else if ($result_arr["origRespCode"] == "03"
				 	    || $result_arr["origRespCode"] == "04"
				 	    || $result_arr["origRespCode"] == "05" ){
				    //后续需发起交易状态查询交易确定交易状态
				    //TODO
				     return array('reqstatus'=>2,'paystatus'=>0,'info'=>'交易处理中，请稍后再查询。');
				} else {
				    //其他应答码做以失败处理
				     //TODO
				     echo "交易失败：" . $result_arr["origRespMsg"] . "。";
				     return array('reqstatus'=>1,'paystatus'=>2,'info'=>'交易失败：' . $result_arr["origRespMsg"]);
				}
			} else if ($result_arr["respCode"] == "03"
			 	    || $result_arr["respCode"] == "04"
			 	    || $result_arr["respCode"] == "05" ){
			    //后续需发起交易状态查询交易确定交易状态
			    //TODO
			     return array('reqstatus'=>2,'paystatus'=>0,'info'=>'交易处理中，请稍后再查询。');
			} else {
			    //其他应答码做以失败处理
			     //TODO
			     return array('reqstatus'=>0,'paystatus'=>0,'info'=>'失败：' . $result_arr["respMsg"]);
			}
    	}else{
    		return array('reqstatus'=>0,'paystatus'=>0,'info'=>'订单不存在');
    	}
    }
	/**
	 * 签名
	 *
	 * @param String $params_str
	 */
	function sign(&$params) {
		$logapi=new LogApi;
		$logapi->LogInfo ( '=====签名报文开始======' );
		if(isset($params['signature'])){
			unset($params['signature']);
		}
		// 转换成key=val&串
		$params_str = $this->createLinkString ( $params, true, false );
		$logapi->LogInfo ( "签名key=val&...串 >" . $params_str );
		
		$params_sha1x16 = sha1 ( $params_str, FALSE );
		$logapi->LogInfo ( "摘要sha1x16 >" . $params_sha1x16 );
		
		// 签名证书路径
		$cert_path = C('UNIONPAY_SDKCONFIG.SIGN_CERT_PATH');
			
		$private_key = $this->getPrivateKey ( $cert_path );
		// 签名
		$sign_falg = openssl_sign( $params_sha1x16, $signature, $private_key, OPENSSL_ALGO_SHA1);
		if ($sign_falg) {
			$signature_base64 = base64_encode ( $signature );
			$logapi->LogInfo ( "签名串为 >" . $signature_base64 );
			$params ['signature'] = $signature_base64;
		} else {
			$logapi->LogInfo ( ">>>>>签名失败<<<<<<<" );
		}
		$logapi->LogInfo ( '=====签名报文结束======' );
	}

	/**
	 * 验签
	 *
	 * @param String $params_str        	
	 * @param String $signature_str        	
	 */
	function verify($params) {
		$logapi=new LogApi;
		// 公钥
		$public_key = $this->getPulbicKeyByCertId ( $params ['certId'] );	
		//	echo $public_key.'<br/>';
		// 签名串
		$signature_str = $params ['signature'];
		unset ( $params ['signature'] );
		$params_str = $this->createLinkString ( $params, true, false );
		$logapi->LogInfo ( '报文去[signature] key=val&串>' . $params_str );
		$signature = base64_decode ( $signature_str );
		//	echo date('Y-m-d',time());
		$params_sha1x16 = sha1 ( $params_str, FALSE );
		$logapi->LogInfo ( '摘要shax16>' . $params_sha1x16 );	
		$isSuccess = openssl_verify ( $params_sha1x16, $signature,$public_key, OPENSSL_ALGO_SHA1 );
		$logapi->LogInfo ( $isSuccess ? '验签成功' : '验签失败' );
		return $isSuccess;
	}

	/**
	 * 根据证书ID 加载 证书
	 *
	 * @param unknown_type $certId        	
	 * @return string NULL
	 */
	function getPulbicKeyByCertId($certId) {
		$logapi=new LogApi;
		$logapi->LogInfo ( '报文返回的证书ID>' . $certId );
		// 证书目录
		$cert_dir = C('UNIONPAY_SDKCONFIG.VERIFY_CERT_DIR');
		$logapi->LogInfo ( '验证签名证书目录 :>' . $cert_dir );
		$handle = opendir ( $cert_dir );
		if ($handle) {
			while ( $file = readdir ( $handle ) ) {
				clearstatcache ();
				$filePath = $cert_dir . '/' . $file;
				if (is_file ( $filePath )) {
					if (pathinfo ( $file, PATHINFO_EXTENSION ) == 'cer') {
						if ($this->getCertIdByCerPath ( $filePath ) == $certId) {
							closedir ( $handle );
							$logapi->LogInfo ( '加载验签证书成功' );
							return $this->getPublicKey ( $filePath );
						}
					}
				}
			}
			$logapi->LogInfo ( '没有找到证书ID为[' . $certId . ']的证书' );
		} else {
			$logapi->LogInfo ( '证书目录 ' . $cert_dir . '不正确' );
		}
		closedir ( $handle );
		return null;
	}

	/**
	 * 取证书ID(.pfx)
	 *
	 * @return unknown
	 */
	function getCertId($cert_path) {
		$pkcs12certdata = file_get_contents ( $cert_path );
		openssl_pkcs12_read ( $pkcs12certdata, $certs, C('UNIONPAY_SDKCONFIG.SIGN_CERT_PWD'));
		$x509data = $certs ['cert'];
		openssl_x509_read ( $x509data );
		$certdata = openssl_x509_parse ( $x509data );
		$cert_id = $certdata ['serialNumber'];
		return $cert_id;
	}

	/**
	 * 取证书ID(.cer)
	 *
	 * @param unknown_type $cert_path        	
	 */
	function getCertIdByCerPath($cert_path) {
		$x509data = file_get_contents ( $cert_path );
		openssl_x509_read ( $x509data );
		$certdata = openssl_x509_parse ( $x509data );
		$cert_id = $certdata ['serialNumber'];
		return $cert_id;
	}

	/**
	 * 签名证书ID
	 *
	 * @return unknown
	 */
	function getSignCertId() {
		// 签名证书路径
		return $this->getCertId (C('UNIONPAY_SDKCONFIG.SIGN_CERT_PATH'));
	}
	function getEncryptCertId() {
		// 签名证书路径
		return $this->getCertIdByCerPath (C('UNIONPAY_SDKCONFIG.ENCRYPT_CERT_PATH'));
	}

	/**
	 * 取证书公钥 -验签
	 *
	 * @return string
	 */
	function getPublicKey($cert_path) {
		return file_get_contents ( $cert_path );
	}
	/**
	 * 返回(签名)证书私钥 -
	 *
	 * @return unknown
	 */
	function getPrivateKey($cert_path) {
		$pkcs12 = file_get_contents ( $cert_path );
		openssl_pkcs12_read ( $pkcs12, $certs, C('UNIONPAY_SDKCONFIG.SIGN_CERT_PWD') );
		return $certs ['pkey'];
	}

	/**
	 * 加密数据
	 * @param string $data数据
	 * @param string $cert_path 证书配置路径
	 * @return unknown
	 */
	function encryptData($data, $cert_path='') {
		if(empty($cert_path)){
			$cert_path=C('UNIONPAY_SDKCONFIG.ENCRYPT_CERT_PATH');
		}
		$public_key = $this->getPublicKey ( $cert_path );
		openssl_public_encrypt ( $data, $crypted, $public_key );
		return base64_encode ( $crypted );
	}


	/**
	 * 解密数据
	 * @param string $data数据
	 * @param string $cert_path 证书配置路径
	 * @return unknown
	 */
	function decryptData($data, $cert_path='') {
		if(empty($cert_path)){
			$cert_path=C('UNIONPAY_SDKCONFIG.SIGN_CERT_PATH');
		}
		$data = base64_decode ( $data );
		$private_key = $this->getPrivateKey ( $cert_path );
		openssl_private_decrypt ( $data, $crypted, $private_key );
		return $crypted;
	}

	/**
	 * Author: gu_yongkang
	 * data: 20110510
	 * 密码转PIN
	 * Enter description here ...
	 * @param $spin
	 */
	function  Pin2PinBlock( &$sPin )
	{
		//	$sPin = "123456";
		$iTemp = 1;
		$sPinLen = strlen($sPin);
		$sBuf = array();
		//密码域大于10位
		$sBuf[0]=intval($sPinLen, 10);

		if($sPinLen % 2 ==0)
		{
			for ($i=0; $i<$sPinLen;)
			{
				$tBuf = substr($sPin, $i, 2);
				$sBuf[$iTemp] = intval($tBuf, 16);
				unset($tBuf);
				if ($i == ($sPinLen - 2))
				{
					if ($iTemp < 7)
					{
						$t = 0;
						for ($t=($iTemp+1); $t<8; $t++)
						{
							$sBuf[$t] = 0xff;
						}
					}
				}
				$iTemp++;
				$i = $i + 2;	//linshi
			}
		}else{
			for ($i=0; $i<$sPinLen;)
			{
				if ($i ==($sPinLen-1))
				{
					$mBuf = substr($sPin, $i, 1) . "f";
					$sBuf[$iTemp] = intval($mBuf, 16);
					unset($mBuf);
					if (($iTemp)<7){
						$t = 0;
						for ($t=($iTemp+1); $t<8; $t++)
						{
							$sBuf[$t] = 0xff;
						}
					}
				}
				else
				{
					$tBuf = substr($sPin, $i, 2);
					$sBuf[$iTemp] = intval($tBuf, 16);
					unset($tBuf);
				}
				$iTemp++;
				$i = $i + 2;
			}
		}
		return $sBuf;
	}
	/**
	 * Author: gu_yongkang
	 * data: 20110510
	 * Enter description here ...
	 * @param $sPan
	 */
	 function FormatPan(&$sPan)
	 {
		$iPanLen = strlen($sPan);
		$iTemp = $iPanLen - 13;
		$sBuf = array();
		$sBuf[0] = 0x00;
		$sBuf[1] = 0x00;
		for ($i=2; $i<8; $i++)
		{
			$tBuf = substr($sPan, $iTemp, 2);
			$sBuf[$i] = intval($tBuf, 16);
			$iTemp = $iTemp + 2;
		}
		return $sBuf;
	}

	function Pin2PinBlockWithCardNO(&$sPin, &$sCardNO)
	{
		$sPinBuf = $this->Pin2PinBlock($sPin);
		$iCardLen = strlen($sCardNO);
		//		$log->LogInfo("CardNO length : " . $iCardLen);
		if ($iCardLen <= 10)
		{
			return (1);
		}
		elseif ($iCardLen==11){
			$sCardNO = "00" . $sCardNO;
		}
		elseif ($iCardLen==12){
			$sCardNO = "0" . $sCardNO;
		}
		$sPanBuf = $this->FormatPan($sCardNO);
		$sBuf = array();

		for ($i=0; $i<8; $i++)
		{
		//			$sBuf[$i] = $sPinBuf[$i] ^ $sPanBuf[$i];	//十进制
			//			$sBuf[$i] = vsprintf("%02X", ($sPinBuf[$i] ^ $sPanBuf[$i]));
			$sBuf[$i] = vsprintf("%c", ($sPinBuf[$i] ^ $sPanBuf[$i]));
		}
		unset($sPinBuf);
		unset($sPanBuf);
		//		return $sBuf;
		$sOutput = implode("", $sBuf);	//数组转换为字符串
		return $sOutput;
	}

	/**
	 * 讲数组转换为string
	 *
	 * @param $para 数组        	
	 * @param $sort 是否需要排序        	
	 * @param $encode 是否需要URL编码        	
	 * @return string
	 */
	function createLinkString($para, $sort, $encode) {
		
		if($para == NULL || !is_array($para))
			return "";
		
		$linkString = "";
		if ($sort) {
			$para = $this->argSort( $para );
		}
		while ( list ( $key, $value ) = each ( $para ) ) {
			if ($encode) {
				$value = urlencode ( $value );
			}
			$linkString .= $key . "=" . $value . "&";
		}
		// 去掉最后一个&字符
		$linkString = substr ( $linkString, 0, count ( $linkString ) - 2 );
		
		return $linkString;
	}

	/**
	 * 对数组排序
	 *
	 * @param $para 排序前的数组
	 *        	return 排序后的数组
	 */
	function argSort($para) {
		ksort ( $para );
		reset ( $para );
		return $para;
	}

	/**
	 * 构造自动提交表单
	 *
	 * @param unknown_type $params        	
	 * @param unknown_type $action        	
	 * @return string
	 */
	function create_html($params, $action) {
		// <body onload="javascript:document.pay_form.submit();">
		$encodeType = isset ( $params ['encoding'] ) ? $params ['encoding'] : 'UTF-8';
		$html = <<<eot
		<html>
		<head>
		    <meta http-equiv="Content-Type" content="text/html; charset={$encodeType}" />
		</head>
		<body onload="javascript:document.pay_form.submit();">
		    <form id="pay_form" name="pay_form" action="{$action}" method="post">
			
eot;
		foreach ( $params as $key => $value ) {
			$html .= "    <input type=\"hidden\" name=\"{$key}\" id=\"{$key}\" value=\"{$value}\" />\n";
		}
		$html .= <<<eot
	   <!-- <input type="submit" type="hidden">-->
	    </form>
	</body>
	</html>
eot;
		return $html;
	}

	/**
	 * 字符串转换为 数组
	 *
	 * @param unknown_type $str        	
	 * @return multitype:unknown
	 */
	function convertStringToArray($str) {
		return $this->parseQString($str);
	}

	/**
	 * key1=value1&key2=value2转array
	 * @param $str key1=value1&key2=value2的字符串
	 * @param $$needUrlDecode 是否需要解url编码，默认不需要
	 */
	function parseQString($str, $needUrlDecode=false){
		$result = array();
		$len = strlen($str);
		$temp = "";
		$curChar = "";
		$key = "";
		$isKey = true;
		$isOpen = false;
		$openName = "\0";
		
		for($i=0; $i<$len; $i++){
			$curChar = $str[$i];
			if($isOpen){
				if( $curChar == $openName){
					$isOpen = false;
				}
				$temp = $temp . $curChar;
			} elseif ($curChar == "{"){
				$isOpen = true;
				$openName = "}";
				$temp = $temp . $curChar;
			} elseif ($curChar == "["){
				$isOpen = true;
				$openName = "]";
				$temp = $temp . $curChar;
			} elseif ($isKey && $curChar == "="){
				$key = $temp;
				$temp = "";
				$isKey = false;
			} elseif ( $curChar == "&" && !$isOpen){
				$this->putKeyValueToDictionary($temp, $isKey, $key, $result, $needUrlDecode);
				$temp = "";
				$isKey = true;
			} else {
				$temp = $temp . $curChar;
			}	
		}
		$this->putKeyValueToDictionary($temp, $isKey, $key, $result, $needUrlDecode);
		return $result;		
	}

	function putKeyValueToDictionary($temp, $isKey, $key, &$result, $needUrlDecode) {
		if ($isKey) {
			$key = $temp;
			if (strlen ( $key ) == 0) {
				return false;
			}
			$result [$key] = "";
		} else {
			if (strlen ( $key ) == 0) {
				return false;
			}
			if ($needUrlDecode)
				$result [$key] = urldecode ( $temp );
			else
				$result [$key] = $temp;
		}
	}

	/**
	 * 压缩文件 对应java deflate
	 *
	 * @param unknown_type $params        	
	 */
	function deflate_file(&$params) {
		$logapi=new LogApi;
		foreach ( $_FILES as $file ) {
			$logapi->LogInfo ( "---------处理文件---------" );
			if (file_exists ( $file ['tmp_name'] )) {
				$params ['fileName'] = $file ['name'];
				
				$file_content = file_get_contents ( $file ['tmp_name'] );
				$file_content_deflate = gzcompress ( $file_content );
				
				$params ['fileContent'] = base64_encode ( $file_content_deflate );
				$logapi->LogInfo ( "压缩后文件内容为>" . base64_encode ( $file_content_deflate ) );
			} else {
				$logapi->LogInfo ( ">>>>文件上传失败<<<<<" );
			}
		}
	}

	/**
	 * 处理报文中的文件
	 *
	 * @param unknown_type $params        	
	 */
	function deal_file($params) {
		$logapi=new LogApi;
		if (isset ( $params ['fileContent'] )) {
			$logapi->LogInfo ( "---------处理后台报文返回的文件---------" );
			$fileContent = $params ['fileContent'];
			
			if (empty ( $fileContent )) {
				$logapi->LogInfo ( '文件内容为空' );
				return false;
			} else {
				// 文件内容 解压缩
				$content = gzuncompress ( base64_decode ( $fileContent ) );
				$root = C('UNIONPAY_SDKCONFIG.FILE_DOWN_PATH');
				$filePath = null;
				if (empty ( $params ['fileName'] )) {
					$logapi->LogInfo ( "文件名为空" );
					$filePath = $root . $params ['merId'] . '_' . $params ['batchNo'] . '_' . $params ['txnTime'] . '.txt';
				} else {
					$filePath = $root . $params ['fileName'];
				}
				$handle = fopen ( $filePath, "w+" );
				if (! is_writable ( $filePath )) {
					$logapi->LogInfo ( "文件:" . $filePath . "不可写，请检查！" );
					return false;
				} else {
					file_put_contents ( $filePath, $content );
					$logapi->LogInfo ( "文件位置 >:" . $filePath );
				}
				fclose ( $handle );
			}
			return true;
		} else {
			return false;
		}
	}

	/**
	 * map转换string
	 *
	 * @param
	 *        	$customerInfo
	 */
	function getCustomerInfoStr($customerInfo) {
		return base64_encode ( "{" . $this->createLinkString ( $customerInfo, false, false ) . "}" );
	}

	/**
	 * map转换string，按新规范加密
	 *
	 * @param
	 *        	$customerInfo
	 */
	function getCustomerInfoStrNew($customerInfo) {
		$encryptedInfo = array();
		foreach ( $customerInfo as $key => $value ) {
			if ($key == 'phoneNo' || $key == 'cvn2' || $key == 'expired' ) {
			//if ($key == 'phoneNo' || $key == 'cvn2' || $key == 'expired' || $key == 'certifTp' || $key == 'certifId') {
				$encryptedInfo [$key] = $customerInfo [$key];
				unset ( $customerInfo [$key] );
			}
		}
		if(count($encryptedInfo) != 0){
			$encryptedInfo = $this->createLinkString ( $encryptedInfo, false, false );
			$encryptedInfo = $this->encryptData ( $encryptedInfo, C('UNIONPAY_SDKCONFIG.ENCRYPT_CERT_PATH') );
			$customerInfo ['encryptedInfo'] = $encryptedInfo;
		}
		return base64_encode ( "{" . $this->createLinkString ( $customerInfo, false, false ) . "}" );
	}

	/**
	 * 后台交易 HttpClient通信
	 *
	 * @param unknown_type $params        	
	 * @param unknown_type $url        	
	 * @return mixed
	 */
	function post($params, $url, &$errmsg) {
		$opts = $this->createLinkString ( $params, false, true );
		$ch = curl_init ();
		curl_setopt ( $ch, CURLOPT_URL, $url );
		curl_setopt ( $ch, CURLOPT_POST, 1 );
		curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, false ); // 不验证证书
		curl_setopt ( $ch, CURLOPT_SSL_VERIFYHOST, false ); // 不验证HOST
		curl_setopt ( $ch, CURLOPT_SSLVERSION, 1 ); // http://php.net/manual/en/function.curl-setopt.php页面搜CURL_SSLVERSION_TLSv1
		curl_setopt ( $ch, CURLOPT_HTTPHEADER, array (
				'Content-type:application/x-www-form-urlencoded;charset=UTF-8' 
		) );
		curl_setopt ( $ch, CURLOPT_POSTFIELDS, $opts );
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
		$html = curl_exec ( $ch );
		if(curl_errno($ch)){
			$errmsg = curl_error($ch);
			curl_close ( $ch );
			return false;
		}
	    if( curl_getinfo($ch, CURLINFO_HTTP_CODE) != "200"){
			$errmsg = "http状态=" . curl_getinfo($ch, CURLINFO_HTTP_CODE);
			curl_close ( $ch );
			return false;
	    }
		curl_close ( $ch );
		return $html;
	}

	/**
	 * 打印请求应答
	 *
	 * @param
	 *        	$url
	 * @param
	 *        	$req
	 * @param
	 *        	$resp
	 */
	function printResult($url, $req, $resp) {
		$logapi=new LogApi;
		$logapi->LogInfo ("=============");
		$logapi->LogInfo ("地址：" . $url);
		$logapi->LogInfo ("请求：" . htmlentities ( $this->createLinkString ( $req, false, true ) ) );
		$logapi->LogInfo ("应答：" . htmlentities ( $resp ) );
		$logapi->LogInfo ("=============");
	}

	/**
	 * 解析customerInfo。
	 * 为方便处理，encryptedInfo下面的信息也均转换为customerInfo子域一样方式处理，
	 * @param unknown $customerInfostr       	
	 * @return array形式ParseCustomerInfo
	 */
	function ParseCustomerInfo($customerInfostr) {
		$customerInfostr = base64_decode($customerInfostr);
		$customerInfostr = substr($customerInfostr, 1, strlen($customerInfostr) - 2);
		$customerInfo = $this->parseQString($customerInfostr);
		if(array_key_exists("encryptedInfo", $customerInfo)) {
			$encryptedInfoStr = $customerInfo["encryptedInfo"];
			unset ( $customerInfo ["encryptedInfo"] );
			$encryptedInfoStr = $this->decryptData($encryptedInfoStr);
			$encryptedInfo = $this->parseQString($encryptedInfoStr);
			foreach ($encryptedInfo as $key => $value){
				$customerInfo[$key] = $value;
			}
		}
		return $customerInfo;
	}
}