function collect(objid,type,sourceid){
    var datain;
    switch(type){
        case 1:
            url="/Store/collect";
            datain={object_id:objid,type:'product'};
            break;
        case 2:
            url="/Shop/collect";
            datain={object_id:objid,type:'product',sourceobjid:sourceid};
            break;
        case 3:
            url="/Shop/collect";
            datain={object_id:objid,type:'shop'};
            break;
    }
    $.post(url,datain,
    function(data){
        if(data.success){
            if(!$("#colbtn").hasClass("active")){
                $("#colbtn").addClass("active");
            }else{
                $("#colbtn").removeClass("active");
            }
        }else{
            alert(data.body.error);
        }
    },'json');
};

function enjoy(objid,type){
    if(type==1){
        url="/Store/favor";
    }else{
        url="/Shop/favor";
    }
    $.post(url,{object_id:objid},
    function(data){
        if(data.success){
            if(!$("#enjbtn").hasClass("active")){
                $("#enjbtn").addClass("active");
            }
        }else{
            alert(data.body.error);
        }
    },'json');
};