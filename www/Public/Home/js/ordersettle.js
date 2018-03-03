$(function(){
    $('#shippingaddr input[type="radio"]').on('change',function(){
        // var addrid=$(this).val();
        // $('#shippingaddr table tr').each(function(){
        //     var curtrid=$(this).attr('id');
        //     if(addrid==curtrid){
        //         $(this).addClass('selecttr');
        //         $(this).children('td').each(function(key){
        //             if(key==0){
        //                 $(this).addClass('selicon');
        //             }
        //             if(key==1){
        //                 $(this).html('寄送至');
        //             }
        //         });
        //     }else{
        //         if($(this).hasClass('selecttr')){
        //             $(this).removeClass('selecttr');
        //         }
        //         $(this).children('td').each(function(key){
        //             if(key==0){
        //                 if($(this).hasClass('selicon')){
        //                     $(this).removeClass('selicon');
        //                 }
        //             }
        //             if(key==1){
        //                 $(this).html('');
        //             }
        //         });
        //     }
        // });
        $('#formorder').attr('action','ordersettle');
        $('#formorder').submit();
    });

    $('.shippingmethod select').on('change',function(){
        $('#formorder').attr('action','ordersettle');
        $('#formorder').submit();
    });

    $('.coupon select').on('change',function(){
        $('#formorder').attr('action','ordersettle');
        $('#formorder').submit();
    });

    $('#btok').on('click',function(){
        $('#formorder').attr('action','orderpay');
        $('#formorder').submit();
    });
});
