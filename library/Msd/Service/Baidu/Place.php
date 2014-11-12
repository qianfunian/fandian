<?php

/**
 * 百度位置服务
 * 
 * @author pang
 *
 */

class Msd_Service_Baidu_Place extends Msd_Service_Baidu_Base
{
	protected static $instance = null;
	
	public function __construct()
	{
		parent::__construct();
		
		$this->url = 'http://api.map.baidu.com/place/search?region='.urlencode(parent::$cityConfig->service->baidu->city_name).'&output=json&key='.$this->key.'&query=';
	}
	
	public static function &getInstance()
	{
		if (self::$instance==null) {
			self::$instance = new self();
		}
		
		return self::$instance;
	}
	
	public function search($place)
	{
		$result = array(
				'longitude' => 0,
				'latitude' => 0
				);
		
		$url = $this->url.urlencode($place);

		try {
			$row = &Msd_Dao::table('baidu/placelog')->searchAddress($place);

			if (!$row) {
				$city_id = Msd_Config::cityConfig()->city_id;
				$http = new Msd_Http_Client($url, array());
				$binary = $http->request()->getBody();
				
				$json = json_decode($binary);
				if ($json->status=='OK' && count($json->results)>0) {
					$result['longitude'] = $json->results[0]->location->lng;
					$result['latitude'] = $json->results[0]->location->lat;
					
					$table = &Msd_Dao::table('baidu/placelog');
					$table->insert(array(
							'Name' => $place,
							'Address' => $place,
							'Longitude' => $json->results[0]->location->lng,
							'Latitude' => $json->results[0]->location->lat,
							'CityId' => $city_id
							));
					
					foreach ($json->results as $row) {
						if ($row->address!=$place) {
							$table->insert(array(
									'Address' => $row->address,
									'Name' => $row->name,
									'Longitude' => $row->location->lng,
									'Latitude' => $row->location->lat,
									'Phone' => isset($row->telphone) ? $row->telphone : '',
									'CityId' => $city_id
									));
						}	
					}
				}
			} else {
				$result['longitude'] = $row['Longitude'];
				$result['latitude'] = $row['Latitude'];
			}
		} catch (Exception $e) {
			Msd_Log::getInstance()->baidu($e->getMessage()."\n".$e->getTraceAsString());
		}
		
		return $result;
	}
}