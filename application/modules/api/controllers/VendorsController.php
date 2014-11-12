<?php

class Api_VendorsController extends Msd_Controller_Api
{
	/**
	 * 获取商家详情
	 * 
	 */
	public function detailAction()
	{
		$this->xmlRoot = 'vendor';
		$VendorGuid = trim($this->getRequest()->getParam('id', ''));
		if ($VendorGuid) {
			$vendor = &Msd_Waimaibao_Vendor::getInstance($VendorGuid);
			Msd_Dao::table('vendor/extend')->increase('Views', $VendorGuid);
			$detail = &Msd_Waimaibao_Vendor::Detail($VendorGuid);
			
			$t = &Msd_Api_Translator::getInstance()->t('vendor');
			
			$this->output[$this->xmlRoot] = $t->translate($detail);
		}

		$this->output();
	}
	
	/**
	 * 商家搜索
	 * 
	 */
	public function searchAction()
	{
		$this->xmlRoot = 'result';
		
		$config = &Msd_Config::appConfig();
		$cConfig = &Msd_Config::cityConfig();
		
		$lon = (float)$this->getRequest()->getParam('lon', 0);
		$lat = (float)$this->getRequest()->getParam('lat', 0);
		$range = (int)$this->getRequest()->getParam('range', 3000);
		$sortby = $this->getRequest()->getParam('sortby');
		$sort = $this->getRequest()->getParam('sort', 'ASC');
		$category = $this->getRequest()->getParam('category', '');
		$area = $this->getRequest()->getParam('area');
		$bizArea = $this->getRequest()->getParam('biz_area');
		$page = (int)$this->getRequest()->getParam('page', 1);
		$pageSize = (int)$this->getRequest()->getParam('page_size', 10);
		$keyword = trim(urldecode(trim($this->getRequest()->getParam('keyword'))));
		$service = trim(urldecode(trim($this->getRequest()->getParam('service'))));
		$service || $service = $config->db->n->service_name->normal;
		
		// override range
		$range = 99999999;

		$this->pager_init(array(
			'limit' => $pageSize	
			));
		$params = $orderby = array();
		
		$area && $params['RegionName'] = $area;
		$category && $params['CategoryName'] = $category;
		strlen($keyword) && $params['VendorName'] = $keyword;
		$bizArea && $params['BizArea'] = $bizArea;
		$params['Longitude'] = $lon;
		$params['Latitude'] = $lat;
		$params['Distance'] = '0,'.$range;
		$params['service'] = $service;
		($area && $bizArea) && $params['BizArea'] = $area.','.$bizArea;
		
		$service==$config->db->n->service_name->normal && $params['passby_category'] = array(
			$config->db->n->service_name->night,
			$config->db->n->service_name->afternoon
			);
		$params['exclude_mini'] = true;
		$params['Disabled'] = 0;
		$params['CityId'] = $cConfig->city_id;
		$params['AreaGuid'] = $cConfig->db->guids->area->toArray();
		
		switch ($sort) {
			case 'DESC':
			case 'ASC':
				break;
			default:
				$sort = 'DESC';
				break;
		}
		
		switch ($sortby) {
			case 'random':
				$orderby = array(
					'_random_' => 'DESC'
					);
				break;
			case 'hotrate':
				$orderby = array(
					'hotrate' => $sort
					);
				break;
			case 'distance':
				$orderby = Msd_Validator::isValidLngLat($lon, $lat) ? array(
					'distance' => $sort
					) : array(
					'hotrate' => $sort
					);
				break;
			default:
				$orderby = Msd_Validator::isValidLngLat($lon, $lat) ? array(
					'distance' => $sort
					) : array(
					'hotrate' => $sort
					);
				break;
		}
		
		$vendors = &Msd_Dao::table('vendor')->search($this->pager, $params, $orderby);
		$t = &Msd_Api_Translator::getInstance()->t('vendor_concise');

		$this->output[$this->xmlRoot]['vendors'] = array();
		foreach ($vendors as $vendor) {
			$data = $vendor;
			
			if (Msd_Validator::isLngLat($lon, $lat)) {
				$tmp = Msd_Waimaibao_Freight::calculateByLL($lon, $lat, $vendor['VendorGuid']);
				$data['freight'] = $tmp['freight'];
				$data['Longitude'] = $tmp['Longitude'];
				$data['Latitude'] = $tmp['Latitude'];
			}
			
			$this->output[$this->xmlRoot]['vendors'][] = array(
				'vendor_concise' => $t->translate($data)	
				);
		}

		$this->output[$this->xmlRoot]['summary'] = array(
			'page' => $page,
			'page_size' => $pageSize,
			'total_pages' => $this->pages(),
			'total_rows' => $this->pager['total']	
			);
		
		$this->output();
	}
}