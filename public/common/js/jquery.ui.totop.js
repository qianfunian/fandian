(function(A){A.fn.UItoTop=function(D){var F={text:"To Top",min:200,inDelay:600,outDelay:400,containerID:"toTop",containerHoverID:"toTopHover",scrollSpeed:1200,easingType:"linear"},E=A.extend(F,D),C="#"+E.containerID,B="#"+E.containerHoverID;A("body").append('<a href="#" id="'+E.containerID+'">'+E.text+"</a>");A(C).hide().on("click.UItoTop",function(){A("html, body").animate({scrollTop:0},E.scrollSpeed,E.easingType);A("#"+E.containerHoverID,this).stop().animate({opacity:0},E.inDelay,E.easingType);return false}).prepend('<span id="'+E.containerHoverID+'"></span>').hover(function(){A(B,this).stop().animate({opacity:1},600,"linear")},function(){A(B,this).stop().animate({opacity:0},700,"linear")});A(window).scroll(function(){var G=A(window).scrollTop();if(typeof document.body.style.maxHeight==="undefined"){A(C).css({position:"absolute",top:G+A(window).height()-50})}if(G>E.min){A(C).fadeIn(E.inDelay)}else{A(C).fadeOut(E.Outdelay)}})}})(jQuery);