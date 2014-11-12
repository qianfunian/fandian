<?php
/*
 * 年夜饭业务
 */

class NewYearController extends Msd_Controller_Default
{
	
	public function indexAction()
	{
		
		include('./jcart/jcart-config.php');
		include('./jcart/jcart-defaults.php');
		$cart =& $_SESSION['jcart']; if(!is_object($cart)) $cart = new Msd_Jcart();
		$cart->freight = 0;
		$cart->empty_cart();
		$config = &Msd_Config::appConfig();
		$cityConfig = &Msd_Config::cityConfig();
		
		$pager = array(
				'page' => 1,
				'limit' => 999,
				'skip' => 0
		);
		$rows = &Msd_Dao::table('item')->search($pager, array(
				'ServiceName' => $config->db->n->service_name->newyear,
				'Disabled' => 0,
				'passby_pager' => true,
				'CityId' => $cityConfig->city_id
		));
		foreach($rows as $row){
			$vendor[$row['VendorName']][] = $row;
		}
		
		
		krsort($vendor);
		
		$this->view->vendor = $vendor;
		$this->view->cart = $cart->display_cart($jcart);
	}
	
}