/**
 *
 * HTML5 Image uploader with Jcrop
 *
 * Licensed under the MIT license.
 * http://www.opensource.org/licenses/mit-license.php
 * 
 * Copyright 2012, Script Tutorials
 * http://www.script-tutorials.com/
 */
// convert bytes into friendly format
function bytesToSize(bytes) {
    var sizes = ['Bytes', 'KB', 'MB'];
    if (bytes == 0) return 'n/a';
    var i = parseInt(Math.floor(Math.log(bytes) / Math.log(1024)));
    return (bytes / Math.pow(1024, i)).toFixed(1) + ' ' + sizes[i];
};

// check for selected crop region
function checkForm() {
    if (parseInt($('#w').val())) return true;
    $('.error').html('请重新选择').show();
    return false;
};

// update info by cropping (onChange and onSelect events handler)
function updateInfo(e) {
    $('#x1').val(e.x);
    $('#y1').val(e.y);
    $('#x2').val(e.x2);
    $('#y2').val(e.y2);
    $('#w').val(e.x);
    $('#h').val(e.y);
};

// clear info by cropping (onRelease event handler)
function clearInfo() {
    $('.info #w').val('');
    $('.info #h').val('');
};

// Create variables (in this scope) to hold the Jcrop API and image size
var jcrop_api, boundx, boundy;

function fileSelectHandler() {
    // get selected file
    var oFile = $('#image_file')[0].files[0];

    // hide all errors
    $('.error').hide();

    // check for image type (jpg and png are allowed)
    var rFilter = /^(image\/jpeg|image\/png|image\/jpg|image\/gif)$/i;
    if (! rFilter.test(oFile.type)) {
        $('.error').html('请选择JPG、GIF、PNG、JPEG格式图片').show();
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

        // e.target.result contains the DataURL which we can use as a source of the image
        oImage.src = e.target.result;
        oImage.onload = function () { // onload event handler

            //display step 2
            $('.step2').fadeIn(500);

            // display some basic image info
            var sResultFileSize = bytesToSize(oFile.size);
            $('#filesize').val(sResultFileSize);
            $('#filetype').val(oFile.type);
            $('#filedim').val(oImage.naturalWidth + ' x ' + oImage.naturalHeight);
            $('#orgname').val(oFile.name);

            // destroy Jcrop if it is existed
            if (typeof jcrop_api != 'undefined') {
                jcrop_api.destroy();
            }

            $('#head-frame').html('<canvas id="preview"></canvas>');

            var MaximgW=300;
            var MaximgH=300;
            if(oImage.width<=300)
                MaximgW=oImage.width;
            if(oImage.height<=300)
                MaximgH=oImage.height;

            var canvas=document.getElementById('preview');
            if(oImage.width>oImage.height)  
            {  
                imageWidth=MaximgW;  
                imageHeight=MaximgH*(oImage.height/oImage.width);  
            }  
            else if(oImage.width<oImage.height)  
            {  
                imageHeight=MaximgH;  
                imageWidth=MaximgW*(oImage.width/oImage.height);  
            }  
            else  
            {  
                imageWidth=MaximgW;  
                imageHeight=MaximgH;  
            }  
            canvas.width=imageWidth;  
            canvas.height=imageHeight;  
            var con=canvas.getContext('2d');  
            con.clearRect(0,0,canvas.width,canvas.height); 
            con.drawImage(oImage,0,0,imageWidth,imageHeight);  

            // initialize Jcrop
            $('#preview').Jcrop({
                minSize: [150, 150], // min crop size
                maxSize: [150, 150], // min crop size
                allowSelect: false,
                allowResize: false,
                aspectRatio : 1, // keep aspect ratio 1:1
                bgFade: true, // use fade effect
                bgOpacity: .3, // fade opacity
                boxWidth:imageWidth,
                boxHeight:imageHeight,
                onChange: updateInfo,
                onSelect: updateInfo,
                onRelease: clearInfo
            }, function(){

                // use the Jcrop API to get the real image size
                var bounds = this.getBounds();
                boundx = bounds[0];
                boundy = bounds[1];

                // Store the Jcrop API in the jcrop_api variable
                jcrop_api = this;
                var sx=Math.floor((imageWidth-150)/2);
                var sy=Math.floor((imageHeight-150)/2);
                jcrop_api.setSelect([sx,sy,sx+150,sy+150]);
            });

            var headparent=document.getElementById('head-frame');
            if(headparent.childNodes){
                for(var i = 0;i < headparent.childNodes.length;i++){
                    var child = headparent.childNodes[i];
                    if(child.className == 'jcrop-holder'){
                        var left=Math.floor((300-imageWidth)/2);
                        var top=Math.floor((300-imageHeight)/2);
                        child.style.left=left+'px';
                        child.style.top=top+'px';
                        var childsel = headparent.childNodes[0];
                        childsel.ondblclick=function(){
                            var crop_canvas=document.getElementById('preview'),  
                                left = $('#w').val(),
                                top =  $('#h').val(),
                                width = 150,  
                                height = 150;
                            var ctx=crop_canvas.getContext("2d");
                            var imgdata=ctx.getImageData(left,top,width,height);
                            if (typeof jcrop_api != 'undefined') {
                                jcrop_api.destroy();
                            }

                            $('#head-frame').html('<canvas id="preview" width="150" height="150" style="margin:75px 75px;"></canvas>');
                            crop_canvas=document.getElementById('preview');
                            ctx=crop_canvas.getContext("2d");
                            ctx.createImageData(150,150);
                            ctx.putImageData(imgdata,0,0);
                            var base64=crop_canvas.toDataURL(oFile.type,0.5).substr(22);  
                            $('#cavportrait').val(base64);
                        };
                        break;
                    };
                };
            };
        };
    };

    // read selected file as DataURL
    oReader.readAsDataURL(oFile);
};

$(function(){
    $('form#portraitform').submit(function(){
        if($('#cavportrait').val()==''){
            alert('请拖动选框进行选择，确认后“双击”选框');
            return false;
        }
    });
});