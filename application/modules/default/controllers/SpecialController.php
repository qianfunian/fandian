<?php

/**
 * 
 * 特价套餐
 * @author pang
 *
 */

class SpecialController extends Msd_Controller_Default
{
	public function indexAction()
	{	
		if (!Msd_Config::cityConfig()->navi->special_enabled) {
			$this->redirect('');
		}
		
		// USER CONFIG
		include_once('./jcart/jcart-config.php');
		// DEFAULT CONFIG VALUES
		include_once('./jcart/jcart-defaults.php');
		// INITIALIZE JCART AFTER SESSION START
		$cart =& $_SESSION['jcart']; if(!is_object($cart)) $cart = new Msd_Jcart();
		$cart->empty_cart();
	
		//常州小渔村guid
		$VendorGuid= 'B670E7D8-1934-41DD-9CE3-3D225F8E5C0A';
		if ($_COOKIE['coord_guid']) {
			$row = Msd_Waimaibao_Freight::calculate($_COOKIE['coord_guid'], $VendorGuid);
			$distance = (int)$row['distance'];
			$freight  = $row['freight'];
		} else {
			$distance = $freight = null;
		}
			
		$cart->set_freight($VendorGuid,$freight);
		$cart->set_distance($VendorGuid,$distance);
		// PROCESS INPUT AND RETURN UPDATED CART HTML
		$this->view->cart = $cart->display_cart($jcart);
		

		$this->pager_init(array('limit' => 10));
		$items = &Msd_Dao::table('item')->search($this->pager,array('CtgName'=>'商务套餐','Disabled' => '0'));
		
		$this->view->page_links = $this->page_links($this);
		
		$this->view->special_items = $items;
	}
	
	
}