KindEditor.plugin('gallery', function(K) {
    var self = this, name = 'gallery',
    	lang = self.lang(name + '.');
    //点击图标时执行
    self.clickToolbar(name, function() {
        html = '<div class="ke-dialog-row">'+
            '<div class="error" style="color:red;width:100%"></div>'+
            '<input type="file" class="ke-upload-button" value="上传" id="file-img" />'+
            '<img id="diagimg" src="" style="width:250px;height:250px">'+
            '</div>';
        dialog = self.createDialog({
            name : name,
            width : 300,
            height : 350,
            title : self.lang(name),
            body : html
        }),
        div = dialog.div;

        var fileinput=K('#file-img',div);
        fileinput.change(function(e){
           var oFile = e.target.files[0];

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
                if (oImage.naturalWidth!=600 || oImage.naturalHeight!=450) {
                    $('.error').html('图片尺寸为600x450').show();
                    return;
                }
                dialog.showLoading(self.lang('uploadLoading'));
                oImage.onload = function () {
                    var quality =  50;
                    var mime_type = oFile.type;
                    var cvs = document.createElement('canvas');
                    //naturalWidth真实图片的宽度
                    cvs.width = oImage.naturalWidth;
                    cvs.height = oImage.naturalHeight;
                    var ctx = cvs.getContext("2d").drawImage(oImage, 0, 0);
                    var base64 = cvs.toDataURL(mime_type, quality/100).substr(22);
                    var curuid=$('#curuid').val();
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
                        imgtype:'message',
                        files:inputfile
                    }
                    $.post("admin.php?s=/Membership/picture_upload",{data:data1},
                    function(data){
                        if(data.status){
                            dialog.hideLoading();
                            $('#diagimg').attr('src',data.path);
                            var url = data.path;
                            self.insertHtml('<img src="' + url + '" border="0" style="width:100%" alt="" />').hideMenu().focus();
                        }else{
                            alert('上传失败:'+data.info);
                        }
                    },'json');
                };
            }

            // Read in the image file as a data URL.
            oReader.readAsDataURL(oFile);
        });
    });
});