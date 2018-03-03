function opensend(orderid){
    var img=$('#img'+prodid).attr('src');
    var name=$('#name'+prodid).html();
    var spec=$('#spec'+prodid).html();
    var created=$('#create'+orderid).html();
    $('#commentimg').attr('src',img);
    $('#commentname').html(name+'</br>'+spec);
    $('#commentdate').html(created);
    $('#commentorderid').val(orderid);
    $('#commentproductid').val(prodid);
    $('#doc-modal-comment').modal({closeViaDimmer: 0, width: 690, height: 420});
};

function commentadd(){
    var orderid=$('#commentorderid').val();
    var prodid=$('#commentproductid').val();
    var commenttext=$('#commenttext').val();
    var score=$('input[name="score"]').val();
    if(orderid==""||orderid=="0"){
        alert('订单号错误，请重新评价');
        $('#doc-modal-comment').modal('close');
        return false;
    }
    if(prodid==""||prodid=="0"){
        alert('商品编号错误，请重新评价');
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
    $.post("/user/commentadd",{orderid:orderid,product_id:prodid,commenttext:commenttext,score:score},
    function(data){
        if(data.success){
            $('#doc-modal-comment').modal('close');
            location.href='User/myorders?item=99';
        }else{
            alert(data.body.error);
        }
    },'json');
};