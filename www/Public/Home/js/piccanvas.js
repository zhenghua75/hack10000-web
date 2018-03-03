function handleFileSelect (evt,imgtp) {
    var oFile = evt.files[0];

    // hide all errors
    $('.error').hide();

    // check for image type (jpg and png are allowed)
    var rFilter = /^(image\/jpeg|image\/png|image\/jpg)$/i;
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
    //var oImage = document.getElementById('preview');
    var oImage=new Image();  

    // prepare HTML5 FileReader
    var oReader = new FileReader();
    oReader.onload = function(e) {
    	oImage.src = e.target.result;
        $('#pic-uploading').modal('open');
    	oImage.onload = function () {
			var quality =  50;
			var mime_type = oFile.type;
			var cvs = document.createElement('canvas');
			//naturalWidth真实图片的宽度
			cvs.width = oImage.naturalWidth;
			cvs.height = oImage.naturalHeight;
			var ctx = cvs.getContext("2d").drawImage(oImage, 0, 0);
			var base64 = cvs.toDataURL(mime_type, quality/100).substr(22);
            var fieldname=evt.id.substr(5);
			$('#'+fieldname+'name').val(oFile.name);
            if(imgtp=='MAKERORG'){
                var curuid=$('#uidorg').val();
            }else{
                var curuid=$('#uid').val();
            }
            var file={
                name:oFile.name,
                type:mime_type,
                imgbase64:base64
            }
            var inputfile={
                productimg:file
            }
            var data1={
                uid:curuid,
                imgtype:imgtp,
                files:inputfile
            }
            $.post("reg_picture_upload",{data:data1},
            function(data){
                $('#pic-uploading').modal('close');
                if(data.status){
                    $('#'+fieldname).val(data.id);
                }else{
                    alert('上传失败:'+data.info);
                }
            },'json');
        };
    }

    // Read in the image file as a data URL.
    oReader.readAsDataURL(oFile);
}