function handleDiagFileSelect (evt) {
    var oFile = evt.files[0];

    // hide all errors
    $('.diagerror').hide();

    // check for image type (jpg and png are allowed)
    var rFilter = /^(image\/jpeg|image\/png|image\/jpg|image\/gif)$/i;
    if (! rFilter.test(oFile.type)) {
        $('.diagerror').html('请选择JPG、PNG、JPEG格式图片').show();
        return;
    }

    // check for file size
    if (oFile.size > 1 * 1024 *1024) {
        $('.diagerror').html('图片文件不能超过1M').show();
        return;
    }

    var pcid=$('#picclassid').val();
    // preview element
    var oImage=new Image();  

    // prepare HTML5 FileReader
    var oReader = new FileReader();
    oReader.onload = function(e) {
    	oImage.src = e.target.result;
        if (oImage.naturalWidth>1200) {
            $('.diagerror').html('图片宽度必须小于等于1200').show();
            return;
        }
        $(".picturedialog-all span.am-icon-spinner").show();
    	oImage.onload = function () {
			var quality =  50;
			var mime_type = "image/jpeg";
			var cvs = document.createElement('canvas');
			//naturalWidth真实图片的宽度
			cvs.width = oImage.naturalWidth;
			cvs.height = oImage.naturalHeight;
			var ctx = cvs.getContext("2d").drawImage(oImage, 0, 0);
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
                imgtype:"PRODUCTDETAIL",
                pcid:pcid,
                files:inputfile
            }
            $.post("picture_upload",{data:data1},
            function(data){
                $(".picturedialog-all span.am-icon-spinner").hide();
                if(data.status){
                    reloadpicturediag('productdetail','3');
                }else{
                    alert('上传失败:'+data.info);
                }
            },'json');
        };
    }

    // Read in the image file as a data URL.
    oReader.readAsDataURL(oFile);
};

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

    var pcid=$('#picclassid').val();

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
        $(".picturedialog-all span.am-icon-spinner").show();
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
                pcid:pcid,
                files:inputfile
            }
            $.post("picture_upload",{data:data1},
            function(data){
                $(".picturedialog-all span.am-icon-spinner").hide();
                if(data.status){
                    reloadpicturediag('product','1');
                }else{
                    alert('上传失败:'+data.info);
                }
            },'json');
        };
    }

    // Read in the image file as a data URL.
    oReader.readAsDataURL(oFile);
};

function handleFileSelect2 (evt) {
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

    var pcid=$('#picclassid').val();

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
        $(".picturedialog-all span.am-icon-spinner").show();
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
                pcid:pcid,
                files:inputfile
            }
            $.post("picture_upload",{data:data1},
            function(data){
                $(".picturedialog-all span.am-icon-spinner").hide();
                if(data.status){
                    reloadpicturediag('product','2');
                }else{
                    alert('上传失败:'+data.info);
                }
            },'json');
        };
    }

    // Read in the image file as a data URL.
    oReader.readAsDataURL(oFile);
};

function reloadpicturediag(imgtype,pos){
    $(".picturedialog-all span.am-icon-spinner").show();
    $(".picturedialog-all ul").empty();
    var uid=$('#uid').val();
    var pcidkey=$('#picclassid').val();
    var pcur=$('#pagecur').val();
    $('#pagetype').val('pic');
    $.get("get_user_picture?uid="+uid+"&imgtype="+imgtype+'&pcid='+pcidkey+'&p='+pcur,function(data){
        if(data['pagecnt']<=1){
            $('.diagtop .picnext').attr('disabled','disabled');
            $('.diagtop .picprev').attr('disabled','disabled');
        }else if(data['pagecnt']>1 && data['page']==1){
            $('.diagtop .picnext').removeAttr('disabled');
            $('.diagtop .picprev').attr('disabled','disabled');
        }else if(data['pagecnt']>1 && data['page']==data['pagecnt']){
            $('.diagtop .picnext').attr('disabled','disabled');
            $('.diagtop .picprev').removeAttr('disabled');
        }else{
            $('.diagtop .picnext').removeAttr('disabled');
            $('.diagtop .picprev').removeAttr('disabled');
        }
        for(var i in data['list']){
            var newli=$('<li>',{'id':data['list'][i].pid});
            newli.html('<img src="'+data['list'][i].path+'"></li>');
            $(".picturedialog-all ul").append(newli);
            switch(pos){
                case '1':
                    newli.click(function(){
                        var pid=$(this).attr('id');
                        var imgpath=$(this).children('img').attr('src');
                        imgpath=imgpath.replace(/96x72/,"300x225");
                        var childbrick=$('.gridly').children('div');
                        $('#pagecur').val(data['page']);
                        childbrick.each(function(){
                            var $this=$(this);
                            var objimg=$this.find("img");
                            if(objimg.length==0){
                                var imgdom='<img id="img[]" src="'+imgpath+'">';
                                imgdom+='<input type="hidden" id="mainpid[]" name="mainpid[]" value="'+pid+'"/>';
                                $this.append(imgdom);
                                return false;
                            }
                        });
                        $('#picturedialog').modal('close');
                    });
                    break;
                case '2':
                    newli.click(function(){
                        var pid=$(this).attr('id');
                        var imgpath=$(this).children('img').attr('src');
                        var rowname=$('#rowname').val();
                        var childspecprice=$('#specprice li#'+rowname).children();
                        $('#pagecur').val(data['page']);
                        $('#specprice li#'+rowname).children('img').attr('src',imgpath);
                        $('#specprice li#'+rowname).children('input#pidprice').val(pid);
                        $('#specprice li#'+rowname).children('img').show();
                        $('#picturedialog').modal('close');
                    });
                    break;
                case '3':
                    newli.click(function(){
                        var pid=$(this).attr('id');
                        var imgpath=$(this).children('img').attr('src');
                        imgpath=imgpath.replace(/96x72/,"org");
                        $('#pagecur').val(data['page']);
                        $('#detailimgid').val(pid);
                        $('#detailimg').attr('src',imgpath);
                        $('#picturedialog').modal('close');
                    });
                    break;
            }

        }
    });
    $(".picturedialog-all span.am-icon-spinner").hide();
};

function showdiag1(imgtype){
    if(imgtype=="product"){
        $(".picturedialog-all span.am-icon-spinner").show();
        var tophtml='<div class="top1"><button class="mypicclass" onclick="refreshpicclass(\'allclass\',1)">我的目录</button>';
        tophtml+='<input type="text" value="" id="newdir"><button class="mypicclass" onclick="createdir()">新建目录</button>';
        tophtml+='<button class="searchpicclass" onclick="refreshpicclass(\'product\',1);">搜索</button>';
        tophtml+='<input type="hidden" id="pagetype" value="dir"><input type="hidden" id="pagecur" value="1">';
        tophtml+='<input type="hidden" id="picclassid" value="0"><input type="hidden" id="btnpos" value="1"></div>';
        tophtml+='<div class="top2"><div class="am-form-file">';
        tophtml+='<button type="button" class="am-btn am-btn-danger am-btn-sm">';
        tophtml+='<i class="am-icon-plus"></i> 本地上传';
        tophtml+='<input type="file" name="file-prodimg" id="file-prodimg" onchange="handleFileSelect(this)"></button>';
        tophtml+='</div></div>';
        $('.diagtop').text("");
        $('.diagtop').append(tophtml);
        $('.diagtop').show();
        var uid=$('#uid').val();
        $(".picturedialog-all ul").empty();
        $.get("get_user_picture_class?uid="+uid+"&type=sup_prod",function(data){
            if(data['pagecnt']<=1){
                tophtml='<button class="picnext" onclick="btnnext(\'product\')" disabled>下一页</button><button class="picprev" onclick="btnprev(\'product\')" disabled>上一页</button>';
            }else if(data['pagecnt']>1 && data['page']==1){
                tophtml='<button class="picnext" onclick="btnnext(\'product\')">下一页</button><button class="picprev" onclick="btnprev(\'product\')" disabled>上一页</button>';
            }else if(data['pagecnt']>1 && data['page']==data['pagecnt']){
                tophtml='<button class="picnext" onclick="btnnext(\'product\')" disabled>下一页</button><button class="picprev" onclick="btnprev(\'product\')">上一页</button>';
            }else{
                tophtml='<button class="picnext" onclick="btnnext(\'product\')">下一页</button><button class="picprev" onclick="btnprev(\'product\')">上一页</button>';
            }
            $('.diagtop .top1').append(tophtml);
            $.each(data['list'],function(key,val){
                var newlicls=$('<li class="lipiccl" id="lipiccl'+key+'">',{'id':key});
                newlicls.html('<span class="lipicclname">'+val+'</span></li>');
                $(".picturedialog-all ul").append(newlicls);
                newlicls.click(function(){
                    $(".picturedialog-all span.am-icon-spinner").show();
                    $(".picturedialog-all ul").empty();
                    var pcidkey=$(this).attr('id');
                    pcidkey=pcidkey.substr(7);
                    $('#pagetype').val('pic');
                    $('#picclassid').val(pcidkey);
                    $.get("get_user_picture?uid="+uid+"&imgtype="+imgtype+'&pcid='+pcidkey,function(data){
                        if(data['pagecnt']<=1){
                            $('.diagtop .picnext').attr('disabled','disabled');
                            $('.diagtop .picprev').attr('disabled','disabled');
                        }else if(data['pagecnt']>1 && data['page']==1){
                            $('.diagtop .picnext').removeAttr('disabled');
                            $('.diagtop .picprev').attr('disabled','disabled');
                        }else if(data['pagecnt']>1 && data['page']==data['pagecnt']){
                            $('.diagtop .picnext').attr('disabled','disabled');
                            $('.diagtop .picprev').removeAttr('disabled');
                        }else{
                            $('.diagtop .picnext').removeAttr('disabled');
                            $('.diagtop .picprev').removeAttr('disabled');
                        }
                        $('#pagecur').val(data['page']);
                        for(var i in data['list']){
                            var newli=$('<li>',{'id':data['list'][i].pid});
                            newli.html('<img src="'+data['list'][i].path+'"></li>');
                            $(".picturedialog-all ul").append(newli);
                            newli.click(function(){
                                var pid=$(this).attr('id');
                                var imgpath=$(this).children('img').attr('src');
                                imgpath=imgpath.replace(/96x72/,"300x225");
                                var childbrick=$('.gridly').children('div');
                                childbrick.each(function(){
                                    var $this=$(this);
                                    var objimg=$this.find("img");
                                    if(objimg.length==0){
                                        var imgdom='<img id="img[]" src="'+imgpath+'">';
                                        imgdom+='<input type="hidden" id="mainpid[]" name="mainpid[]" value="'+pid+'"/>';
                                        $this.append(imgdom);
                                        return false;
                                    }
                                });
                                $('#picturedialog').modal('close');
                            });
                        }
                    });
                    $(".picturedialog-all span.am-icon-spinner").hide();
                });
            });
        });
        $(".picturedialog-all span.am-icon-spinner").hide();
        $('#picturedialog').modal({'closeViaDimmer':0});
    }
};

function showdiag2(imgtype,rowname){
    if(imgtype=="product"){
        $(".picturedialog-all span.am-icon-spinner").show();
        var tophtml='<div class="top1"><button class="mypicclass" onclick="refreshpicclass(\'allclass\',1)">我的目录</button>';
        tophtml+='<input type="text" value="" id="newdir"><button class="mypicclass" onclick="createdir()">新建目录</button>';
        tophtml+='<button class="searchpicclass" onclick="refreshpicclass(\'product\',1);">搜索</button>';
        tophtml+='<input type="hidden" id="pagetype" value="dir"><input type="hidden" id="pagecur" value="1">';
        tophtml+='<input type="hidden" id="picclassid" value="0"><input type="hidden" id="btnpos" value="2">';
        tophtml+='<input type="hidden" id="rowname" value="'+rowname+'"></div>';
        tophtml+='<div class="top2"><div class="am-form-file">';
        tophtml+='<button type="button" class="am-btn am-btn-danger am-btn-sm">';
        tophtml+='<i class="am-icon-plus"></i> 本地上传';
        tophtml+='<input type="file" name="file-prodimg" id="file-prodimg" onchange="handleFileSelect2(this)"></button>';
        tophtml+='</div></div>';
        $('.diagtop').text("");
        $('.diagtop').append(tophtml);
        $('.diagtop').show();
        var uid=$('#uid').val();
        $(".picturedialog-all ul").empty();
        $.get("get_user_picture_class?uid="+uid+"&type=sup_prod",function(data){
            if(data['pagecnt']<=1){
                tophtml='<button class="picnext" onclick="btnnext(\'product\')" disabled>下一页</button><button class="picprev" onclick="btnprev(\'product\')" disabled>上一页</button>';
            }else if(data['pagecnt']>1 && data['page']==1){
                tophtml='<button class="picnext" onclick="btnnext(\'product\')">下一页</button><button class="picprev" onclick="btnprev(\'product\')" disabled>上一页</button>';
            }else if(data['pagecnt']>1 && data['page']==data['pagecnt']){
                tophtml='<button class="picnext" onclick="btnnext(\'product\')" disabled>下一页</button><button class="picprev" onclick="btnprev(\'product\')">上一页</button>';
            }else{
                tophtml='<button class="picnext" onclick="btnnext(\'product\')">下一页</button><button class="picprev" onclick="btnprev(\'product\')">上一页</button>';
            }
            $('.diagtop .top1').append(tophtml);
            $.each(data['list'],function(key,val){
                var newlicls=$('<li class="lipiccl" id="lipiccl'+key+'">',{'id':key});
                newlicls.html('<span class="lipicclname">'+val+'</span></li>');
                $(".picturedialog-all ul").append(newlicls);
                newlicls.click(function(){
                    $(".picturedialog-all span.am-icon-spinner").show();
                    $(".picturedialog-all ul").empty();
                    var pcidkey=$(this).attr('id');
                    pcidkey=pcidkey.substr(7);
                    $('#pagetype').val('pic');
                    $('#picclassid').val(pcidkey);
                    $.get("get_user_picture?uid="+uid+"&imgtype="+imgtype+'&pcid='+pcidkey,function(data){
                        if(data['pagecnt']<=1){
                            $('.diagtop .picnext').attr('disabled','disabled');
                            $('.diagtop .picprev').attr('disabled','disabled');
                        }else if(data['pagecnt']>1 && data['page']==1){
                            $('.diagtop .picnext').removeAttr('disabled');
                            $('.diagtop .picprev').attr('disabled','disabled');
                        }else if(data['pagecnt']>1 && data['page']==data['pagecnt']){
                            $('.diagtop .picnext').attr('disabled','disabled');
                            $('.diagtop .picprev').removeAttr('disabled');
                        }else{
                            $('.diagtop .picnext').removeAttr('disabled');
                            $('.diagtop .picprev').removeAttr('disabled');
                        }
                        $('#pagecur').val(data['page']);
                        for(var i in data['list']){
                            var newli=$('<li>',{'id':data['list'][i].pid});
                            newli.html('<img src="'+data['list'][i].path+'"></li>');
                            $(".picturedialog-all ul").append(newli);
                            newli.click(function(){
                                var pid=$(this).attr('id');
                                var imgpath=$(this).children('img').attr('src');
                                var childspecprice=$('#specprice li#'+rowname).children();
                                $('#specprice li#'+rowname).children('img').attr('src',imgpath);
                                $('#specprice li#'+rowname).children('input#pidprice').val(pid);
                                $('#specprice li#'+rowname).children('img').show();
                                $('#picturedialog').modal('close');
                            });
                        }
                    });
                    $(".picturedialog-all span.am-icon-spinner").hide();
                });
            });
        });
        $(".picturedialog-all span.am-icon-spinner").hide();
        $('#picturedialog').modal({'closeViaDimmer':0});
    }
};

function showdiag3(){
    $(".picturedialog-all span.am-icon-spinner").show();
    var tophtml='<div class="top1"><button class="mypicclass" onclick="refreshpicclass(\'allclassdetail\',1)">我的目录</button>';
    tophtml+='<input type="text" value="" id="newdir"><button class="mypicclass" onclick="createdir()">新建目录</button>';
    tophtml+='<button class="searchpicclass" onclick="refreshpicclass(\'productdetail\',1);">搜索</button>';
    tophtml+='<input type="hidden" id="pagetype" value="dir"><input type="hidden" id="pagecur" value="1">';
    tophtml+='<input type="hidden" id="picclassid" value="0"><input type="hidden" id="btnpos" value="3"></div>';
    tophtml+='<div class="top2"><div class="am-form-file">';
    tophtml+='<button type="button" class="am-btn am-btn-danger am-btn-sm">';
    tophtml+='<i class="am-icon-plus"></i> 本地上传详情图';
    tophtml+='<input type="file" name="file-prodimg" id="file-prodimg" onchange="handleDiagFileSelect(this)"></button>';
    tophtml+='</div><span style="font-size:13px;color:red;float:left">此处只能上传详情图</span></div>';
    $('.diagtop').text("");
    $('.diagtop').append(tophtml);
    $('.diagtop').show();
    var uid=$('#uid').val();
    $(".picturedialog-all ul").empty();
    var imgtype='productdetail';
    $.get("get_user_picture_class?uid="+uid+"&type=sup_prod",function(data){
        if(data['pagecnt']<=1){
            tophtml='<button class="picnext" onclick="btnnext(\'productdetail\')" disabled>下一页</button><button class="picprev" onclick="btnprev(\'productdetail\')" disabled>上一页</button>';
        }else if(data['pagecnt']>1 && data['page']==1){
            tophtml='<button class="picnext" onclick="btnnext(\'productdetail\')">下一页</button><button class="picprev" onclick="btnprev(\'productdetail\')" disabled>上一页</button>';
        }else if(data['pagecnt']>1 && data['page']==data['pagecnt']){
            tophtml='<button class="picnext" onclick="btnnext(\'productdetail\')" disabled>下一页</button><button class="picprev" onclick="btnprev(\'productdetail\')">上一页</button>';
        }else{
            tophtml='<button class="picnext" onclick="btnnext(\'productdetail\')">下一页</button><button class="picprev" onclick="btnprev(\'productdetail\')">上一页</button>';
        }
        $('.diagtop .top1').append(tophtml);
        $.each(data['list'],function(key,val){
            var newlicls=$('<li class="lipiccl" id="lipiccl'+key+'">',{'id':key});
            newlicls.html('<span class="lipicclname">'+val+'</span></li>');
            $(".picturedialog-all ul").append(newlicls);
            newlicls.click(function(){
                $(".picturedialog-all span.am-icon-spinner").show();
                $(".picturedialog-all ul").empty();
                var pcidkey=$(this).attr('id');
                pcidkey=pcidkey.substr(7);
                $('#pagetype').val('pic');
                $('#picclassid').val(pcidkey);
                $.get("get_user_picture?uid="+uid+"&imgtype="+imgtype+'&pcid='+pcidkey,function(data){
                    if(data['pagecnt']<=1){
                        $('.diagtop .picnext').attr('disabled','disabled');
                        $('.diagtop .picprev').attr('disabled','disabled');
                    }else if(data['pagecnt']>1 && data['page']==1){
                        $('.diagtop .picnext').removeAttr('disabled');
                        $('.diagtop .picprev').attr('disabled','disabled');
                    }else if(data['pagecnt']>1 && data['page']==data['pagecnt']){
                        $('.diagtop .picnext').attr('disabled','disabled');
                        $('.diagtop .picprev').removeAttr('disabled');
                    }else{
                        $('.diagtop .picnext').removeAttr('disabled');
                        $('.diagtop .picprev').removeAttr('disabled');
                    }
                    $('#pagecur').val(data['page']);
                    for(var i in data['list']){
                        var newli=$('<li>',{'id':data['list'][i].pid});
                        newli.html('<img src="'+data['list'][i].path+'"></li>');
                        $(".picturedialog-all ul").append(newli);
                        newli.click(function(){
                            var pid=$(this).attr('id');
                            var imgpath=$(this).children('img').attr('src');
                            imgpath=imgpath.replace(/96x72/,"org");
                            $('#detailimgid').val(pid);
                            $('#detailimg').attr('src',imgpath);
                            $('#picturedialog').modal('close');
                        });
                    }
                });
                $(".picturedialog-all span.am-icon-spinner").hide();
            });
        });
    });
    $(".picturedialog-all span.am-icon-spinner").hide();
    $('#picturedialog').modal({'closeViaDimmer':0});
};

function refreshpicclass(imgtype,p){
    $(".picturedialog-all span.am-icon-spinner").show();
    var uid=$('#uid').val();
    $(".picturedialog-all ul").empty();
    $('#pagetype').val('dir');
    $('#picclassid').val('0');
    $('#pagecur').val(p);
    if(imgtype=='allclass'){
        var classname='';
        $('#newdir').val('');
        imgtype="product";
    }else if(imgtype=='allclassdetail'){
        var classname='';
        $('#newdir').val('');
        imgtype="productdetail";
    }else{
        var classname=$('#newdir').val();
    }
    
    var url="get_user_picture_class?uid="+uid+"&type=sup_prod&p="+p+"&cln="+classname;
    $.get(url,function(data){
        if(data['pagecnt']<=1){
            $('.diagtop .picnext').attr('disabled','disabled');
            $('.diagtop .picprev').attr('disabled','disabled');
        }else if(data['pagecnt']>1 && data['page']==1){
            $('.diagtop .picnext').removeAttr('disabled');
            $('.diagtop .picprev').attr('disabled','disabled');
        }else if(data['pagecnt']>1 && data['page']==data['pagecnt']){
            $('.diagtop .picnext').attr('disabled','disabled');
            $('.diagtop .picprev').removeAttr('disabled');
        }else{
            $('.diagtop .picnext').removeAttr('disabled');
            $('.diagtop .picprev').removeAttr('disabled');
        }
        $.each(data['list'],function(key,val){
            var newlicls=$('<li class="lipiccl" id="lipiccl'+key+'">',{'id':key});
            newlicls.html('<span class="lipicclname">'+val+'</span></li>');
            $(".picturedialog-all ul").append(newlicls);
            newlicls.click(function(){
                var pcidkey=$(this).attr('id');
                var pos=$('#btnpos').val();
                pcidkey=pcidkey.substr(7);
                $('#pagetype').val('pic');
                $('#picclassid').val(pcidkey);
                $('#pagecur').val(1);
                reloadpicturediag(imgtype,pos);
            });
        });
    });
    $(".picturedialog-all span.am-icon-spinner").hide();
};

function createdir(){
    var dirname=$.trim($('#newdir').val());
    if(dirname==''){
        alert('新输入新建目录名称');
    }else{
        var uid=$('#uid').val();
        var data1={
            name:dirname,
            uid:uid,
            type:'sup_prod',
        }
        $.post("create_member_picclass",{data:data1},
        function(data){
            if(data.status){
                refreshpicclass('product',1);
            }else{
                alert(data.error);
            }
        },'json');
    }
};

function displaypageimg(imgtype,p){
    var pos=$('#btnpos').val();
    $('#pagecur').val(p);
    reloadpicturediag(imgtype,pos);
};

function btnprev(imgtype){
    var type=$('#pagetype').val();
    var p=parseInt($('#pagecur').val())-1;
    if(type=='dir'){
        refreshpicclass(imgtype,p);
    }else{
        displaypageimg(imgtype,p);
    }
}

function btnnext(imgtype){
    var type=$('#pagetype').val();
    var p=parseInt($('#pagecur').val())+1;
    if(type=='dir'){
        refreshpicclass(imgtype,p);
    }else{
        displaypageimg(imgtype,p);
    }
}