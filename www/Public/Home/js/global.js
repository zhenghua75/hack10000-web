/**
 * Created by Administrator on 2015-11-10.
 */






//  悬浮框
function leftbar1() {
    document.getElementById("appdownload-hover").style.display = "block";
}
function leftbarer1() {
    document.getElementById("appdownload-hover").style.display = "none";
}

function leftbar2() {
    document.getElementById("advisory-hover").style.display = "block";
}
function leftbarer2() {
    document.getElementById("advisory-hover").style.display = "none";
}

function showleft() {
    if (document.getElementById('advisory-hover').style.display == 'block')
        document.getElementById('advisory-hover').style.display = 'none';
    else
        document.getElementById('advisory-hover').style.display = 'block'
}
function showleft2() {
    if (document.getElementById('appdownload-hover').style.display == 'block')
        document.getElementById('appdownload-hover').style.display = 'none';
    else
        document.getElementById('appdownload-hover').style.display = 'block'
}


//  勋章滑过
function medal() {
    document.getElementById("medal-display").style.display = "block";
}
function medaler() {
    document.getElementById("medal-display").style.display = "none";
}
function medal2() {
    document.getElementById("medal-display2").style.display = "block";
}
function medaler2() {
    document.getElementById("medal-display2").style.display = "none";
}
function medal3() {
    document.getElementById("medal-display3").style.display = "block";
}
function medaler3() {
    document.getElementById("medal-display3").style.display = "none";
}
function medal4() {
    document.getElementById("medal-display4").style.display = "block";
}
function medaler4() {
    document.getElementById("medal-display4").style.display = "none";
}
function medal5() {
    document.getElementById("medal-display5").style.display = "block";
}
function medaler5() {
    document.getElementById("medal-display5").style.display = "none";
}
function medal6() {
    document.getElementById("medal-display6").style.display = "block";
}
function medaler6() {
    document.getElementById("medal-display6").style.display = "none";
}
function medal7() {
    document.getElementById("medal-display7").style.display = "block";
}
function medaler7() {
    document.getElementById("medal-display7").style.display = "none";
}
function medal8() {
    document.getElementById("medal-display8").style.display = "block";
}
function medaler8() {
    document.getElementById("medal-display8").style.display = "none";
}
function medal9() {
    document.getElementById("medal-display9").style.display = "block";
}
function medaler9() {
    document.getElementById("medal-display9").style.display = "none";
}
function medal10() {
    document.getElementById("medal-display10").style.display = "block";
}
function medaler10() {
    document.getElementById("medal-display10").style.display = "none";
}
function medal11() {
    document.getElementById("medal-display11").style.display = "block";
}
function medaler11() {
    document.getElementById("medal-display11").style.display = "none";
}
function medal12() {
    document.getElementById("medal-display12").style.display = "block";
}
function medaler12() {
    document.getElementById("medal-display12").style.display = "none";
}


//  弹出
$(function () {
    $('#doc-prompt-toggle').on('click', function () {
        $('#login-window').modal({
            relatedTarget: this,
            onConfirm: function (e) {
            }
        });
    });
});
$(function () {
    $('#doc-prompt-toggle2').on('click', function () {
        $('#login-window').modal({
            relatedTarget: this,
            onConfirm: function (e) {
            }
        });
    });
});
$(function () {
    $('#doc-prompt-toggle3').on('click', function () {
        $('#login-window').modal({
            relatedTarget: this,
            onConfirm: function (e) {
            }
        });
    });
});
$(function () {
    $('#doc-prompt-toggle4').on('click', function () {
        $('#login-window').modal({
            relatedTarget: this,
            onConfirm: function (e) {
            }
        });
    });
});
$(function () {
    $('#doc-prompt-toggle5').on('click', function () {
        $('#login-window').modal({
            relatedTarget: this,
            onConfirm: function (e) {
            }
        });
    });
});
$(function () {
    $('#doc-prompt-toggle6').on('click', function () {
        $('#publish-window').modal({
            relatedTarget: this,
            closeViaDimmer:false,
            onConfirm: function (e) {
            }
        });
    });
});
$(function () {
    $('#doc-prompt-toggle7').on('click', function () {
        $('#search-window').modal({
            relatedTarget: this,
            onConfirm: function (e) {
            }
        });
    });
});
$(function () {
    $('#doc-prompt-toggle8').on('click', function () {
        $('#publish-window').modal({
            relatedTarget: this,
            closeViaDimmer:false,
            onConfirm: function (e) {
            }
        });
    });
});
$(function () {
    $('#doc-prompt-toggle9').on('click', function () {
        $('#publish-window').modal({
            relatedTarget: this,
            closeViaDimmer:false,
            onConfirm: function (e) {
            }
        });
    });
});
$(function () {
    $('#doc-prompt-toggle10').on('click', function () {
        $('#publish-window').modal({
            relatedTarget: this,
            closeViaDimmer:false,
            onConfirm: function (e) {
            }
        });
    });
});
$(function () {
    $('#doc-prompt-toggle11').on('click', function () {
        $('#publish-window').modal({
            relatedTarget: this,
            closeViaDimmer:false,
            onConfirm: function (e) {
            }
        });
    });
});

//    日历
$(function() {
    var startDate = new Date(2015, 10, 20);
    var endDate = new Date(2015, 10, 25);
    var $alert = $('#my-alert');
    $('#my-start').datepicker().
        on('changeDate.datepicker.amui', function(event) {
            if (event.date.valueOf() > endDate.valueOf()) {
                $alert.find('p').text('开始日期应小于结束日期！').end().show();
            } else {
                $alert.hide();
                startDate = new Date(event.date);
                $('#my-startDate').text($('#my-start').data('date'));
            }
            $(this).datepicker('close');
        });

    $('#my-end').datepicker().
        on('changeDate.datepicker.amui', function(event) {
            if (event.date.valueOf() < startDate.valueOf()) {
                $alert.find('p').text('结束日期应大于开始日期！').end().show();
            } else {
                $alert.hide();
                endDate = new Date(event.date);
                $('#my-endDate').text($('#my-end').data('date'));
            }
            $(this).datepicker('close');
        });

    var shuzhi=10000;
    var bianliang = Math.random()*1000 - 500;
    var num = shuzhi+ Math.floor(bianliang);
    $('p#zi-xun').html("当前咨询人数："+num);
});