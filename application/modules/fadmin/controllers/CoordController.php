<?php

class Fadmin_CoordController extends Msd_Controller_Fadmin
{
	public function init()
	{
		parent::init();
		
		$this->auth('coord');
	}
	
	public function forbaiduAction()
	{
		$CoordGuid = $this->getRequest()->getParam('CoordGuid');
		$lng = (float)$this->getRequest()->getParam('lng');
		$lat = (float)$this->getRequest()->getParam('lat');
		$output = array();
		
		if (Msd_Validator::isGuid($CoordGuid) && Msd_Validator::isLngLat($lng, $lat)) {
			$table = &Msd_Dao::table('coordforbaidu');
			$row = $table->get($CoordGuid);
			if ($row) {
				$table->doUpdate(array(
					'Longitude' => $lng,
					'Latitude' => $lat	
					), $CoordGuid);
			} else {
				$table->insert(array(
					'CoordGuid' => $CoordGuid,
					'Longitude' => $lng,
					'Latitude' => $lat	
					));
			}
			
			$output = array(
				'Longitude' => $lng,
				'Latitude' => $lat	
				);
			
			$cacher = &Msd_Cache_Remote::getInstance();
			$ckey = 'bmap_ll_'.$CoordGuid;
			$cacher->set($ckey, $output);
		}
		
		$this->ajaxOutput($output);
	}
	
	public function indexAction()
	{
		$this->pager_init();

		$table = &Msd_Dao::table('coordinate');
		 
		$params = $sort = array();
		$orderKey = trim($this->getRequest()->getParam('order_key', 'CoordName'));
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
		
		$this->view->rows = $rows;
		$this->view->page_links = $this->page_links();
		$this->view->data = array();
		$this->view->request = $_REQUEST;
		 
		$this->log(array(
				'type' => 'browse',
				'message' => '浏览地标',
			));		
	}
}