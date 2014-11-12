$(document).ready(function () {
	/*enter input*/
	$("#stepadd").keydown(function(e){
    var curKey = e.which;
    if(curKey == 13){
        $("#gonext").click();
        return false;
      }
    });
    $("#search_keyword").keydown(function(e){
    var curKey = e.which;
    if(curKey == 13){
        $("#idx_search_input").click();
        return false;
      }   
    });
    /*slideshow*/
    $("#banner").KinSlideshow();
    /*tab*/
    $(".tab_content").hide();
    $("ul.tabs li:first").addClass("active").show();
    $(".tab_content:first").show();
    $("ul.tabs li").click(function () {
        $("ul.tabs li").removeClass("active");
        $(this).addClass("active");
        $(".tab_content").hide();
        var activeTab = $(this).find("a").attr("href");
        $(activeTab).toggle();
        return false;
    });
	/*slideshow*/
    $("#banner2").KinSlideshow();
    /*tab*/
    $(".free").hide();
    $(".diy-tabs li:first").addClass("active").show();
    $(".free:first").show();
    $(".diy-tabs li").click(function () {
        $(".diy-tabs li").removeClass("active");
        $(this).addClass("active");
        $(".free").hide();
        var activeTab = $(this).find("a").attr("href");
        $(activeTab).fadeIn();
        return false;
    });
    /*input*/
    $("input[focucmsg]").each(function () {
        $(this).val($(this).attr("focucmsg"));

        $(this).focus(function () {
            if ($(this).val() == $(this).attr("focucmsg")) {
                $(this).val('');

            }
        });
        $(this).blur(function () {
            if (!$(this).val()) {
                $(this).val($(this).attr("focucmsg"));

            }
        });
    });
    /*top*/
    $().UItoTop({ easingType:'easeOutQuart' });
    /*megamenu*/
    $("#megamenu").dcMegaMenu({
        rowItems:'3',
        speed:'fast',
        effect:'fade'
    });
	
	$(".select-city").click(function (e) {
        if ($("#sle-area").css("display") == "none") {
            e.stopPropagation();
            $("#sle-area").show();
        }
        else {
            $("#sle-area").hide();
        }
    });
    $(document).click(function (event) {
        $("#sle-area").hide();
    });
	 $(".area ul li").hover(function () {
		
        $(this).find(".areashow").show();
		$(this).addClass("lihover");
    }, function () {
        $(this).find(".areashow").hide();
		$(this).removeClass("lihover");
    });
	
	$(".all-shops li").hover(function(){
		$(this).addClass("img-hover");
		},function(){$(this).removeClass("img-hover");});
		
	$(".shop-index .li-first ul li").hover(function(){
		$(this).addClass("li-hover");
		},function(){$(this).removeClass("li-hover");});
	$(".all-group-shop .left,.all-group-shop .right").hover(function(){
		$(this).addClass("tuanhover");
		},function(){$(this).removeClass("tuanhover");});
	$("button.button").hover(function(){
		$(this).addClass("buttonhover");
		},function(){$(this).removeClass("buttonhover");});
});
	function autoScroll(obj) {
    $(obj).find(".list").animate({
        marginTop:"-25px"
    }, 500, function () {
        $(this).css({marginTop:"0px"}).find("li:first").appendTo(this);
    })
}
$(function () {
    setInterval('autoScroll(".scroll")', 3000)
});

