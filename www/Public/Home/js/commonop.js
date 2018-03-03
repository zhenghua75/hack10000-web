function quantityminus(cartid,type){
    event.preventDefault();
	$('#waitingfor').modal('open');
    var url="";
    if(type==1){
        url="makercartop";
    }else{
        url="mycartop";
    }
    $.post(url,{op:"minus",ctid:cartid},
    function(data){
        $('#waitingfor').modal('close');
        if(data.status){
            $('#price'+cartid).html('金额：￥'+data.amount);
            $('#rowamount'+cartid).val(data.amount);
            $('#quantity'+cartid).val(data.quantity);
            if(data.quantity==1){
                $('#minus'+cartid).attr('disabled',true);
            }else if(data.quantity==0){
                $('#wrapprod'+cartid).remove();
            }
        }else{
            alert('修改失败:'+data.info);
        }
        calcntamount();
    },'json');
}

function quantityadd(cartid,type){
    event.preventDefault();
	$('#waitingfor').modal('open');
    var url="";
    if(type==1){
        url="makercartop";
    }else{
        url="mycartop";
    }
    $.post(url,{op:"add",ctid:cartid},
    function(data){
        $('#waitingfor').modal('close');
        if(data.status){
            $('#price'+cartid).html('金额：￥'+data.amount);
            $('#rowamount'+cartid).val(data.amount);
            $('#quantity'+cartid).val(data.quantity);
            if(data.quantity>1){
                $('#minus'+cartid).attr('disabled',false);
            }
        }else{
            alert('修改失败:'+data.info);
        }
        calcntamount();
    },'json');
}

function cartdel(cartid,type){
    event.preventDefault();
	$('#waitingfor').modal('open');
    var url="";
    if(type==1){
        url="makercartop";
    }else{
        url="mycartop";
    }
    $.post(url,{op:"del",ctid:cartid},
    function(data){
        $('#waitingfor').modal('close');
        if(data.status){
            $('#wrapprod'+cartid).remove();
        }else{
            alert('删除失败:'+data.info);
        }
        calcntamount();
    },'json');
}

function calcntamount(){
    var cnt=0;
    var amount=0;
    $('.tab1-goods input[type="checkbox"]').each(function(){
        if($(this).is(':checked')){
            cnt=cnt+1;
            amount=amount+parseInt($('#rowamount'+$(this).val()).val());
        }
    });
    var comment="您选择了"+cnt+"款商品，总价格：￥"+amount+"（不含运输费用）";
    var commentamount="￥"+amount;
    $('.tab1-goods-total-prices1').html(comment);
    $('.tab1-goods-total-prices2').html(commentamount);
}

$(function(){
    $("#allcheck").click(function(){
        if($(this).is(':checked')){
            $('.tab1-goods input[type="checkbox"]').each(function(){
                if(!$(this).attr('disabled')){
                    if(!$(this).is(':checked')){
                        $(this).prop("checked",true);
                    }
                }
            });
        }else{
            $('.tab1-goods input[type="checkbox"]').each(function(){
                if($(this).is(':checked')){
                    $(this).prop("checked",false);
                }
            });
        }
        calcntamount();
    });

    $('#formcart').submit(function(){
        var selcnt=0;
        $('.tab1-goods input[type="checkbox"]').each(function(){
            if($(this).is(':checked')){
                selcnt=selcnt+1;
            }
        });
        if(selcnt==0){
            alert('请选择要结算的商品');
            event.preventDefault();
            return false;
        }
    });
});
