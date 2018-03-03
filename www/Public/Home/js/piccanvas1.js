function handleFileSelect (evt) {
    var oFile = evt.files[0];

    // hide all errors
    $('.error').hide();

    // check for image type (jpg and png are allowed)
    var rFilter = /^(image\/jpeg|image\/png|image\/jpg|image\/gif)$/i;
    if (! rFilter.test(oFile.type)) {
        $('.error').html('请选择JPG、PNG、JPEG格式图片').show();
        return;
    }

    // check for file size
    if (oFile.size > 1 * 1024 *1024) {
        $('.error').html('图片文件不能超过1M').show();
        return;
    }

    // preview element
    var oImage=new Image();  

    // prepare HTML5 FileReader
    var oReader = new FileReader();
    oReader.onload = function(e) {
    	oImage.src = e.target.result;
        if (oImage.naturalWidth!=1024 || oImage.naturalHeight!=768) {
            $('.error').html('图片尺寸必须为1024x768').show();
            return;
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
                imgtype:"PRODUCT",
                files:inputfile
            }
            $.post("picture_upload",{data:data1},
            function(data){
                $('#pic-uploading').modal('close');
                if(data.status){
                    var childbrick=$('.gridly').children('div');
                    childbrick.each(function(){
                        var $this=$(this);
                        var objimg=$this.find("img");
                        if(objimg.length==0){
                            var imgdom='<img id="img[]" src="'+data.path+'">';
                            imgdom+='<input type="hidden" id="mainpid[]" name="mainpid[]" value="'+data.id+'"/>';
                            $this.append(imgdom);
                            return false;
                        }
                    });
                }else{
                    alert('上传失败:'+data.info);
                }
            },'json');
        };
    }

    // Read in the image file as a data URL.
    oReader.readAsDataURL(oFile);
}