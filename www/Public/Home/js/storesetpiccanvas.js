function handleFileSelect (evt) {
    var oFile = evt.files[0];

    // hide all errors
    $('.error').hide();

    // check for image type (jpg and png are allowed)
    var rFilter = /^(image\/jpeg|image\/png|image\/jpg|image\/gif)$/i;
    if (! rFilter.test(oFile.type)) {
        $('.error').html('请选择JPG、PNG、JPEG格式图片').show();
        return false;
    }

    // check for file size
    if (oFile.size > 1 * 1024 *1024) {
        $('.error').html('图片文件不能超过1M').show();
        return false;
    }

    // preview element
    var oImage=new Image();  

    // prepare HTML5 FileReader
    var oReader = new FileReader();
    oReader.onload = function(e) {
    	oImage.src = e.target.result;
        var opimgtype='';
        if(evt.id=='file-logo'){
            if (oImage.naturalWidth!=200 && oImage.naturalHeight!=200) {
                $('.error').html('店铺logo图片尺寸必须为200x200').show();
                return false;
            }
            opimgtype='SHOPLOGO';
        }else if(evt.id=='file-back'){
            if (oImage.naturalWidth!=1200 && oImage.naturalHeight!=400) {
                $('.error').html('店铺背景尺寸必须为1200x400').show();
                return false;
            }
            opimgtype='SHOPBACKGP';
        }else{
            $('.error').html('没有选中任何图片').show();
            return false;
        }
        $('#pic-uploading').modal('open');
    	oImage.onload = function () {
			var quality =  50;
			var mime_type = "image/jpeg";
			var cvs = document.createElement('canvas');
			//naturalWidth真实图片的宽度
			cvs.width = oImage.naturalWidth;
			cvs.height = oImage.naturalHeight;
			var ctx = cvs.getContext("2d").drawImage(oImage, 0, 0);
            var disimg = cvs.toDataURL(mime_type, quality/100);
			var base64 = cvs.toDataURL(mime_type, quality/100).substr(22);
            var file={
                name:oFile.name,
                type:"image\/jpeg",
                imgbase64:base64
            }
            var inputfile={
                productimg:file
            }
            var data1={
                uid:$('#uid').val(),
                imgtype:opimgtype,
                files:inputfile
            }
            $.post("store_picture_upload",{data:data1},
            function(data){
                $('#pic-uploading').modal('close');
                if(data.status){
                    if(opimgtype=='SHOPLOGO'){
                        var imgdom='<img src="'+data.path+'" style="width:100px;height:100px">';
                        imgdom+='<input type="hidden" id="imglogo" name="imglogo" value="'+data.id+'"/>';
                        $('#logoimg').text("");
                        $('#logoimg').append(imgdom);
                    }else if(opimgtype=='SHOPBACKGP'){
                        var imgdom='<img src="'+data.path+'" style="width:300px;height:100px">';
                        imgdom+='<input type="hidden" id="imgbackgroup" name="imgbackgroup" value="'+data.id+'"/>';
                        $('#backgroudimg').text("");
                        $('#backgroudimg').append(imgdom);
                    }
                }else{
                    alert('上传失败:'+data.info);
                }
            },'json');
        };
    }

    // Read in the image file as a data URL.
    oReader.readAsDataURL(oFile);
}

function showdiag1(imgtype){
    $(".picturedialog-all span.am-icon-spinner").show();
    var uid=$('#uid').val();
    $(".picturedialog-all ul").empty();
    $.get("store_get_user_picture?uid="+uid+"&imgtype="+imgtype,function(data){
        for(var i in data){
            var newli=$('<li>',{'id':data[i].pid});
            newli.html('<img src="'+data[i].path+'"></li>');
            $(".picturedialog-all ul").append(newli);
            newli.click(function(){
                var pid=$(this).attr('id');
                var imgpath=$(this).children('img').attr('src');
                if(imgtype=='shoplogo'){
                    var imgdom='<img src="'+imgpath+'" style="width:100px;height:100px">';
                    imgdom+='<input type="hidden" id="imglogo" name="imglogo" value="'+pid+'"/>';
                    $('#logoimg').text("");
                    $('#logoimg').append(imgdom);
                }else if(imgtype=='shopbackgp'){
                    var imgdom='<img src="'+imgpath+'" style="width:300px;height:100px">';
                    imgdom+='<input type="hidden" id="imgbackgroup" name="imgbackgroup" value="'+pid+'"/>';
                    $('#backgroudimg').text("");
                    $('#backgroudimg').append(imgdom);
                }
                $('#picturedialog').modal('close');
            });
        }
    });
    $(".picturedialog-all span.am-icon-spinner").hide();
    $('#picturedialog').modal({'closeViaDimmer':0});
};

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
                $('#tplid').val($(this).attr("data-aid"));
            }
        });
    });
});