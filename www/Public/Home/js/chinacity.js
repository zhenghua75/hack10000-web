$(function(){
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
                if( pid == k ){
                    datapro += " selected ";
                    $.each(val['data'],function(c,valc){
                        datacity += "<option ";
                        if( cid == valc['id'] ){
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
            $.each(citylist[cid],function(k,val){
                datadis += "<option ";
                if( did == val['id'] ){
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
});
