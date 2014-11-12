<?php

// JCART v1.1
// http://conceptlogic.com/jcart/

// INCLUDE CONFIG SO THIS SCRIPT HAS ACCESS USER FIELD NAMES
require_once('jcart-config.php');

// INCLUDE DEFAULT VALUES SINCE WE NEED TO PASS THE VALUE OF THE UPDATE BUTTON BACK TO jcart.php IF UPDATING AN ITEM QTY
// IF NO VALUE IS SET IN CONFIG, THERE MUST BE A DEFAULT VALUE SINCE SIMPLY CHECKING FOR THE VAR ITSELF FAILS
require_once('jcart-defaults.php');

// OUTPUT PHP FILE AS JAVASCRIPT
header('content-type:application/x-javascript');

// PREVENT CACHING
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past

// CONTINUE THE SESSION
session_start();

?>
function jiajian(id,tag)
{
	if(tag==1)
	{
	   var counts = parseInt($('#jcart-item-id-'+id).val())+1;
	}else
	{
	   var counts = parseInt($('#jcart-item-id-'+id).val())-1;
	}
	$('#jcart-item-id-'+id).val(counts);
	var isCheckout = $('#jcart-is-checkout').val();
	updateId = id;
	var updateQty = counts;
	if (updateQty !== '')
	{
		$.post('http://<?php echo $_SERVER[HTTP_HOST].$jcart['path'];?>jcart-relay', { "item_id": updateId, "item_qty": updateQty, "jcart_update_item": 'update', "jcart_is_checkout": isCheckout }, function(data) {$('#jcart').html(data);scall();});
	}
} 

$(document).ready(function () {
	( function( $ ) {
		$.fn.jcartTooltip = function( o, callback ) {
			o = $.extend( {
				content: null,
				follow: true,
				auto: true,
				fadeIn: 0,
				fadeOut: 0,
				appendTip: document.body,
				offsetY: 25,
				offsetX: -10,
				style: {},
				id: 'jcart-tooltip'
			}, o || {});

			if ( !o.style && typeof o.style != "object" )
			{
				o.style = {}; o.style.zIndex = "1000";
			}
			else
			{
				o.style = $.extend( {}, o.style || {});
			}

			o.style.display = "none";
			o.style.position = "absolute";

			var over = {};
			var maxed = false;
			var tooltip = document.createElement( 'div' );

            tooltip.id = o.id;

			for ( var p in o.style ) { tooltip.style[p] = o.style[p]; }

			function fillTooltip( condition ) { if ( condition ) { $( tooltip ).html( o.content ); }}

			fillTooltip( o.content && !o.ajax );
			$( tooltip ).appendTo( o.appendTip );

			return this.each( function() {
				this.onclick = function( ev ) {
					function _execute() {
						var display;
						if ( o.content )
						{
							display = "block";
						}
						else
						{
							display = "none";
						}
						if ( display == "block" && o.fadeIn )
						{
							$( tooltip ).fadeIn( o.fadeIn );

							setTimeout(function(){
								$( tooltip ).fadeOut( o.fadeOut );
							}, 1000);
						}
					}
				_execute();
			};

			this.onmousemove = function( ev ) {
				var e = ( ev ) ? ev : window.event;
				over = this;
				if ( o.follow ) {
					var scrollY = $( window ).scrollTop();
					var scrollX = $( window ).scrollLeft();
					var top = e.clientY + scrollY + o.offsetY;
					var left = e.clientX + scrollX + o.offsetX;
					var maxLeft = $( window ).width() + scrollX - $( tooltip ).outerWidth();
					var maxTop = $( window ).height() + scrollY - $( tooltip ).outerHeight();
					maxed = ( top > maxTop || left > maxLeft ) ? true : false;

					if ( left - scrollX <= 0 && o.offsetX < 0 )
					{
						left = scrollX;
					}
					else if ( left > maxLeft )
					{
						left = maxLeft;
					}
					if ( top - scrollY <= 0 && o.offsetY < 0 )
					{
						top = scrollY;
					}
					else if ( top > maxTop )
					{
						top = maxTop;
					}

					tooltip.style.top = top + "px";
					tooltip.style.left = left + "px";
					}
				};

			this.onmouseout = function() {
				$( tooltip ).css('display', 'none');
			};
		});
	};
	})( jQuery );

	$('.jcart input[name="<?php echo $jcart['item_add'];?>"]').jcartTooltip({content: '<?php echo $jcart['text']['item_added_message'];?>', fadeIn: 500, fadeOut: 350 });

	var cartHasItems = $('td.jcart-item-qty').html();
	if(cartHasItems === null)
	{
	    $('#jcart-paypal-checkout').attr('disabled', 'disabled');
	}

	// HIDE THE UPDATE AND EMPTY BUTTONS SINCE THESE ARE ONLY USED WHEN JAVASCRIPT IS DISABLED
	$('.jcart-hide').remove();
	var isCheckout = $('#jcart-is-checkout').val();
	if (isCheckout !== 'true') { isCheckout = 'false'; }

	// WHEN AN ADD-TO-CART FORM IS SUBMITTED
	$('.jcarts').submit(function(){
		var itemId = $(this).find('input[name=<?php echo $jcart['item_id']?>]').val();
		var itemPrice = $(this).find('input[name=<?php echo $jcart['item_price']?>]').val();
		var itemName = $(this).find('input[name=<?php echo $jcart['item_name']?>]').val();
		var itemQty = $(this).find('input[name=<?php echo $jcart['item_qty']?>]').val();
		var itemAdd = $(this).find('input[name=<?php echo $jcart['item_add']?>]').val();
		var vendorGuid = $(this).find('input[name=my-vendor-guid]').val();
		var vendorName = $(this).find('input[name=my-vendor-name]').val();
		var iteminbox = $(this).find('input[name=my-iteminbox-qty]').val();
		var boxQty = $(this).find('input[name=my-box-qty]').val();
		var boxUnitprice = $(this).find('input[name=my-box-unitprice]').val();
		var unitName = $(this).find('input[name=my-unitname]').val();
		
		$.post('http://<?php echo $_SERVER[HTTP_HOST].$jcart['path'];?>jcart-relay', { "<?php echo $jcart['item_id']?>": itemId, "<?php echo $jcart['item_price']?>": itemPrice, "<?php echo $jcart['item_name']?>": itemName, "<?php echo $jcart['item_qty']?>": itemQty, "<?php echo $jcart['item_add']?>" : itemAdd, "my-vendor-guid" : vendorGuid, "my-vendor-name" : vendorName , "iteminbox" : iteminbox, "my-box-qty" : boxQty, "my-box-unitprice" : boxUnitprice, "my-unitname" : unitName}, function(data) {
			$('#jcart').html(data);
			$('.jcart-hide').remove();
			scall();
		});
		return false;
	});
	
	$('#jcart').keydown(function(e) {
		if(e.which == 13) {
			return false;
		}
	});

	// WHEN A REMOVE LINK IS CLICKED
	$('#jcart a').live('click', function(){
		var queryString = $(this).attr('href');
		queryString = queryString.split('=');
		var removeId = queryString[1];
		$.get('http://<?php echo $_SERVER[HTTP_HOST].$jcart['path'];?>jcart-relay', { "jcart_remove": removeId, "jcart_is_checkout":  isCheckout },function(data) {
			$('#jcart').html(data);
			$('.jcart-hide').remove();
			scall();
		});
		return false;
	});

	$('#jcart input[type="text"]').live('keyup', function(){
		var updateId = $(this).attr('id');
		updateId = updateId.replace("jcart-item-id-",'');
		var updateQty = $(this).val();

		if (updateQty !== '')
		{
			var updateDelay = setTimeout(function(){
				$.post('http://<?php echo $_SERVER[HTTP_HOST].$jcart['path'];?>jcart-relay', { "item_id": updateId, "item_qty": updateQty, "jcart_update_item": '<?php echo $jcart['text']['update_button'];?>', "jcart_is_checkout": isCheckout }, function(data) {
					$('#jcart').html(data);
					$('.jcart-hide').remove();
				});
			}, 1000);
		}
		
		$(this).keydown(function(){
			window.clearTimeout(updateDelay);
		});
	});
});
