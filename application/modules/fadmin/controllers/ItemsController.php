<?php

class Fadmin_ItemsController extends Msd_Controller_Fadmin
{
	public function init()
	{
		parent::init();
		
		$this->auth('items');
	}
	
	protected function _initItem($ItemGuid)
	{
		$dao = &Msd_Dao::table('item');
		$eDao = &Msd_Dao::table('item/extend');
		$itemExtend = $eDao->get($ItemGuid);
		if (!$itemExtend) {
			$item = $dao->get($ItemGuid);
			if ($item) {
				$eDao->insert(array(
					'ItemGuid' => $ItemGuid,
					'CityId' => $item['CityId'],
					'HasLogo' => 0,
					'IsRec' => 0,
					'IsTuan' => 0,
					'Sales' => 0,
					'Persisted' => '',
					'Detail' => '',
					'LongTitle' => ''
					));
			}
		}
	}
	
	public function setrecAction()
	{
		$ItemGuid = trim($this->getRequest()->getParam('ItemGuid'));
		
		if (Msd_Validator::isGuid($ItemGuid)) {
			$this->_initItem($ItemGuid);
			Msd_Dao::table('item/extend')->doUpdate(array(
				'IsRec' => 1	
				), $ItemGuid);
		}
		
		$this->ajaxOutput();
	}
	
	public function unsetrecAction()
	{
		$ItemGuid = trim($this->getRequest()->getParam('ItemGuid'));
		
		if (Msd_Validator::isGuid($ItemGuid)) {
			$this->_initItem($ItemGuid);
			Msd_Dao::table('item/extend')->doUpdate(array(
				'IsRec' => 0
				), $ItemGuid);
		}
		
		$this->ajaxOutput();
	}
	
	public function settuanAction()
	{
		$ItemGuid = trim($this->getRequest()->getParam('ItemGuid'));
		
		if (Msd_Validator::isGuid($ItemGuid)) {
			$this->_initItem($ItemGuid);
			Msd_Dao::table('item/extend')->doUpdate(array(
				'IsTuan' => 1	
				), $ItemGuid);
		}
		
		$this->ajaxOutput();
	}
	
	public function unsettuanAction()
	{
		$ItemGuid = trim($this->getRequest()->getParam('ItemGuid'));
		
		if (Msd_Validator::isGuid($ItemGuid)) {
			$this->_initItem($ItemGuid);
			Msd_Dao::table('item/extend')->doUpdate(array(
				'IsTuan' => 0
				), $ItemGuid);
		}
		
		$this->ajaxOutput();
	}
	
	public function editAction()
	{
		$this->view->headScript()->appendFile($this->baseUrl.'js/jquery/jquery.ajaxupload.js');
		$ItemGuid = trim(urldecode($this->getRequest()->getParam('ItemGuid')));
		
		$iTable = &Msd_Dao::table('item');
		$ieTable = &Msd_Dao::table('item/extend');
		
		$data = $extend = array();
		
		if ($ItemGuid) {
			$data = &$iTable->get($ItemGuid);
			if ($data['ItemGuid']) {
				$extend = $ieTable->get($ItemGuid);
				if (!$extend) {
					$ieTable->insert(array(
						'ItemGuid' => $ItemGuid,
						'CityId' => $data['CityId']
						));
				}
			}
		}
		
		$this->view->data = $data;
		$this->view->extend = $extend;
	}
	
	public function indexAction()
	{
		$this->pager_init();
		
		$cConfig = &Msd_Config::cityConfig();
		
		$this->view->headScript()->appendFile($this->baseUrl.'js/jquery/jquery.ajaxupload.js');
		$this->view->headScript()->appendFile($this->baseUrl.'js/jquery/jquery.tooltip.js');

		$VendorGuid = trim(urldecode($this->getRequest()->getParam('VendorGuid', '')));
		$table = &Msd_Dao::table('item');
		 
		$params = $sort = array();
		$orderKey = trim($this->getRequest()->getParam('order_key', ''));
		$searchKey = trim(urldecode($this->getRequest()->getParam('search_key', '')));
		$searchVal = trim(urldecode($this->getRequest()->getParam('search_val', '')));
		
		$params['Vendor'] = $VendorGuid;		
		$params['AreaGuid'] = $cConfig->db->guids->area->toArray();
		
		(int)$this->getRequest()->getParam('IsRec', '')==1 && $params['IsRec'] = 1;
		 
		if (strlen($searchKey) && strlen($searchVal)) {
			$params[$searchKey] = $searchVal;
		}
		 
		if ($orderKey!='') {
			$sort[$orderKey] = 'DESC';
		}

		$tmp = $table->search($this->pager, $params, $sort);
		$rows = array();
		foreach ($tmp as $row) {
			$row['img_url'] = Msd_Waimaibao_Item::imageUrl($row);
			$row['bimg_url'] = Msd_Waimaibao_Item::imageBigUrl($row);
			$rows[] = $row;
		}
		
		$this->view->rows = $rows;
		$this->view->page_links = $this->page_links();
		$this->view->data = array();
		$this->view->request = $_REQUEST;
		$this->view->VendorGuid = $VendorGuid;
		$this->view->VendorDetail = $VendorGuid ? Msd_Waimaibao_Vendor::Detail($VendorGuid) : array();
		 
		$this->log(array(
				'type' => 'browse',
				'message' => '浏览商家菜品',
		));		
	}
	
}
