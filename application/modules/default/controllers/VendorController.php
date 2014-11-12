<?php

class VendorController extends Msd_Controller_Default
{
	protected $nowServices = array();
	protected $ourServices = array();
	protected $ServiceName = '普通';
	protected $ServiceNames = array();
	protected $vData = array();
	protected $cPrefix = '';
	
	public function init() {
		parent::init ();
		
		$config = &Msd_Config::appConfig ();
		$ss = &Msd_Cache_Loader::Services ();
		$now = time ();
		$h = date ( 'H' );
		
		foreach ( $ss as $s ) {
			$SpanDays = ( int ) $s ['SpanDays'];
			$StartTime = substr ( $s ['StartTime'], 0, 8 );
			$EndTime = substr ( $s ['EndTime'], 0, 8 );
			
			if ($h < 3) {
				$sd = new DateTime ( date ( 'Y-m-d', $now - MSD_ONE_DAY * $SpanDays ) . ' ' . $StartTime );
				$ed = new DateTime ( date ( 'Y-m-d' ) . ' ' . $EndTime );
			} else {
				$sd = new DateTime ( date ( 'Y-m-d' ) . ' ' . $StartTime );
				$ed = new DateTime ( date ( 'Y-m-d', $now + MSD_ONE_DAY * $SpanDays ) . ' ' . $EndTime );
			}
			
			$service = array (
					'name' => $s ['SrvName'],
					'start' => $sd->getTimestamp (),
					'end' => $ed->getTimestamp () 
			);
			$service ['s'] = date ( 'Y-m-d H:i:s', $service ['start'] );
			$service ['e'] = date ( 'Y-m-d H:i:s', $service ['end'] );
			if ($now > $sd->getTimestamp () && $now < $ed->getTimestamp ()) {
				$this->nowServices [] = $service;
				$this->ServiceNames [] = $service ['name'];
			}
			
			$this->ourServices [$s ['SrvName']] = $service;
		}
		
		$this->view->ourServices = $this->ourServices;
		$this->view->nowServices = $this->nowServices;
		$this->view->ServiceName = $this->ServiceName = trim ( urldecode ( $this->getRequest ()->getParam ( 'service', $config->db->n->service_name->normal ) ) );
	}	
	
	public function __call($method, $params) {
		$r = $this->getRequest ()->getParams ();
		$cConfig = &Msd_Config::cityConfig ();
		$VendorName = trim ( urldecode ( trim ( $r ['action'] ) ) );
		
		$Vendor = &Msd_Dao::table ( 'vendor' )->getByName ( $VendorName, $cConfig->db->guids->root_region );
		
		if (! $Vendor) {
			try {
				$Vendor = &Msd_Dao::table ( 'vendor' )->getByName ( Msd_Iconv::g2u ( $VendorName ), $cConfig->db->guids->root_region );
				if (! $Vendor ['VendorGuid']) {
					$Vendor = array ();
				}
			} catch ( Exception $e ) {
				Msd_Log::getInstance ()->vendor ( $e->getMessage () . "\n" . $e->getTraceAsString () );
			}
		}
		
		if ($Vendor && ! $Vendor ['Disabled']) {
			$this->vData = $Vendor;
			$this->showAction ( $Vendor ['VendorGuid'] );
			echo $this->view->render ( 'vendor/show.phtml' );
		} else {
			$this->_helper->getHelper ( 'Redirector' )->gotoUrl ( $this->scriptUrl . 'vendor' );
		}
	}
	
	public function jcartRelayAction() {
		// USER CONFIG
		include_once ('./jcart/jcart-config.php');
		// DEFAULT CONFIG VALUES
		include_once ('./jcart/jcart-defaults.php');
		
		// INITIALIZE JCART AFTER SESSION START
		$cart = & $_SESSION ['jcart'];
		if (! is_object ( $cart ))
			$cart = new Msd_Jcart ();
		if ($this->_request->getParam ( 'empty' ) == 1) {
			$cart->empty_cart ( $this->_request->getParam ( 'vendorguid' ) );
		}
		echo $cart->display_cart ( $jcart );
		exit ();
	}
	
	public function updateFreightAction() {
		include_once ('./jcart/jcart-config.php');
		include_once ('./jcart/jcart-defaults.php');
		$cart = & $_SESSION ['jcart'];
		if (! is_object ( $cart ))
			$cart = new Msd_Jcart ();
		$vendors = $cart->get_vendorsguid ();
		if ($_COOKIE ['coord_guid'] && $_COOKIE['longitude'] && $_COOKIE['latitude']) {
			foreach ( $vendors as $vendorguid ) {
				$vaTable = Msd_Dao::table('vendor/address');
				$distance =$vaTable->getDistance($vendorguid, $_COOKIE['longitude'], $_COOKIE['latitude']);
				$freight = Msd_Waimaibao_Freight::calculate ( $distance, $vendorguid );
				$cart->set_freight ( $vendorguid, $freight );
				$cart->set_distance ( $vendorguid, $distance );
			}
		} else {
			foreach ( $vendors as $vendorguid ) {
				$distance = $freight = null;
				$cart->set_freight ( $vendorguid, $freight );
				$cart->set_distance ( $vendorguid, $distance );
			}
		}
		
		echo $cart->display_cart ( $jcart );
		exit ();
	}
	
	public function showAction($VendorGuid = 0) {
		$config = &Msd_Config::appConfig ();
		$cacher = &Msd_Cache_Remote::getInstance ();
		$cityConfig = &Msd_Config::cityConfig ();
		
		$MiniMarketGuid = $cityConfig->db->guids->mini_market;
		$keyword = trim ( urldecode ( $this->getRequest ()->getParam ( 'keyword', '' ) ) );
		
		if ($VendorGuid) {
			Msd_Dao::table ( 'vendor/extend' )->increase ( 'Views', $VendorGuid );
			
			include ('./jcart/jcart-config.php');
			include ('./jcart/jcart-defaults.php');
			$cart = & $_SESSION ['jcart'];
			if (! is_object ( $cart ))
				$cart = new Msd_Jcart ();
			
			if ($this->getRequest ()->getParam ( 'empty', 0 ) || ! is_array ( $cart->freight )) {
				$cart->empty_cart ();
			}
			if ($_COOKIE ['phone']) {
				$this->view->lsas = Msd_Lsadds::getlsadds ( 0, $_COOKIE ['phone'] );
			}
			
			if ($_COOKIE ['coord_guid'] && $_COOKIE['longitude'] && $_COOKIE['latitude']) {
				//根据商家经纬度获取距离，然后根据距离获取运费
				$vaTable = Msd_Dao::table('vendor/address');
				$distance =$vaTable->getDistance($VendorGuid, $_COOKIE['longitude'], $_COOKIE['latitude']);
				$freight = Msd_Waimaibao_Freight::calculate ( $distance, $VendorGuid );
			} else {
				$distance = $freight = null;
			}
			
			// $vendors = $cart->get_vendorsguid();
			// if(!(in_array($VendorGuid, $vendors) || $vendors ==
			// null||(count($vendors) == 1 &&in_array($MiniMarketGuid,
			// $vendors))))
			// {
			// $this->view->pvname = $cart->get_vendorsname();
			// $this->render('info');
			// exit;
			// }
			
			$cart->set_freight ( $VendorGuid, $freight );
			$cart->set_distance ( $VendorGuid, $distance );
			$this->view->cart = $cart->display_cart ( $jcart );
			
			$vendor = &Msd_Waimaibao_Vendor::getInstance ( $VendorGuid );
			$detail = &Msd_Waimaibao_Vendor::Detail ( $VendorGuid, $this->ServiceName );
			$basic = $vendor->basic ();
			$extend = $vendor->extend ();
			
			foreach ( $this->view->MetaPara as $key => $val ) {
				$val = str_replace ( '{VENDOR_NAME}', Msd_Waimaibao_Vendor::FilterVendorName ( $basic ['VendorName'] ), $val );
				$val = str_replace ( '{VENDOR_DESCRIPTION}', $basic ['Remark'], $val );
				$this->view->MetaPara [$key] = $val;
			}
			
			$this->sess->set ( 'last_vendor_name', $basic ['VendorName'] );
			
			$this->view->keyword = $keyword;
			
			$this->view->basic = $basic;
			$this->view->extend = $extend;
			
			$this->view->isFavorited = $this->member->uid () ? Msd_Dao::table ( 'favorited/vendors' )->isFavorited ( $VendorGuid, $this->member->uid () ) : false;
			
			$this->view->miniMarket = array ();
			$this->view->miniMarket = &Msd_Cache_Loader::MiniMarket ();
			
			$this->view->vendor_items = array ();
			$ItemGuids = array ();
			$detail ['items'] || $detail ['items'] = array ();
			$tuanGuid = $cityConfig->db->guids->category->tuan;
			
			foreach ( $detail ['items'] as $item ) {
				if ($item ['CtgGuid'] != $tuanGuid && $item ['ServiceName'] == $this->ServiceName) {
					$ItemGuids [] = $item ['ItemGuid'];
					$this->view->vendor_items [] = array (
							'ItemGuid' => $item ['ItemGuid'],
							'ItemName' => $item ['ItemName'],
							'UnitPrice' => $item ['UnitPrice'],
							'UnitName' => $item ['UnitName'],
							'ItemQty' => $item ['ItemQty'],
							'BoxQty' => $item ['BoxQty'],
							'BoxUnitPrice' => $item ['BoxUnitPrice'],
							'MinOrderQty' => $item ['MinOrderQty'] ? $item ['MinOrderQty'] : 1,
							'VendorGuid' => $item ['VendorGuid'] 
					);
				}
			}
			
			$this->view->market_itemids = $this->view->market_items = array ();
			foreach ( $this->view->miniMarket ['items'] as $item ) {
				$this->view->market_itemids [] = $item ['ItemGuid'];
				
				$this->view->market_items [] = array (
						'ItemGuid' => $item ['ItemGuid'],
						'ItemName' => $item ['ItemName'],
						'UnitPrice' => $item ['UnitPrice'],
						'UnitName' => $item ['UnitName'],
						'ItemQty' => $item ['ItemQty'],
						'BoxQty' => $item ['BoxQty'],
						'BoxUnitPrice' => $item ['BoxUnitPrice'],
						'MinOrderQty' => $item ['MinOrderQty'] ? $item ['MinOrderQty'] : 1,
						'VendorGuid' => $item ['VendorGuid'] 
				);
			}
			
			$batchIsFavorited = array ();
			if ($this->member->uid ()) {
				$result = &Msd_Dao::table ( 'favorited/items' )->batchIsFavorited ( $ItemGuids, $this->member->uid () );
				foreach ( $result as $row ) {
					$batchIsFavorited [] = $row ['ItemGuid'];
				}
			}
			
			$this->view->batchIsFavorited = $batchIsFavorited;
			
			$marketPreparedItems = array ();
			if ($ods ['items'] [$MiniMarketGuid] ['items']) {
				foreach ( $ods ['items'] [$MiniMarketGuid] ['items'] as $item ) {
					$marketPreparedItems [] = $item ['ItemGuid'];
				}
			}
			$this->view->marketPreparedItems = $marketPreparedItems;
			
			// 招牌菜 start
			$this->view->sign = array ();
			$signGuids = array ();
			if ($detail ['sign']) {
				foreach ( $detail ['sign'] as $row ) {
					$signGuids [] = $row ['ItemGuid'];
					$this->view->sign [] = $row;
				}
			}
			
			if (count ( $this->view->sign ) < 7) {
				$readyGuids = array_diff ( $ItemGuids, $signGuids );
				$remain = 7 - count ( $this->view->sign );
				
				if (count ( $readyGuids ) > $remain) {
					$readyGuids = array_rand ( $readyGuids, $remain );
				}
				
				if (is_array ( $readyGuids )) {
					foreach ( $readyGuids as $guid ) {
						$this->view->sign [] = $detail ['items'] [$guid];
					}
				}
			}
			
			
			// 营业时间
			$this->view->service_times = $detail ['serviceTimes'];
			$this->view->vendor_in_service = Msd_Waimaibao_Vendor::nInService ( $VendorGuid );
			
			$this->view->st = array ();
			if (is_array ( $this->view->service_times ) && count ( $this->view->service_times )) {
				foreach ( $this->view->service_times as $st ) {
					$this->view->st [] = array (
							'start' => substr ( $st ['StartTime'], 0, 8 ),
							'end' => substr ( $st ['EndTime'], 0, 8 ) 
					);
				}
			}
			
			// 商家营业时间
			$this->view->ServiceTime = Msd_Dao::table ( 'vendor' )->getServiceTimeString ( $VendorGuid );
		} else {
			throw new Msd_Exception ( '参数不正确' );
		}
		
		$this->view->detail = $detail;
		$this->view->mini_market_guid = $MiniMarketGuid;
		$this->view->vendorguid = $VendorGuid;
	}
	
	public function categoryAction()
	{
		$this->indexAction();
		echo $this->view->render('index.phtml');
	}
	
	public function viewVendorAction()
	{
		$viewedVendors = $this->sess->get('viewed_vendors');
		$viewedVendors || $viewedVendors = array();
		
		$this->view->viewedVendors = $viewedVendors;
	}
	
	public function orderedVendorAction()
	{
		$orderedVendors = array();
		$oTable  = &Msd_Dao::table('order');
		$cacher  = &Msd_Cache_Remote::getInstance();
		if ($this->member->uid()) {
			$ckey = 'ovs_'.$this->member->uid();
			$orderedVendors = $cacher->get($ckey);
	
			if (!$orderedVendors) {
				$orderedVendors = &$oTable->orderedVendorsByCustGuid($this->member->uid());
				$cacher->set($ckey, $orderedVendors, MSD_ONE_DAY);
			}
		} else {
			$OrderGuids = array();
			$ogs = explode(',', trim(urldecode($this->sess->get('oids'))));
			foreach ($ogs as $guid) {
				if (Msd_Validator::isGuid($guid)) {
					$OrderGuids[] = $guid;
				}
			}

			if (count($OrderGuids)>0) {
				$orderedVendors = &$oTable->orderedVendorsByGuids($OrderGuids);
			}
		}

		$this->view->orderedVendors = $orderedVendors;
	}
	public function searchAction()
	{
		$keyword = trim(urldecode($this->getRequest()->getParam('keyword', '')));
		$cConfig = &Msd_Config::cityConfig();
		
		$aconfig = &Msd_Config::appConfig();
		$ctgs = &Msd_Waimaibao_Category::Vendor();
		$this->view->categories = array();
		
		foreach ($ctgs as $ctg) {
			if ($ctg['CtgStdName']==$aconfig->db->n->ctg_std_name->vendor && $ctg['CtgName']==$keyword) {
				$this->_helper->getHelper('Redirector')->setCode(301)->gotoUrl($this->scriptUrl.'vendor/index/category/'.$ctg['CtgName']);
				exit(0);
			}
		}
		
		$Vendor = &Msd_Dao::table('vendor')->getByName($keyword,$cConfig->root_region);
		if ($Vendor['VendorGuid']) {
			$this->_helper->getHelper('Redirector')->setCode(301)->gotoUrl($this->scriptUrl.'vendor/'.$Vendor['VendorName']);
			exit(0);
		}
		
		$this->indexAction();
		
		echo $this->view->render('index.phtml');
		exit(0);
	}

	public function indexAction() {
		$this->pager_init ( array (
				'limit' => 10 
		) );
		
		$config = &Msd_Config::appConfig ();
		$cConfig = &Msd_Config::cityConfig ();
		$table = &Msd_Dao::table ( 'vendor' );
		$oTable = &Msd_Dao::table ( 'order' );
		
		$params = $sort = array ();
		$getParams = $this->getRequest ()->getParams ();
		$Category = trim ( urldecode ( $getParams ['category'] ) );
		$Keyword = trim ( urldecode ( $getParams ['keyword'] ) );
		$Distance = intval ( $getParams ['distance'] ) > 0 ? intval ( $getParams ['distance'] ) : 3000;
		$_sort = strtolower ( trim ( $getParams ['sort'] ) );
		$_sort = ! empty ( $_sort ) ? $_sort : 'hotrate';
		
		$this->view->cName = $cName = $Category;
		$sort ['InService'] = 'DESC';
		switch ($_sort) {
			case 'distance' :
				$sort ['Distance'] = 'ASC';
				break;
			default :
				$sort ['HotRate'] = 'DESC';
				break;
		}
		
		//$this->_helper->getHelper ( 'Redirector' )->setCode ( 301 )->gotoUrl ( $this->scriptUrl . 'vendor/index/service/' . $config->db->n->service_name->night );
		
		if ($this->member->uid ()) {
			$params ['CustGuid'] = $this->member->uid ();
			$sort ['Favorited'] = 'DESC';
		}
		
		$this->view->categories = array ();
		$ctgs = &Msd_Waimaibao_Category::Vendor ();
		foreach ( $ctgs as $ctg ) {
			if ($ctg ['CtgStdName'] == $config->db->n->ctg_std_name->vendor && ! in_array ( $ctg ['CtgName'], $this->view->categories )) {
				$this->view->categories [$ctg ['CtgGuid']] = $ctg ['CtgName'];
			}
		}
		
		$CoordGuid = $_COOKIE ['coord_guid'];
		if (Msd_Validator::isGuid ( $CoordGuid )) {
			$params ['CoordGuid'] = $CoordGuid;
			$params ['Distance'] = '0,' . $Distance;
			
			if ($_COOKIE ['longitude'] > 10 && $_COOKIE ['latitude'] > 10) {
				$params ['Longitude'] = $_COOKIE ['longitude'];
				$params ['Latitude'] = $_COOKIE ['latitude'];
				
				$sort ['Distance'] = 'ASC';
			}
		} else if (isset ( $sort ['Distance'] )) {
			unset ( $sort ['Distance'] );
		}
		
		if (strlen ( $Keyword )) {
			$Keyword = str_replace ( '/', '', $Keyword );
			$params ['VendorName'] = $Keyword;
		}
		
		$params ['Disabled'] = '0';
		$params ['CtgName'] = $cName;
		$params ['exclude_mini'] = true;
		$params ['service'] = $this->ServiceName;
		$params ['AreaGuid'] = $cConfig->db->guids->area->toArray ();
		$params ['CityId'] = $cConfig->city_id;
		
		$regions = &Msd_Cache_Loader::Regions ();
		$this->view->regions = array ();
		if ($regions) {
			foreach ( $regions as $region ) {
				$this->view->regions [$region ['RegionGuid']] = $region ['RegionName'];
			}
		}
		
		$rows = $table->newsearch ( $this->pager, $params, $sort );
		
		if ($Distance == 3000 && count ( $rows ) <= 0) {
			$this->_helper->getHelper ( 'Redirector' )->gotoUrl ( $this->view->Vendorurl ( array (
					'keyword' => $Keyword,
					'distance' => 5000,
					'category' => $cName,
					'sort' => $_sort 
			) ) );
		} else if ($Distance == 5000 && count ( $rows ) <= 0) {
			$this->_helper->getHelper ( 'Redirector' )->gotoUrl ( $this->view->Vendorurl ( array (
					'keyword' => $Keyword,
					'distance' => 99999,
					'category' => $cName,
					'sort' => $_sort 
			) ) );
		}
		
		$this->view->distance = $Distance;
		$this->view->rows = $rows;
		$this->view->page_links = $this->page_links ( $this->view );
		$this->view->data = array ();
		$this->view->request = $_REQUEST;
		$this->view->order_data = $this->order_data;
		$this->view->keyword = $Keyword;
		$this->view->server_categories = Msd_Cache_Loader::ServerCategories ();
		
		if (strlen ( $Keyword )) {
			$lastKeyword = $this->sess->get ( 'last_keyword' );
			
			if (($lastKeyword && $lastKeyword != $Keyword) || ! $lastKeyword) {
				Msd_Dao::table ( 'searchlogs' )->insert ( array (
						'Keywords' => $Keyword,
						'CustGuid' => $this->sess->get ( 'uid' ),
						'Results' => $this->pager ['total'],
						'CityId' => $cConfig->city_id 
				) );
				$this->sess->set ( 'last_keyword', $Keyword );
			}
		}
	}
}

