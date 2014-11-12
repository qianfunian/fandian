<?php

/**
 * 百度相关服务
 * 
 * @author pang
 *
 */
class Service_BaiduController extends Msd_Controller_Service
{
	protected $key = '';
		
	public function init()
	{
		parent::init();
		
		$keys = explode(',', Msd_Config::cityConfig()->service->baidu->keys);
		$this->key = $keys[rand(0, count($keys)-1)];
	}
	
	public function forcsharpsAction()
	{
		$callback = trim(urldecode(trim($this->getRequest()->getParam('callback'))));
		$keywords = trim(urldecode(trim($this->getRequest()->getParam('keywords'))));
		
		$cacher = Msd_Cache_Remote::getInstance();
		$key = 'forcsharps_'.md5($keywords);
		$data = $cacher->get($key);
		
		if (!$data) {
			
			$cacher->set($key, $data, MSD_ONE_DAY);
		}
		
		$this->ajaxOutput($data, array(
			'prefix' => $callback.'(',
			'suffix' => ')'	
			));
	}
	
	public function forcsharpAction()
	{
		$RegionGuid = $this->getRequest()->getParam('RegionGuid', '');
		$config = &Msd_Config::cityConfig();
		
		$data = array(
			'lat' => $config->latitude,
			'lng' => $config->longitude	
			);
		
		if (Msd_Validator::isGuid($RegionGuid)) {
			$data = Msd_Dao::table('region')->cget($RegionGuid);
		}
		
		$this->view->data = $data;
	}
		
	/**
	 * Reverse Geo搜索
	 * 
	 */
	public function placeAction()
	{
		$place = trim(urldecode($this->getRequest()->getParam('place', '')));
		$config = &Msd_Config::cityConfig();
		
		$output = array(
			'longitude' => $config->longitude,
			'latitude' => $config->latitude,
			);
		$cacher = &Msd_Cache_Remote::getInstance();
		$key = 'baidu_place_'.md5($place);
		$data = $cacher->get($key);

		if (!$data) {
			$tmp = &Msd_Service_Baidu_Place::getInstance()->search($place);
			
			if (!Msd_Validator::isLngLat($tmp['longitude'], $tmp['latitude']) || Msd_Validator::isDefaultLngLat($tmp['longitude'], $tmp['latitude'])) {
				$tmp2 = &Msd_Waimaibao_Coordinate::searchAddress($place);	
	
				if ($tmp2 && Msd_Validator::isLngLat($tmp2['longitude'], $tmp2['latitude'])) {
					$tmp = &$tmp2;
				}
			}
			
			if (Msd_Validator::isLngLat($tmp['longitude'], $tmp['latitude'])) {
				$output = &$tmp;
			}
			
			$cacher->set($key, $output);
		} else {
			$output = &$data;
		}
		
		$this->ajaxOutput($output);
	}
}