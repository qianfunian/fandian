<?php

class Fadmin_VendorController extends Msd_Controller_Fadmin
{
	protected $bizAreas = array();
	
	public function init()
	{
		parent::init();
		
		$this->auth('vendor');
		
		$this->bizAreas = &Msd_Waimaibao_Vendor::BizAreas();
	}
	
	public function doeditAction()
	{
		$VendorGuid = $this->getRequest()->getParam('VendorGuid', '');

		$p = &$_POST;
		$error = array();

		$toUpdate = array(
			'Description' => $this->getRequest()->getParam('Description', ''),
			'Views' => (int)$this->getRequest()->getParam('Views', 0),
			'Favorites' => (int)$this->getRequest()->getParam('Favorites', 0),
			'AverageCost' => (int)$this->getRequest()->getParam('AverageCost', 0),
			'HotRate' => (int)$this->getRequest()->getParam('HotRate', 1000),
			'IsRec' => (bool)$this->getRequest()->getParam('IsRec', '0') ? 1 : 0,
			'IsIdxRec' => (bool)$this->getRequest()->getParam('IsIdxRec', '0') ? 1 : 0,
			'OrderNo' => (int)$this->getRequest()->getParam('OrderNo', 9999),
			'BizArea' => $p['BizArea']
			);
		
		if (!$VendorGuid) {
			throw new Msd_Exception('参数不正确');
		}

		if (count($error)>0) {
			$this->view->error = $error;
			$this->editAction();
			
			echo $this->view->render('vendor/edit.phtml');
			exit(0);
		} else if (count($toUpdate)>0) {
			$vendor = &Msd_Waimaibao_Vendor::getInstance($VendorGuid);
			$vendor->update($toUpdate);
			$basic = $vendor->basic();
			
			$this->log(array(
					'type' => 'update',
					'message' => '修改商家信息,商家: '.$basic['VendorName']
					));
		}
		
		$this->redirect($this->scriptUrl.'vendor');
	}
	
	public function editAction()
	{
		$this->view->headScript()->appendFile($this->baseUrl.'js/kindeditor/kindeditor-min.js');
		$this->view->headScript()->appendFile($this->baseUrl.'js/jquery/jquery.ajaxupload.js');
		
		$VendorGuid = $this->getRequest()->getParam('VendorGuid', '');
		$this->view->vendor_logo_url = Msd_Waimaibao_Vendor::imageUrl(array(
			'VendorGuid' => $VendorGuid	
			));
		$this->view->vendor_logo_big_url = Msd_Waimaibao_Vendor::imageBigUrl(array(
			'VendorGuid' => $VendorGuid	
			));
		$this->view->bizAreas = $this->bizAreas;
		
		if ($VendorGuid) {
			$vendor = &Msd_Waimaibao_Vendor::getInstance($VendorGuid);
			$this->view->basic = $vendor->basic();
			$this->view->extend = $vendor->extend();
		} else {
			throw new Msd_Exception('参数不正确');
		}
		
	}
	
	public function indexAction()
	{
		$this->pager_init();

		$cConfig = &Msd_Config::cityConfig();
		
		$table = &Msd_Dao::table('vendor');
		 
		$params = $sort = array();
		$orderKey = trim($this->getRequest()->getParam('order_key', 'VendorName'));
		$searchKey = trim(urldecode($this->getRequest()->getParam('search_key', '')));
		$searchVal = trim(urldecode($this->getRequest()->getParam('search_val', '')));
		$RegionGuid = trim(urldecode($this->getRequest()->getParam('Region')));
		$CategoryName = trim(urldecode($this->getRequest()->getParam('CategoryName', '')));
		$Address = trim(urldecode($this->getRequest()->getParam('Address', '')));
		 
		if (strlen($searchKey) && strlen($searchVal)) {
			$params[$searchKey] = $searchVal;
		}
		
		if ($RegionGuid) {
			$params['Region'] = $RegionGuid;
		}
		
		if (strlen($CategoryName)) {
			$params['CategoryName'] = $CategoryName;
		}
		
		if (strlen($Address)) {
			$params['Address'] = $Address;
		}
		
		$params['AreaGuid'] = $cConfig->db->guids->area->toArray();
		 
		if ($orderKey!='') {
			$sort[$orderKey] = 'DESC';
		}

		$cacher = &Msd_Cache_Remote::getInstance();
		$regions = (array)$cacher->get('Regions');
		$this->view->regions = array();
		foreach ($regions as $region) {
			$this->view->regions[$region['RegionGuid']] = $region['RegionName'];
		}
		
		$rows = $table->search($this->pager, $params, $sort);
		
		$ctgs = &Msd_Waimaibao_Category::Vendor();
		$this->view->categories = array();
		foreach ($ctgs as $ctg) {
			$this->view->categories[$ctg['CtgName']] = $ctg['CtgName'];
		}

		$this->view->rows = $rows;
		$this->view->page_links = $this->page_links();
		$this->view->data = array();
		$this->view->request = $_REQUEST;
		$this->view->bizAreas = $this->bizAreas;
		 
		$this->log(array(
				'type' => 'browse',
				'message' => '浏览商家',
			));		
	}
}