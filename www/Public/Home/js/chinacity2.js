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
            $('#J_provinceorg').html(datapro);
            $('#J_cityorg').html(datacity);
            var datadis="";
            $.each(citylist[cid],function(k,val){
                datadis += "<option ";
                if( did == val['id'] ){
                    datadis += " selected ";
                }
                datadis += " value ='" + val['id'] + "'>" + val['name'] + "</option>";
            });
            $('#J_districtorg').html(datadis);

            $('#J_provinceorg').change(function(){
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
                $('#J_cityorg').html(datacity);
                var datadis="";
                $.each(citylist[selcity],function(k,val){
                    datadis += "<option ";
                    if(k==0){
                        datadis += " selected ";
                    }
                    datadis += " value ='" + val['id'] + "'>" + val['name'] + "</option>";
                });
                $('#J_districtorg').html(datadis);
            });

            $('#J_cityorg').change(function(){
                var selcity=$(this).val();
                var datadis="";
                $.each(citylist[selcity],function(k,val){
                    datadis += "<option ";
                    if(k==0){
                        datadis += " selected ";
                    }
                    datadis += " value ='" + val['id'] + "'>" + val['name'] + "</option>";
                });
                $('#J_districtorg').html(datadis);
            });
        }
    });
});
