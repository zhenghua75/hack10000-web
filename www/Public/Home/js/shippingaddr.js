(function ($) {
    $.getUrlParam = function (name) {
        var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)");
        var r = window.location.search.substr(1).match(reg);
        if (r != null) return unescape(r[2]); return null;
    }
})(jQuery);

function addshippingaddr(){
    $('#addrid').val('0');
    $('#detailaddr').val('');
    $('#receivename').val('');
    $('#linkphone').val('');
    $('#shippingaddress').modal({'closeViaDimmer':0});
    $('#shippingaddress').find('.am-modal-btn.ok').off('click.close.modal.amui');
};

function addrdefault(id){
    var data1={
        id:id,
        isdefault:1,
    };
    $.post("myshippingaddroper",{data:data1},
    function(data){
        if(data.success){
            window.location.href="/User/index.html?t=4";
        }else{
            alert('收货地址错误:'+data.body.error);
        }
    },'json');
};

function addredit(id){
    var addrkey=-1;
    $.each(shaddrlist,function(key,val){
        if(val['id']==id){
            addrkey=key;
        }
    });
    $('#addrid').val(id);
    pidtmp=shaddrlist[addrkey]['province'];
    cidtmp=shaddrlist[addrkey]['city'];
    didtmp=shaddrlist[addrkey]['district'];
    seteditaddrcity(pidtmp,cidtmp,didtmp);
    $('#detailaddr').val(shaddrlist[addrkey]['detailaddr']);
    $('#receivename').val(shaddrlist[addrkey]['receivename']);
    $('#linkphone').val(shaddrlist[addrkey]['linkphone']);
    $('#shippingaddress').modal({'closeViaDimmer':0});
    $('#shippingaddress').find('.am-modal-btn.ok').off('click.close.modal.amui');
};

function addrdel(id){
    var data1={
        id:id,
        status:-1,
    };
    $.post("myshippingaddroper",{data:data1},
    function(data){
        if(data.success){
            window.location.href="/User/index.html?t=4";
        }else{
            alert('收货地址错误:'+data.body.error);
        }
    },'json');
};

$(function(){
    var tabindex = $.getUrlParam('t');
    tabindex=parseInt(tabindex);
    if(tabindex>0){
        $('#memberindextab').tabs('open', tabindex);
    }else{
        $('#memberindextab').tabs();
    }
    $('#memberindextab').tabs('refresh');
    $('#shippingaddress .am-modal-btn.ok').on('click',function(){
        var province=$('#J_province').val();
        var city=$('#J_city').val();
        var district=$('#J_district').val();
        var detailaddr=$.trim($('#detailaddr').val());
        var receivename=$.trim($('#receivename').val());
        var linkphone=$.trim($('#linkphone').val());
        var topost=true;
        if(province==''||province=='0'||city==''||city=='0'||district==''||district=='0'){
            topost=false;
            alert('请选择地址区域');
            return false;
        }
        if(detailaddr==''){
            topost=false;
            alert('请输入详细地址');
            return false;
        }
        if(receivename==''){
            topost=false;
            alert('请输入收货人');
            return false;
        }
        if(linkphone==''){
            topost=false;
            alert('请输入联系电话');
            return false;
        }
        if(topost){
            var adrid=$('#addrid').val();
            if(adrid=='0'){
                var data1={
                    province:province,
                    city:city,
                    district:district,
                    detailaddr:detailaddr,
                    receivename:receivename,
                    linkphone:linkphone,
                };
            }else{
                var data1={
                    id:parseInt(adrid),
                    province:province,
                    city:city,
                    district:district,
                    detailaddr:detailaddr,
                    receivename:receivename,
                    linkphone:linkphone,
                };
            }

            $.post("myshippingaddroper",{data:data1},
            function(data){
                if(data.success){
                    $('#shippingaddress').modal('close');
                    window.location.href="/User/index.html?t=4";
                }else{
                    alert('收货地址错误:'+data.body.error);
                }
            },'json');
        }
    });
});

function seteditaddrcity(pidnew,cidnew,didnew){
    var provincelist;
    var citylist;
    $.get("/Addons/getChinacityList",function(data){
        if(data){
            provincelist=data['prov'];
            citylist=data['city'];
            var datapro="";
            var datacity="";
            $.each(provincelist,function(k,val){
                datapro += "<option ";
                if( pidnew == k ){
                    datapro += " selected ";
                    $.each(val['data'],function(c,valc){
                        datacity += "<option ";
                        if( cidnew == valc['id'] ){
                            datacity += " selected ";
                        }
                        datacity += " value ='" + valc['id'] + "'>" + valc['name'] + "</option>";
                    });
                }
                datapro += " value ='" + k + "'>" + val['name'] + "</option>";
            });
            $('#J_province').html(datapro);
            $('#J_city').html(datacity);
            var datadis="";
            $.each(citylist[cidnew],function(k,val){
                datadis += "<option ";
                if( didnew == val['id'] ){
                    datadis += " selected ";
                }
                datadis += " value ='" + val['id'] + "'>" + val['name'] + "</option>";
            });
            $('#J_district').html(datadis);

            $('#J_province').change(function(){
                var selprovince=$(this).val();
                var datacity="";
                var selcity=0;
                $.each(provincelist[selprovince]['data'],function(k,val){
                    datacity += "<option ";
                    if(k==0){
                        datacity += " selected ";
                        selcity=val['id'];
                    }
                    datacity += " value ='" + val['id'] + "'>" + val['name'] + "</option>";
                });
                $('#J_city').html(datacity);
                var datadis="";
                $.each(citylist[selcity],function(k,val){
                    datadis += "<option ";
                    if(k==0){
                        datadis += " selected ";
                    }
                    datadis += " value ='" + val['id'] + "'>" + val['name'] + "</option>";
                });
                $('#J_district').html(datadis);
            });

            $('#J_city').change(function(){
                var selcity=$(this).val();
                var datadis="";
                $.each(citylist[selcity],function(k,val){
                    datadis += "<option ";
                    if(k==0){
                        datadis += " selected ";
                    }
                    datadis += " value ='" + val['id'] + "'>" + val['name'] + "</option>";
                });
                $('#J_district').html(datadis);
            });
        }
    });
};