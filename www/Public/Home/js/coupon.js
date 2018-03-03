function opencouponnew(){
	$('#make-coupon').modal('open');
};

function addcoupon(){
	var spid=$('#shopid').val();
	var cuvalue=$('#couponvalue').val();
	var cucount=$('#couponcount').val();
	var bdate=$('#begindate').val();
	var edate=$('#enddate').val();
	var tid=$("input[name='tplid']:checked").val();
	$.post('makercoupon',{shopid:spid,couponvalue:cuvalue,couponcount:cucount,begindate:bdate,enddate:edate,tplid:tid},
		function(data){
            if(data.success){
                location.reload();
            }else{
            	alert(data.body.error);
            }
        },'json');
};

function searchfans(){
	$("#allcheck").prop("checked",false);
	var searchname=$.trim($('#searchname').val());
	if(searchname!=''){
		$('.attention-mine-guoup-check').each(function(){
			var fansname=$(this).children('span').html();
			if(fansname.indexOf(searchname)>-1){
				$(this).show();
			}else{
				$(this).hide();
			}
			var checktmp=$(this).children('input');
			if(checktmp.is(':checked')){
				checktmp.prop("checked",false);
			}
		});
	}else{
		$('.attention-mine-guoup-check').each(function(){
			$(this).show();
			var checktmp=$(this).children('input');
			if(checktmp.is(':checked')){
				checktmp.prop("checked",false);
			}
		});
	}
};

$(function(){
    $("#allcheck").click(function(){
		$('.attention-mine-guoup-check').each(function(){
			var checktmp=$(this).children('input');
			if(checktmp.is(':checked')){
				checktmp.prop("checked",false);
			}
		});
	    if($(this).is(':checked')){
			$('.attention-mine-guoup-check').each(function(){
				var checktmp=$(this).children('input');
				if($(this).css('display')=='none'){
					if(checktmp.is(':checked')){
						checktmp.prop("checked",false);
					}
				}else{
					if(!checktmp.is(':checked')){
						checktmp.prop("checked",true);
					}
				}
			});
	    }else{
			$('.attention-mine-guoup-check').each(function(){
				var checktmp=$(this).children('input');
				if($(this).css('display')=='none'){
					if(checktmp.is(':checked')){
						checktmp.prop("checked",false);
					}
				}else{
					if(checktmp.is(':checked')){
						checktmp.prop("checked",false);
					}
				}
			});
	    }
	});
});