/**
 * Created by Administrator on 2015-11-06.
 */
$(function () {
    $(window).resize(function () {
        $("#main-container").css("min-height", $(window).height() - 343);
    }).resize();
});




// function display() {
//     document.getElementById("xiangqing1").style.display = "block";
// }
// function disappear() {
//     document.getElementById("xiangqing1").style.display = "none";
// }

// function display2() {
//     document.getElementById("xiangqing2").style.display = "block";
// }
// function disappear2() {
//     document.getElementById("xiangqing2").style.display = "none";
// }

// function display3() {
//     document.getElementById("xiangqing3").style.display = "block";
// }
// function disappear3() {
//     document.getElementById("xiangqing3").style.display = "none";
// }


// function display4() {
//     document.getElementById("xiangqing4").style.display = "block";
// }
// function disappear4() {
//     document.getElementById("xiangqing4").style.display = "none";
// }


// function display5() {
//     document.getElementById("xiangqing5").style.display = "block";
// }
// function disappear5() {
//     document.getElementById("xiangqing5").style.display = "none";
// }

function display15() {
    document.getElementById("store-fixed").style.display = "block";
}
function disappear15() {
    document.getElementById("store-fixed").style.display = "none";
}



//图片tag
function pricedisplay(id) {
    $('a#'+id).children('.wares-flow').children('.price').show();
    $('a#'+id).children('.wares-flow').children('.caveat').css('bottom','160px');
}
function pricedisappear(id) {
    $('a#'+id).children('.wares-flow').children('.price').hide();
    $('a#'+id).children('.wares-flow').children('.caveat').css('bottom','60px');
}
