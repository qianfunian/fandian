<?php

/**
 * 11.2 获取系统支持的城市分站信息
 * @author pang
 * @email pang@fandian.com
 *
 */
class Api_CitiesController extends Msd_Controller_Api
{
	public function init()
	{
		parent::init();
		
		Zend_Controller_Front::getInstance()->setParam('noViewRenderer', 0);
	}
	
	/**
	 * 获取城市详细资料
	 * 
	 */
	public function detailAction()
	{
		$this->xmlRoot = 'city';
		
		$translator = &Msd_Api_Translator::getInstance()->t('city');
		
		$this->output[$this->xmlRoot] = $translator->translate(self::cityDetail($this->cityId));
		$this->output();
	}
	
	/**
	 * 支持的城市列表
	 * 
	 */
	public function indexAction()
	{
		$this->xmlRoot = 'cities';
		$config = &Msd_Config::appConfig();
		
		$cities = $config->cities->toArray();
		$tname = 'city_concise';
		$t = &Msd_Api_Translator::getInstance()->t($tname);
		foreach ($cities as $city) {
			if ((bool)$city['api_enabled']) {
				$this->output[$this->xmlRoot][] = array(
					$tname => $t->translate($city)
					);
			}
		}
		
		$this->output();
	}
	
	/**
	 * 根据经纬度自动分配城市
	 * 
	 */
	public function autoAction()
	{
		$this->xmlRoot = 'city';
		$lon = (float)$this->getRequest()->getParam('lon', 0);
		$lat = (float)$this->getRequest()->getParam('lat', 0);
		
		$service = new Msd_Service_Google_Geocoding_Reverse();
		$service->setParams(array(
			'lon' => $lon,
			'lat' => $lat
			));
		$result = $service->fetch();
		$city = strtolower($result['city']);
		
		$config = &Msd_Config::appConfig()->cities->toArray();
		$data = $config['wuxi'];

		if ($city && isset($config[$city])) {
			$cityId = $config[$city]['zone'];
			$cData = self::cityDetail($cityId);
		} else {
			foreach ($config as $k=>$v) {
				if (strpos($v['name'], $city)===false) {
					continue;
				} else {
					$city = $k;
					break;
				}
			}
			
			if ($city && isset($config[$city])) {
				$cityId = $config[$city]['zone'];
				$cData = self::cityDetail($cityId);
			} else {
				$cData = self::cityDetail($config['wuxi']['zone']);
			}
		}

		$this->output[$this->xmlRoot] = Msd_Api_Translator::getInstance()->t('city')->translate($cData);
		$this->output();
	}
	
	protected static function &cityDetail($cityId)
	{
		$cacher = &Msd_Cache_Remote::getInstance();
		$cacheKey = 'cd_'.$cityId;
		$data = $cacher->get($cacheKey);

		if (!$data) {
			$data = array();
			$key = '';
			$config = &Msd_Config::appConfig();
			$cities = $config->cities->toArray();
			
			foreach ($cities as $ckey=>$city) {
				if ($city['zone']==$cityId) {
					$key = $ckey;
					break;
				}
			}

			if ($key) {
				$data = $cities[$key];
				$cConfig = &Msd_Config::cityConfig($key);
				$data['Longitude'] = $cConfig->longitude;
				$data['Latitude'] = $cConfig->latitude;

				$cache = &Msd_Cache_Loader::siteIndex($key);
				$data['biz_area'] = &$cache['biz_area'];

				$cs = &Msd_Waimaibao_Category::Vendor($key);
				$data['categories'] = array();
				foreach ($cs as $c) {
					if ($c['CtgStdName']==$config->db->n->ctg_std_name->vendor && $c['CtgName']!=$config->db->n->service_name->night && $c['CtgName']!=$config->db->n->service_name->afternoon) {
						$data['categories'][] = $c;
					}
				}

				$data['services'] = array();
				$ss = $cConfig->services->supported->toArray();
				foreach ($ss as $s) {
					$data['services'][] = $s;
				}
			}
			
			$cacher->set($cacheKey, $data, MSD_ONE_DAY);
		}

		return $data;
	}
}
