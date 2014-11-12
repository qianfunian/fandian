<?php

/**
 * {0}
 * 
 * @author
 * @version 
 */
	
class Fadmin_NewYearController extends Msd_Controller_Fadmin
{
	public function init()
	{
		parent::init();
	}
	
	/**
	 * The default action - show the home page
	 */
    public function indexAction() 
    {
        $this->pager_init();
		
		$config = &Msd_Config::appConfig();
		
		//$this->view->headScript()->appendFile($this->baseUrl.'js/jquery/jquery.ajaxupload.js');
		//$this->view->headScript()->appendFile($this->baseUrl.'js/jquery/jquery.tooltip.js');
		
		//$VendorGuid = trim(urldecode($this->getRequest()->getParam('VendorGuid', '')));
		$table = &Msd_Dao::table('item');
			
		//$params = $sort = array();
		//$orderKey = trim($this->getRequest()->getParam('order_key', ''));
		//$searchKey = trim(urldecode($this->getRequest()->getParam('search_key', '')));
		//$searchVal = trim(urldecode($this->getRequest()->getParam('search_val', '')));
		
		$params['ServiceName'] = $config->db->n->service_name->newyear;
		$params['Regions'] = Msd_Waimaibao_Region::RegionGuids();
		//$params['Vendor'] = $VendorGuid;
		
		//(int)$this->getRequest()->getParam('IsRec', '')==1 && $params['IsRec'] = 1;
			
		//if (strlen($searchKey) && strlen($searchVal)) {
		//	$params[$searchKey] = $searchVal;
		//}
			
		//if ($orderKey!='') {
		//	$sort[$orderKey] = 'DESC';
		//}
		
		$tmp = $table->search($this->pager, $params, array());
		$rows = array();
		foreach ($tmp as $row) {
			$row['img_url'] = Msd_Waimaibao_Item::imageUrl($row);
			$row['bimg_url'] = Msd_Waimaibao_Item::imageBigUrl($row);
			$rows[] = $row;
		}
		
		$this->view->rows = $rows;
		$this->view->page_links = $this->page_links();
			
		$this->log(array(
				'type' => 'browse',
				'message' => '浏览年夜饭套餐',
		));
        
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
    
    public function doeditAction()
    {
    	$ItemGuid = $this->getRequest()->getParam('ItemGuid', '');
    
    	if (Msd_Validator::isGuid($ItemGuid)) {
    		$p = &$this->getRequest()->getPost();
    		$dao = &Msd_Dao::table('item/extend');
    		$dao->doUpdate(array(
    				'Detail' => $p['Detail'],
    				'Sales' => (int)$p['Sales'],
    				'LongTitle' => trim($p['LongTitle'])
    		), $ItemGuid);
    	}
    
    	$this->redirect($this->scriptUrl.'new-year');
    }
}
