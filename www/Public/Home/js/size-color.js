/**
 * Created by Administrator on 2015-11-05.
 */
//价格json
// var sys_item={
//     "mktprice":"13.00",
//     "price":"6.80",
//     "sys_attrprice":{"6_1":{"price":"6.80","mktprice":"13.00"},"7_1":{"price":"7.80","mktprice":"14.00"},"3_16":{"price":"8.80","mktprice":"15.00"},"3_17":{"price":"9.80","mktprice":"16.00"},"4_13":{"price":"6.80","mktprice":"13.00"},"4_14":{"price":"7.80","mktprice":"14.00"},"4_16":{"price":"8.80","mktprice":"15.00"},"4_17":{"price":"9.80","mktprice":"16.00"},"8_13":{"price":"6.80","mktprice":"13.00"},"8_14":{"price":"7.80","mktprice":"1400"},"8_16":{"price":"8.80","mktprice":"15.00"},"8_17":{"price":"9.80","mktprice":"16.00"},"9_13":{"price":"6.80","mktprice":"13.00"},"9_14":{"price":"7.80","mktprice":"14.00"},"9_16":{"price":"8.80","mktprice":"15.00"},"9_17":{"price":"9.80","mktprice":"16.00"},"10_13":{"price":"6.80","mktprice":"13.00"},"10_14":{"price":"7.80","mktprice":"14.00"},"10_16":{"price":"8.80","mktprice":"15.00"},"10_17":{"price":"9.80","mktprice":"16.00"},"12_13":{"price":"6.80","mktprice":"13.00"},"12_14":{"price":"7.80","mktprice":"14.00"},"12_16":{"price":"8.80","mktprice":"15.00"},"12_17":{"price":"9.80","mktprice":"16.00"}}};


//商品规格选择
$(function(){
    $(".sys_item_spec .sys_item_specpara").each(function(){
        var i=$(this);
        var p=i.find("ul>li");
        p.click(function(){
            if(!!$(this).hasClass("selected")){
                $(this).removeClass("selected");
                i.removeAttr("data-attrval");
            }else{
                $(this).addClass("selected").siblings("li").removeClass("selected");
                i.attr("data-attrval",$(this).attr("data-aid"));
            }
            if($('#prodstatus').val()!='-1'){
                getattrprice();
            }
            
        });
        getattrprice();
    });
    //获取对应属性的价格
    function getattrprice(){
        var defaultstats=true;
        var _val='';
        var _resp={
            mktprice:".sys_item_mktprice",
            price:".sys_item_price"
        }  //输出对应的class
        $(".sys_item_spec .sys_item_specpara").each(function(){
            var i=$(this);
            var v=i.attr("data-attrval");
            var sid=i.attr("data-sid");
            if(v){
                _val+=_val!=""?"-":"";
                _val+=sid+'_'+v;
                defaultstats=false;
            }
        })
        if(!defaultstats){
            if(sys_item['sys_attrprice'][_val]){
                $('#selgroupid').val(sys_item['sys_attrprice'][_val]['group_id']);
                $('#selhackprice').val(sys_item['sys_attrprice'][_val]['price']);
                _mktprice=sys_item['sys_attrprice'][_val]['mktprice'];
                _price=sys_item['sys_attrprice'][_val]['price'];
                if(sys_item['sys_attrprice'][_val]['inventory']>0){
                    $('input#tocart').removeAttr('disabled');
                    $('.alarmprompt').html('');
                }else{
                    $('input#tocart').attr('disabled','disabled');
                    $('.alarmprompt').html('库存不足');
                }
            }else{
                $('#selgroupid').val('');
                $('#selhackprice').val('');
                _mktprice=sys_item['mktprice'];
                _price=sys_item['price'];
                $('input#tocart').attr('disabled','disabled');
                $('.alarmprompt').html('');
            }
        }else{
            $('#selgroupid').val('');
            $('#selhackprice').val('');
            _mktprice=sys_item['mktprice'];
            _price=sys_item['price'];
            $('input#tocart').attr('disabled','disabled');
            $('.alarmprompt').html('');
        }
        //输出价格
        $(_resp.mktprice).text('￥'+_mktprice);  ///其中的math.round为截取小数点位数
        $(_resp.price).text('￥'+_price);
    };
})