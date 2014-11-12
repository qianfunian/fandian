<?php

// JCART v1.1
// http://conceptlogic.com/jcart/

// SESSION BASED SHOPPING CART CLASS FOR JCART

/**********************************************************************
Based on Webforce Cart v.1.5
(c) 2004-2005 Webforce Ltd, NZ
http://www.webforce.co.nz/cart/
**********************************************************************/

// JCART
class Msd_Jcart {
	var $total = 0;
	var $itemcount = 0;
	//打包盒总数
	//var $boxcount = 0;
	//打包盒总价
	//var $boxtotal = 0;
	var $items = array();
	//商品最少起订量
	var $minitems = array();
	
	var $itemprices = array();
	var $itemqtys = array();
	var $itemname = array();
	var $vendorguid = array();
	var $vendorname = array();
    //运费
    var $freight = array();
    //距离
    var $distance = array();
    
    var $iteminbox = array();
	var $boxqtys = array();
    var $boxunitprices = array();
    //商铺打包盒总数
    var $vendor_box_count = array();
    //商铺打包盒总价
    var $vendor_box_total = array();
	//设置运费
	//商铺总价
	var $vendor_total = array();
	//菜品单位
	var $unitname = array();
	
	function set_freight($vendor,$freight){
		$this->freight[$vendor] = $freight;
	}
	//设置距离
	function set_distance($vendor,$distance)
	{
		$this->distance[$vendor] = $distance;
	}
	// GET CART CONTENTS
	function get_contents()
	{
		$items = array();
		foreach($this->items as $tmp_item)
		{
			$item = FALSE;
	
			$item['id'] = $tmp_item;
			$item['qty'] = $this->itemqtys[$tmp_item];
			$item['price'] = $this->itemprices[$tmp_item];
			$item['name'] = $this->itemname[$tmp_item];
			$item['vendorguid'] = $this->vendorguid[$tmp_item];
			$item['vendorname'] = $this->vendorname[$tmp_item];
			
			$item['boxqtys'] = $this->boxqtys[$tmp_item];
			$item['boxunitprices'] = $this->boxunitprices[$tmp_item];
			$item['unitname'] = $this->unitname[$tmp_item];
			
			$item['subtotal'] = $item['qty'] * $item['price'];
			
			$items[] = $item;
		}
		return $items;
	}
	function get_vendorsname()
	{
		foreach(array_unique($this->vendorname) as $vname)
		{
			if($vname!='迷你超市')
			{
				return $vname;
				break;
			}
		}
	}
	function get_vendorsguid()
	{
		return array_unique($this->vendorguid);
	}
	// ADD AN ITEM
	function add_item($item_id, $item_qty=1, $item_price, $item_name, $vendor_guid, $vendor_name, $item_in_box, $box_qty, $box_unitprice, $unit_name)
	{
		// VALIDATION
		$valid_item_qty = $valid_item_price = false;
	
		// IF THE ITEM QTY IS AN INTEGER, OR ZERO
		if (preg_match("/^[0-9-]+$/i", $item_qty))
		{
			$valid_item_qty = true;
		}
		// IF THE ITEM PRICE IS A FLOATING POINT NUMBER
		if (is_numeric($item_price))
		{
			$valid_item_price = true;
		}
		
		// ADD THE ITEM
		if ($valid_item_qty !== false && $valid_item_price !== false)
		{
			// IF THE ITEM IS ALREADY IN THE CART, INCREASE THE QTY
			if($this->itemqtys[$item_id] > 0)
			{
				$this->itemqtys[$item_id] += $item_qty;
			}
			// THIS IS A NEW ITEM
			else
			{
				$this->items[] = $item_id;
				$this->minitems[$item_id] = $this->itemqtys[$item_id] = $item_qty;
				$this->itemprices[$item_id] = $item_price;
				$this->itemname[$item_id] = $item_name;
				$this->vendorguid[$item_id] = $vendor_guid;
				$this->vendorname[$item_id] = $vendor_name;
				
				$this->iteminbox[$item_id] = $item_in_box;
				$this->boxqtys[$item_id] = $box_qty;
				$this->boxunitprices[$item_id] = $box_unitprice;
				
				$this->unitname[$item_id] = $unit_name;
			}
			
			
			$this->_update_total();
			return true;
		}
	
		else if	($valid_item_qty !== true)
		{
			$error_type = 'qty';
			return $error_type;
		}
		else if	($valid_item_price !== true)
		{
			$error_type = 'price';
			return $error_type;
		}
	}


	// UPDATE AN ITEM
	function update_item($item_id, $item_qty)
	{
		// IF THE ITEM QTY IS AN INTEGER, OR ZERO
		// UPDATE THE ITEM
		if (preg_match("/^[0-9-]+$/i", $item_qty))
		{
			if($item_qty < 1)
			{
				$this->del_item($item_id);
			}
			elseif($item_qty < $this->minitems[$item_id])
			{
				return 'e';
			}
			else
			{
				$this->itemqtys[$item_id] = $item_qty;
			}
			$this->_update_total();
			return true;
		}else
		{
			return 'q';
		}
	}


	// UPDATE THE ENTIRE CART
	// VISITOR MAY CHANGE MULTIPLE FIELDS BEFORE CLICKING UPDATE
	// ONLY USED WHEN JAVASCRIPT IS DISABLED
	// WHEN JAVASCRIPT IS ENABLED, THE CART IS UPDATED ONKEYUP
	function update_cart()
	{
		// POST VALUE IS AN ARRAY OF ALL ITEM IDs IN THE CART
		if (is_array($_POST['jcart_item_ids']))
		{
			// TREAT VALUES AS A STRING FOR VALIDATION
			$item_ids = implode($_POST['jcart_item_ids']);
		}

		// POST VALUE IS AN ARRAY OF ALL ITEM QUANTITIES IN THE CART
		if (is_array($_POST['jcart_item_qty']))
		{
			// TREAT VALUES AS A STRING FOR VALIDATION
			$item_qtys = implode($_POST['jcart_item_qty']);
		}

		// IF NO ITEM IDs, THE CART IS EMPTY
		if ($_POST['jcart_item_id'])
		{
			// IF THE ITEM QTY IS AN INTEGER, OR ZERO, OR EMPTY
			// UPDATE THE ITEM
			if (preg_match("/^[0-9-]+$/i", $item_qtys) || $item_qtys == '')
			{
				// THE INDEX OF THE ITEM AND ITS QUANTITY IN THEIR RESPECTIVE ARRAYS
				$count = 0;

				// FOR EACH ITEM IN THE CART
				foreach ($_POST['jcart_item_id'] as $item_id)
				{
					// GET THE ITEM QTY AND DOUBLE-CHECK THAT THE VALUE IS AN INTEGER
					$update_item_qty = intval($_POST['jcart_item_qty'][$count]);

					if($update_item_qty < 1)
					{
						$this->del_item($item_id);
					}
					else
					{
						// UPDATE THE ITEM
						$this->update_item($item_id, $update_item_qty);
					}

					// INCREMENT INDEX FOR THE NEXT ITEM
					$count++;
				}
				return true;
			}
		}
		// IF NO ITEMS IN THE CART, RETURN TRUE TO PREVENT UNNECSSARY ERROR MESSAGE
		else if (!$_POST['jcart_item_id'])
		{
			return true;
		}
	}


	// REMOVE AN ITEM
	/*
	GET VAR COMES FROM A LINK, WITH THE ITEM ID TO BE REMOVED IN ITS QUERY STRING
	AFTER AN ITEM IS REMOVED ITS ID STAYS SET IN THE QUERY STRING, PREVENTING THE SAME ITEM FROM BEING ADDED BACK TO THE CART
	SO WE CHECK TO MAKE SURE ONLY THE GET VAR IS SET, AND NOT THE POST VARS

	USING POST VARS TO REMOVE ITEMS DOESN'T WORK BECAUSE WE HAVE TO PASS THE ID OF THE ITEM TO BE REMOVED AS THE VALUE OF THE BUTTON
	IF USING AN INPUT WITH TYPE SUBMIT, ALL BROWSERS DISPLAY THE ITEM ID, INSTEAD OF ALLOWING FOR USER FRIENDLY TEXT SUCH AS 'remove'
	IF USING AN INPUT WITH TYPE IMAGE, INTERNET EXPLORER DOES NOT SUBMIT THE VALUE, ONLY X AND Y COORDINATES WHERE BUTTON WAS CLICKED
	CAN'T USE A HIDDEN INPUT EITHER SINCE THE CART FORM HAS TO ENCOMPASS ALL ITEMS TO RECALCULATE TOTAL WHEN A QUANTITY IS CHANGED, WHICH MEANS THERE ARE MULTIPLE REMOVE BUTTONS AND NO WAY TO ASSOCIATE THEM WITH THE CORRECT HIDDEN INPUT
	*/
	function del_item($item_id)
	{
		$ti = array();
		$this->itemqtys[$item_id] = 0;
		unset($this->vendorguid[$item_id]);
		unset($this->vendorname[$item_id]);
		unset($this->unitname[$item_id]);
		
		foreach($this->items as $item)
		{
			if($item != $item_id)
			{
				$ti[] = $item;
			}
		}
		$this->items = $ti;
		$this->_update_total();
	}


	// EMPTY THE CART
	function empty_cart($vendorguid=0)
	{
		$this->total = 0;
		$this->itemcount = 0;
		$this->items = array();
		$this->itemprices = array();
		$this->itemqtys = array();
		$this->itemname = array();
		$this->vendorguid = array();
		$this->vendorname = array();
		$this->unitname = array();
		
		$this->boxqtys= array();
		$this->boxunitprices = array();
		if($vendorguid){
			$this->freight = array($vendorguid=>$this->freight[$vendorguid]);
			$this->distance = array($vendorguid=>$this->distance[$vendorguid]);
		}else{
			$this->freight = array();
			$this->distance = array();
		}
		$this->vendor_box_count = array();
		$this->vendor_box_total = array();
		$this->vendor_total = array();
	}


	// INTERNAL FUNCTION TO RECALCULATE TOTAL
	function _update_total()
	{
		$this->itemcount = 0;
		$this->vendor_box_count = array();
		$this->vendor_box_total = array();
		$this->vendor_total = array();
		if(sizeof($this->items > 0))
		{
			foreach($this->items as $item)
			{
				$this->vendor_total[$this->vendorguid[$item]] += $this->itemprices[$item] * $this->itemqtys[$item];
				$this->itemcount += $this->itemqtys[$item];
				
				$flag = floor($this->itemqtys[$item]/$this->iteminbox[$item]);
				$boxs = $this->itemqtys[$item]%$this->iteminbox[$item]?$flag+1:$flag;
				
				$this->vendor_box_count[$this->vendorguid[$item]] += $boxs * $this->boxqtys[$item];
			    $this->vendor_box_total[$this->vendorguid[$item]] += $boxs * $this->boxqtys[$item] * $this->boxunitprices[$item];
			}
			$MINIGUID = '68231310-4DD4-4006-9F3B-B3021C17FD46';
			$vendorarr = $this->get_vendorsguid();

			if(count($vendorarr)==1 && in_array($MINIGUID, $vendorarr))
			{
				$this->set_freight($MINIGUID, 8);
				$this->set_distance($MINIGUID, 0);
			}else if(in_array($MINIGUID, $vendorarr))
			{
				$this->set_freight($MINIGUID, 0);
				$this->set_distance($MINIGUID, 0);
			}
		}
	}

	// PROCESS AND DISPLAY CART
	function display_cart($jcart)
	{
		if($_SERVER[REQUEST_METHOD] != 'POST')
		{
			$this->_update_total();
		}
		// JCART ARRAY HOLDS USER CONFIG SETTINGS
		extract($jcart);

		// ASSIGN USER CONFIG VALUES AS POST VAR LITERAL INDICES
		// INDICES ARE THE HTML NAME ATTRIBUTES FROM THE USERS ADD-TO-CART FORM
		foreach($_POST as $key=>$post)
		{
			$str = preg_replace("/(<\/?)(\w+)([^>]*>)/e","",$post);
			$_POST[$key]=$str;
		}
		
		$item_id = $_POST[$item_id];
		$item_qty = $_POST[$item_qty];
		$item_price = $_POST[$item_price];
		$item_name = $_POST[$item_name];
		$vendor_guid = $_POST['my-vendor-guid'];
		$vendor_name = $_POST['my-vendor-name'];
		
		$item_in_box = $_POST['iteminbox'];
		$box_qty = $_POST['my-box-qty'];
		$box_unitprice = $_POST['my-box-unitprice'];
		
		$unit_name = $_POST['my-unitname'];
		
		// ADD AN ITEM
		if ($_POST[$item_add])
		{
			$item_added = $this->add_item($item_id, $item_qty, $item_price, $item_name, $vendor_guid, $vendor_name, $item_in_box, $box_qty, $box_unitprice,$unit_name);
			// IF NOT TRUE THE ADD ITEM FUNCTION RETURNS THE ERROR TYPE
			if ($item_added !== true)
			{
				$error_type = $item_added;
				switch($error_type)
				{
					case 'qty':
						$error_message = $text['quantity_error'];
						break;
					case 'price':
						$error_message = $text['price_error'];
						break;
				}
			}
		}

		// UPDATE A SINGLE ITEM
		// CHECKING POST VALUE AGAINST $text ARRAY FAILS?? HAVE TO CHECK AGAINST $jcart ARRAY
		if ($_POST['jcart_update_item'] == $jcart['text']['update_button'])
		{
			$item_updated = $this->update_item($_POST['item_id'], $_POST['item_qty']);
			
			if ($item_updated !== true)
			{
				switch($item_updated)
				{
					case 'q':
						$error_message = $text['quantity_error'];
						break;
					case 'e':
						$error_message = "您输入的数量小于此商品的最小起订数量";
						break;
				}
			}
		}

		// UPDATE ALL ITEMS IN THE CART
		if($_POST['jcart_update_cart'] || $_POST['jcart_checkout'])
		{
			$cart_updated = $this->update_cart();
			if ($cart_updated !== true)
			{
				$error_message = $text['quantity_error'];
			}
		}

		// REMOVE AN ITEM
		if($_GET['jcart_remove'] && !$_POST[$item_add] && !$_POST['jcart_update_cart'] && !$_POST['jcart_check_out'])
		{
			$this->del_item($_GET['jcart_remove']);
		}

		// EMPTY THE CART
		if($_POST['jcart_empty'])
		{
			$this->empty_cart();
		}

		// DETERMINE WHICH TEXT TO USE FOR THE NUMBER OF ITEMS IN THE CART
		if ($this->itemcount >= 0)
		{
			$text['items_in_cart'] = $text['multiple_items'];
		}
		if ($this->itemcount == 1)
		{
			$text['items_in_cart'] = $text['single_item'];
		}

		// DETERMINE IF THIS IS THE CHECKOUT PAGE
		// WE FIRST CHECK THE REQUEST URI AGAINST THE USER CONFIG CHECKOUT (SET WHEN THE VISITOR FIRST CLICKS CHECKOUT)
		// WE ALSO CHECK FOR THE REQUEST VAR SENT FROM HIDDEN INPUT SENT BY AJAX REQUEST (SET WHEN VISITOR HAS JAVASCRIPT ENABLED AND UPDATES AN ITEM QTY)
 		//$is_checkout = strpos($_SERVER['REQUEST_URI'], $form_action);
 	    $is_config = strpos($_SERVER['REQUEST_URI'], 'confirm');
 		
// 		if ($is_checkout !== false || $_REQUEST['jcart_is_checkout'] == 'true')
// 		{
// 			$is_checkout = true;
// 		}
// 		else
// 		{
// 			$is_checkout = false;
// 		}

		// OVERWRITE THE CONFIG FORM ACTION TO POST TO jcart-gateway.php INSTEAD OF POSTING BACK TO CHECKOUT PAGE
		// THIS ALSO ALLOWS US TO VALIDATE PRICES BEFORE SENDING CART CONTENTS TO PAYPAL
// 		if ($is_checkout == true)
// 		{
// 			$form_action = $path . 'jcart-gateway.php';
// 		}

		// DEFAULT INPUT TYPE
		// CAN BE OVERRIDDEN IF USER SETS PATHS FOR BUTTON IMAGES
		//$input_type = 'submit';

		// IF THIS ERROR IS TRUE THE VISITOR UPDATED THE CART FROM THE CHECKOUT PAGE USING AN INVALID PRICE FORMAT
		// PASSED AS A SESSION VAR SINCE THE CHECKOUT PAGE USES A HEADER REDIRECT
		// IF PASSED VIA GET THE QUERY STRING STAYS SET EVEN AFTER SUBSEQUENT POST REQUESTS
		if ($_SESSION['quantity_error'] == true)
		{
			$error_message = $text['quantity_error'];
			unset($_SESSION['quantity_error']);
		}

		// OUTPUT THE CART

		// IF THERE'S AN ERROR MESSAGE WRAP IT IN SOME HTML
		if ($error_message)
		{
			$error_message = "<p class='jcart-error'>$error_message</p>";
		}

		// DISPLAY THE CART HEADER
		$displayjcart = "<!-- BEGIN JCART -->\n<div id='jcart'>\n";
		$displayjcart .= "<input type='hidden' value='".$this->itemcount."' id='icount' />";
		$displayjcart .= "\t$error_message\n";
		//$displayjcart .= "\t<form method='post' action='$form_action'>\n";
		$displayjcart .= "\t\t<fieldset>\n";
		$displayjcart .= "\t\t\t<table border='1'>\n";
		$displayjcart .= "\t\t\t\t<tr>\n";
		$displayjcart .= "\t\t\t\t\t<th id='jcart-header' colspan='3'>\n";
		$displayjcart .= "\t\t\t\t\t\t<strong id='jcart-title'>" . $text['cart_title'] . "</strong> (" . $this->itemcount . "&nbsp;" . $text['items_in_cart'] .")\n";
		$displayjcart .= "\t\t\t\t\t</th>\n";
		$displayjcart .= "\t\t\t\t</tr>". "\n";
		
		// IF ANY ITEMS IN THE CART
		if($this->itemcount > 0)
		{
			
			foreach($contents = & $this->get_contents() as $i=>$item)
			{
				$key[$i]=$item['vendorname'];
			}
			array_multisort($key,SORT_ASC,$contents);
			
			$vendorflag = null;

			$this->total = 0;
			// DISPLAY LINE ITEMS
			foreach($contents as $item)
			{
				$curfreight = $this->freight[$item['vendorguid']];
				$freight_text = $curfreight? "￥".number_format($this->freight[$item['vendorguid']],2) : "<font style='color:red'>尚未确定，稍后客服将会与您联系确定</font>";

				if($vendorflag != $item['vendorname'])
				{
					$vendortotal = $this->vendor_total[$item['vendorguid']]+$this->freight[$item['vendorguid']]+$this->vendor_box_total[$item['vendorguid']];
					$this->total += $vendortotal;
					//show vendor name
					$displayjcart .="<tr style='color:#FF4400'><td colspan='2'><b>".$item['vendorname']."</b></td><td class='jcart-item-price'><b>￥".number_format($vendortotal,2)."</b></td></tr>";
					if($curfreight!==0)
					{
						$displayjcart .="<tr><td colspan='3'>运费：".$freight_text."</td></tr>";
					}
					//$this->vendor_box_count[$item['vendorguid']]
					if($this->vendor_box_total[$item['vendorguid']])
					{
						$displayjcart .="<tr><td colspan='3'>打包盒总价：￥".number_format($this->vendor_box_total[$item['vendorguid']],2)."</td></tr>";
					}
				}
				$displayjcart .= "\t\t\t\t<tr>\n";

				// ADD THE ITEM ID AS THE INPUT ID ATTRIBUTE
				// THIS ALLOWS US TO ACCESS THE ITEM ID VIA JAVASCRIPT ON QTY CHANGE, AND THEREFORE UPDATE THE CORRECT ITEM
				// NOTE THAT THE ITEM ID IS ALSO PASSED AS A SEPARATE FIELD FOR PROCESSING VIA PHP
				$displayjcart .= "\t\t\t\t\t<td class='jcart-item-qty'>\n";
				if(!$is_config)
				{
					$displayjcart .= "<img align='absmiddle' onclick=\"jiajian('".$item['id']."',0)\" style='cursor:pointer' src='./common/images/jian.gif' />
							<input type='text' size='2' id='jcart-item-id-" . $item['id'] . "' name='jcart_item_qty[ ]' value='" . $item['qty'] . "' />
							<img  style='cursor:pointer' align='absmiddle' onclick=\"jiajian('".$item['id']."',1)\"  src='./common/images/jia.gif' />";
				}else
				{
					$displayjcart .= "\t\t\t\t\t\t".$item['qty']."\n";
				}
				$displayjcart .= "\t\t\t\t\t</td>\n";
				$displayjcart .= "\t\t\t\t\t<td class='jcart-item-name'>\n";
				$displayjcart .= "\t\t\t\t\t\t" . $item['name'] . "<input type='hidden' name='jcart_item_name[ ]' value='" . $item['name'] . "' />\n";
				$displayjcart .= "\t\t\t\t\t\t<input type='hidden' name='jcart_item_id[ ]' value='" . $item['id'] . "' />\n";
				$displayjcart .= "\t\t\t\t\t</td>\n";
				$displayjcart .= "\t\t\t\t\t<td class='jcart-item-price'>\n";
				$displayjcart .= "\t\t\t\t\t\t" . $text['currency_symbol'] . number_format($item['subtotal'],2) . "<input type='hidden' name='jcart_item_price[ ]' value='" . $item['price'] . "' />";
				if(!$is_config)
				{
					$displayjcart .= "\t\t\t\t\t\t<a class='jcart-remove' href='?jcart_remove=" . $item['id'] . "'>" . $text['remove_link'] . "</a>\n";
				}
				$displayjcart .= "\t\t\t\t\t</td>\n";
				$displayjcart .= "\t\t\t\t</tr>\n";
				
				$vendorflag = $item['vendorname'];
			}
		}

		// THE CART IS EMPTY
		else
		{
			$displayjcart .= "\t\t\t\t<tr><td colspan='3' class='empty'>" . $text['empty_message'] . "</td></tr>\n";
		}
		
		if($this->itemcount > 0)
		{
			// DISPLAY THE CART FOOTER
			$displayjcart .= "\t\t\t\t<tr>\n";
			$displayjcart .= "\t\t\t\t\t<th id='jcart-footer' colspan='3'>\n";
	
			// IF THIS IS THE CHECKOUT HIDE THE CART CHECKOUT BUTTON
	// 		if ($is_checkout !== true)
	// 		{
	// 			if ($button['checkout']) { $input_type = 'image'; $src = ' src="' . $button['checkout'] . '" alt="' . $text['checkout_button'] . '" title="" ';	}
	// 			$displayjcart .= "\t\t\t\t\t\t<input type='" . $input_type . "' " . $src . "id='jcart-checkout' name='jcart_checkout' class='jcart-button' value='" . $text['checkout_button'] . "' />\n";
	// 		}
	
			$displayjcart .= "\t\t\t\t\t\t<span id='jcart-subtotal'>" . $text['subtotal'] . ": <strong>" . $text['currency_symbol'] . number_format($this->total,2) . "</strong></span>\n";
			$displayjcart .= "\t\t\t\t\t</th>\n";
			$displayjcart .= "\t\t\t\t</tr>\n";
		}
		$displayjcart .= "\t\t\t</table>\n\n";

// 		$displayjcart .= "\t\t\t<div class='jcart-hide'>\n";
// 		if ($button['update']) { $input_type = 'image'; $src = ' src="' . $button['update'] . '" alt="' . $text['update_button'] . '" title="" ';	}
// 		$displayjcart .= "\t\t\t\t<input type='" . $input_type . "' " . $src ."name='jcart_update_cart' value='" . $text['update_button'] . "' class='jcart-button' />\n";
// 		if ($button['empty']) { $input_type = 'image'; $src = ' src="' . $button['empty'] . '" alt="' . $text['empty_button'] . '" title="" ';	}
// 		$displayjcart .= "\t\t\t\t<input type='" . $input_type . "' " . $src ."name='jcart_empty' value='" . $text['empty_button'] . "' class='jcart-button' />\n";
// 		$displayjcart .= "\t\t\t</div>\n";

		// IF THIS IS THE CHECKOUT DISPLAY THE PAYPAL CHECKOUT BUTTON
		if ($is_checkout == true && false)
		{
			// HIDDEN INPUT ALLOWS US TO DETERMINE IF WE'RE ON THE CHECKOUT PAGE
			// WE NORMALLY CHECK AGAINST REQUEST URI BUT AJAX UPDATE SETS VALUE TO jcart-relay.php
			$displayjcart .= "\t\t\t<input type='hidden' id='jcart-is-checkout' name='jcart_is_checkout' value='true' />\n";

			// SEND THE URL OF THE CHECKOUT PAGE TO jcart-gateway.php
			// WHEN JAVASCRIPT IS DISABLED WE USE A HEADER REDIRECT AFTER THE UPDATE OR EMPTY BUTTONS ARE CLICKED
			$protocol = 'http://'; if (!empty($_SERVER['HTTPS'])) { $protocol = 'https://'; }
			$displayjcart .= "\t\t\t<input type='hidden' id='jcart-checkout-page' name='jcart_checkout_page' value='" . $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . "' />\n";

			// PAYPAL CHECKOUT BUTTON
			if ($button['paypal_checkout'])	{ $input_type = 'image'; $src = ' src="' . $button['paypal_checkout'] . '" alt="' . $text['checkout_paypal_button'] . '" title="" '; }
			$displayjcart .= "\t\t\t<input type='" . $input_type . "' " . $src ."id='jcart-paypal-checkout' name='jcart_paypal_checkout' value='" . $text['checkout_paypal_button'] . "'" . $disable_paypal_checkout . " />\n";
		}
		$displayjcart .= "\t\t</fieldset>\n";
		//$displayjcart .= "\t</form>\n";

		// IF UPDATING AN ITEM, FOCUS ON ITS QTY INPUT AFTER THE CART IS LOADED (DOESN'T SEEM TO WORK IN IE7)
		if ($_POST['jcart_update_item'])
		{
			$displayjcart .= "\t" . '<script type="text/javascript">$(function(){$("#jcart-item-id-' . $_POST['item_id'] . '").focus()});</script>' . "\n";
		}

		$displayjcart .= "</div>\n<!-- END JCART -->\n";
		return  $displayjcart;
	}
}
?>
