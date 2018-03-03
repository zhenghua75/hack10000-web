$(function(){
    var provincelist;
    var citylist;
    $.get("/Addons/getChinacityList",function(data){
        if(data){
            provincelist=data['prov'];
            citylist=data['city'];
            var datapro="<option value='0'>不限</option>";
            var datacity="<option value='0'>不限</option>";
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
            var datadis="<option value='0'>不限</option>";
            if(cid!='0'){
                $.each(citylist[cid],function(k,val){
                    datadis += "<option ";
                    if( did == val['id'] ){
                        datadis += " selected ";
                    }
                    datadis += " value ='" + val['id'] + "'>" + val['name'] + "</option>";
                });
            }
            $('#J_district').html(datadis);

            $('#J_province').change(function(){
                var selprovince=$(this).val();
                var datacity="<option value='0'>不限</option>";
                $.each(provincelist[selprovince]['data'],function(k,val){
                    datacity += "<option ";
                    datacity += " value ='" + val['id'] + "'>" + val['name'] + "</option>";
                });
                $('#J_city').html(datacity);
                var datadis="<option value='0'>不限</option>";
                $('#J_district').html(datadis);
            });

            $('#J_city').change(function(){
                var selcity=$(this).val();
                var datadis="<option value='0'>不限</option>";
                $.each(citylist[selcity],function(k,val){
                    datadis += "<option ";
                    datadis += " value ='" + val['id'] + "'>" + val['name'] + "</option>";
                });
                $('#J_district').html(datadis);
            });
        }
    });
});
