$(function () {
	var numbertext=$('input#mobile');
	var timer;
	var send_time = 2;
	var maxtime = send_time * 60-(1442806634-parseInt());
	var click_release_get_code_time = 0;
	$('#btcode').val('获取验证码');
	if(maxtime<=send_time * 60){
		timer = setInterval(countDown ,1000);
	}
	var countDown = function () {
		if (maxtime >= 1) {
			$('#btcode').attr('disabled', 'disabled').val(maxtime + '秒后重新获取').addClass("getwait");
			--maxtime;
		} else {
			clearInterval(timer);
			var htm_str = "重获验证码";
			if($('#btcode').val() == '获取验证码'){
				htm_str = "获取验证码";
			}
			$('#btcode').removeAttr('disabled').val(htm_str).removeClass('getwait');
		}
	};
	$('input#btcode').on('mousedown',function () {
		var number=numbertext.val();
		if(number.length==0)
		{
			alert('请输入手机号码！');
			numbertext.focus();
			return false;
		}    
		if(number.length!=11)
		{
			alert('请输入有效的手机号码！');
			numbertext.focus();
			return false;
		}

		var myreg = /^(((13[0-9]{1})|145|147|(15[0-9]{1})|170|176|177|178|(18[0-9]{1}))+\d{8})$/;
		if(!myreg.test(number))
		{
			alert('请输入有效的手机号码！');
			numbertext.focus();
			return false;
		}

		var verifycode=$('#verify').val();
		if(verifycode.length==0){
			alert('请输入图形验证码！');
			$('#verify').parent().parent().removeClass("am-form-success");
			$('#verify').parent().parent().addClass("am-form-error");
			$('#verify').addClass("am-field-error am-active");
			$('#verifycode').focus();
			return false;
		}else{
			$.post("checkverify",{code:verifycode},function(data){
				if(data.status=="0"){
					alert('图形验证码输入错误！');
					$('#verify').parent().parent().removeClass("am-form-success");
					$('#verify').parent().parent().addClass("am-form-error");
					$('#verify').removeClass("am-field-valid");
					$('#verify').addClass("am-field-error");
					$('#verifycode').focus();
					return false;
				}else{
					$('#verify').parent().parent().removeClass("am-form-error");
					$('#verify').parent().parent().addClass("am-form-success");
					$('#verify').removeClass("am-field-error");
					$('#verify').addClass("am-field-valid");

		            $.post("sms_sendsuer_verifycode",{mobile:number},
		            function(data){
		                if(!data.success){
		                	alert(data.info);
		                }else{
		                	$('#mobile').attr('readonly','readonly');
		                	$('#mobile').attr('disabled','disabled');
							maxtime = send_time * 60;
							timer = setInterval(countDown, 1000);
							if($("#maxtime_mobile").length>0){
								$("#maxtime_mobile").val(maxtime);
								maxtime_mobile = maxtime;
								timer_tmp = setInterval(countDown_tmp, 1000);
							}
		                }
		            },'json');
				}
			});
		}
	});
});