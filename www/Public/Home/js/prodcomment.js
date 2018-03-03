function opencomment(orderdetid,prodid,orderid){
    var img=$('#img'+prodid).attr('src');
    var name=$('#name'+prodid).html();
    var spec=$('#spec'+prodid).html();
    var created=$('#create'+orderid).html();
    $('#commentimg').attr('src',img);
    $('#commentname').html(name+'</br>'+spec);
    $('#commentdate').html(created);
    $('#commentorddetid').val(orderdetid);
    $('#doc-modal-comment').modal({closeViaDimmer: 0, width: 690, height: 420});
};

function commentadd(){
    var orderdetid=$('#commentorddetid').val();
    var commenttext=$('#commenttext').val();
    var score=$('input[name="score"]').val();
    if(orderdetid==""||orderdetid=="0"){
        alert('订单号错误，请重新评价');
        $('#doc-modal-comment').modal('close');
        return false;
    }
    if(score==""||score=="0"){
        alert('请选择评分');
        return false;
    }
    if(commenttext==""||commenttext=="0"){
        alert('请填写评价内容');
        return false;
    }
    $.post("/user/commentadd",{orderdetailid:orderdetid,commenttext:commenttext,score:score},
    function(data){
        if(data.success){
            $('#doc-modal-comment').modal('close');
            location.href='User/myorders?status=99';
        }else{
            alert(data.body.error);
        }
    },'json');
};

function orderdel(orderid){
    if(orderid==""||orderid=="0"){
        alert('订单号错误');
        return false;
    }
    $.post("/user/userOrderDel",{orderid:orderid},
    function(data){
        if(data.success){
            location.href='User/myorders?status=3';
        }else{
            alert(data.body.error);
        }
    },'json');
};

function ordercancel(orderid){
    if(orderid==""||orderid=="0"){
        alert('订单号错误');
        return false;
    }
    $.post("/user/userOrderCancel",{orderid:orderid},
    function(data){
        if(data.success){
            location.href='User/myorders?status=4';
        }else{
            alert(data.body.error);
        }
    },'json');
};

function orderafter(orderid,after){
    if(orderid==""||orderid=="0"){
        alert('订单号错误');
        return false;
    }
    $.post("/user/userOrderAfter",{orderid:orderid,after:after},
    function(data){
        if(data.success){
            alert(data.body.error);
            location.href='User/myorders?status='+after;
        }else{
            alert(data.body.error);
        }
    },'json');
};

function orderconfirm(orderid){
    if(orderid==""||orderid=="0"){
        alert('订单号错误');
        return false;
    }
    $.post("/user/userOrderConfirm",{orderid:orderid},
    function(data){
        if(data.success){
            location.href='User/myorders?status=3';
        }else{
            alert(data.body.error);
        }
    },'json');
};

function openship(orderid){
    $.post("/user/getZTOshipinfo",{orderid:orderid},
    function(data){
        var receiver=data.address['receivename'];
        var addressdet=data.address['chinacity']+data.address['detailaddr'];
        var linkphone=data.address['linkphone'];
        $('.delivery-details-address p span.logistics-margin-span').html('收货人：'+receiver);
        $('.delivery-details-address p span.logistics-margin-span2').html('收货地址：'+addressdet);
        $('.delivery-details-address p span.logistics-none-span').html('联系电话：'+linkphone);
        var strdetail='';
        $.each(data.orderships,function(key,val){
            var shipcorp=val.name;
            $.each(val.data['data'],function(keydate,valdata){
                strdetail+='<div class="delivery-detail" id="'+valdata.billCode+'">';
                strdetail+='<p class="shiptitle"><span class="logistics-margin-span">运送单号：'+valdata.billCode+'</span>';
                strdetail+='<span class="logistics-margin-span" style="margin-left:20px;">物流公司：'+shipcorp+'</span></p>';
                $.each(valdata.traces,function(key1,valtrace){
                    strdetail+='<p class="delivery-desc"><span class="logistics-span-time">'+valtrace.scanDate+'</span>';
                    strdetail+='<span class="logistics-span-desc">'+valtrace.desc+'</span></p>';
                });
                strdetail+='</div>';
            });
        });
        $('#all-delivery-details').html(strdetail);
        $('#doc-modal-send').modal();
    },'json');
};

function openopership(orderid,uid){
    $('#shiporderid').val(orderid);
    $.post("/user/getOrderShipping",{orderid:orderid,uid:uid},
    function(data){
        $('#shipments-shiptpl').empty();
        $.each(data.ordershiptpl,function(key,val){
            $('#shipments-shiptpl').append("<option value='"+key+"' corp='"+val['corp']+"'>"+val['name']+"</option>");
            changcorp();
        });
        var receiver=data.address['receivename'];
        var addressdet=data.address['chinacity']+data.address['detailaddr'];
        var linkphone=data.address['linkphone'];
        $('.shipments-indent-content-span1').html('订单号：'+orderid);
        $('.shipments-indent-content-span2').html('订单时间：'+data.orderdate);
        $('.shipments-indent-content-span3').html('收货人：'+receiver);
        $('.shipments-indent-content-span4').html('联系电话：'+linkphone);
        $('.shipments-indent-content-span5').html('收货地址：'+addressdet);
        if(data.shipstatus=='1'){
            $('#outshipbt').attr('disabled','disabled');
            $('#addshipbt').attr('disabled','disabled');
        }else{
            $('#outshipbt').removeAttr('disabled');
            $('#addshipbt').removeAttr('disabled');
        }
        $('#ordershiptable tbody').remove();
        var comm=document.getElementById('ordershiptable');
        var tbody = document.createElement('tbody');
        comm.appendChild(tbody);
        $.each(data.orderships,function(key,val){
            var index=$("#ordershiptable tbody tr").length;
            var row = tbody.insertRow(index);
            var col = row.insertCell();
            col.innerHTML = val.name;
            col = row.insertCell();
            col.innerHTML = val.corp;
            col = row.insertCell();
            col.innerHTML = val.num;
            col = row.insertCell();
            col.innerHTML = '<input type="button" value="删除">';
        });
        $('#doc-modal-shipments').modal({closeViaDimmer: 0});
    },'json');
};

function changcorp(){
    var corpname=$('#shipments-shiptpl').find("option:selected").attr("corp");
    $('span.shipments-corp').html("物流公司："+corpname);
}

function addshipnum(uid){
    var shiptplid=$('#shipments-shiptpl').val();
    var shipnum=$('#shipments-shipnum').val();
    var orderid=$('#shiporderid').val();
    if(orderid==""){
        alert('订单号错误');
        return false;
    }
    if(shiptplid==""){
        alert('请选择运费模板');
        return false;
    }
    if(shipnum==""){
        alert('请输入物流运单号');
        return false;
    }
    $.post("/user/addOrderShipNum",{orderid:orderid,uid:uid,tplid:shiptplid,num:shipnum},
    function(data){
        if(data.status){
            $('#ordershiptable tbody').remove();
            var comm=document.getElementById('ordershiptable');
            var tbody = document.createElement('tbody');
            comm.appendChild(tbody);
            $.each(data.info,function(key,val){
                var index=$("#ordershiptable tbody tr").length;
                var row = tbody.insertRow(index);
                var col = row.insertCell();
                col.innerHTML = val.name;
                col = row.insertCell();
                col.innerHTML = val.corp;
                col = row.insertCell();
                col.innerHTML = val.num;
                col = row.insertCell();
                col.innerHTML = '<input type="button" value="删除">';
            });
        }else{
            alert(data.info);
        }
    },'json');
};

function shipoutok(uid){
    var orderid=$('#shiporderid').val();
    if(orderid==""){
        alert('订单号错误');
        return false;
    }
    $.post("/user/outOrderShip",{orderid:orderid,uid:uid},
    function(data){
        if(data.status){
            location.href='User/supplierorders?status=1';
        }else{
            alert(data.info);
        }
    },'json');
};