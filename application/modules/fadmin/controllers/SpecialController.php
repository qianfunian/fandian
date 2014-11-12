<?php

class Fadmin_SpecialController extends Msd_Controller_Fadmin
{
	public function init()
	{
		parent::init();
		
		$this->auth('items');
	}
	
	public function doeditAction()
	{
		$ItemGuid = $this->getRequest()->getParam('ItemGuid', '');
		
		if (Msd_Validator::isGuid($ItemGuid)) {
			$p = &$this->getRequest()->getPost();
			$dao = &Msd_Dao::table('item/extend');
			$dao->doUpdate(array(
				'Detail' => $p['Detail'],
				'Sales' => (int)$p['Sales'],
				'Persisted' => trim($p['Persisted']),
				'LongTitle' => trim($p['LongTitle'])
				), $ItemGuid);
		}

		$this->redirect($this->scriptUrl.'special');
	}
	
	public function editAction()
	{
		$this->view->headScript()->appendFile($this->baseUrl.'js/kindeditor/kindeditor-min.js');
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
						'ItemGuid' => $ItemGuid	
						));
				}
			}
		}
		
		$this->view->data = $data;
		$this->view->extend = $extend;
		$this->view->item_logo_url = Msd_Waimaibao_Item::imageSpecialUrl(array(
			'ItemGuid' => $ItemGuid,
			'VendorGuid' => $data['VendorGuid']	
			));
	}
	
	public function indexAction()
	{
		$this->pager_init();
		
		$config = &Msd_Config::appConfig();
		$this->view->headScript()->appendFile($this->baseUrl.'js/jquery/jquery.ajaxupload.js');
		$this->view->headScript()->appendFile($this->baseUrl.'js/jquery/jquery.tooltip.js');

		$VendorGuid = trim(urldecode($this->getRequest()->getParam('VendorGuid', '')));
		$table = &Msd_Dao::table('item');
		 
		$params = $sort = array();
		$orderKey = trim($this->getRequest()->getParam('order_key', ''));
		$searchKey = trim(urldecode($this->getRequest()->getParam('search_key', '')));
		$searchVal = trim(urldecode($this->getRequest()->getParam('search_val', '')));
		
		$params['CtgName'] = $config->db->n->ctg_name->special;
		$params['Regions'] = Msd_Waimaibao_Region::RegionGuids();
		$params['Vendor'] = $VendorGuid;
		
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
				'message' => '浏览特价套餐',
			));		
	}
	
}
