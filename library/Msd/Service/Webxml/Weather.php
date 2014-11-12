<?php

class Msd_Service_Webxml_Weather extends Msd_Service_Webxml_Base
{
	//	Webxml中江苏的地域代码
	protected $jiangsuCode = '31111';
	
	protected static $instance = null;
	
	public function __construct()
	{
		$this->urls['endpoint'] = 'http://webservice.webxml.com.cn/WebServices/WeatherWS.asmx';
		$this->urls['disco'] = 'http://webservice.webxml.com.cn/WebServices/WeatherWS.asmx?disco';
		$this->urls['wdsl'] = 'http://webservice.webxml.com.cn/WebServices/WeatherWS.asmx?wsdl';
	}
	
	public static function &getInstance()
	{
		if (self::$instance==null) {
			self::$instance = new self();
		}
		
		return self::$instance;
	}
	
	public function getSupportCityString()
	{
		
	}
	
	public function &getWeather($city)
	{
		$serviceUrl = $this->urls['endpoint'].'/getWeather?theUserID=&theCityCode='.$city;
		$data = array();
		
		try {
			$http = new Msd_Http_Client($serviceUrl, array());
			$result = $http->request()->getBody();		
			Msd_Log::getInstance()->service($result);
			
			$xml = simplexml_load_string($result);
			$results = array();

			if (count($xml->string)>5) {
				$data['prov_city'] = (string)$xml->string[0];
				$data['city'] =  (string)$xml->string[1];
				$data['code'] =  (string)$xml->string[2];
				$data['datetime'] =  (string)$xml->string[3];
				$data['detail'] =  (string)$xml->string[4];
				$data['air'] =  (string)$xml->string[5];
				$data['description'] =  (string)$xml->string[6];
				$data['tommorrow'] = array(
						'string' =>  (string)$xml->string[7],
						'temperature' =>  (string)$xml->string[8],
						'cloud' =>  (string)$xml->string[9],
						'img_1' =>  (string)$xml->string[10],
						'img_2' =>  (string)$xml->string[11],
						);
				$data['next_tommorrow'] = array(
						'string' => (string)$xml->string[12],
						'temperature' =>  (string)$xml->string[13],
						'cloud' =>  (string)$xml->string[14],
						'img_1' => (string) $xml->string[15],
						'img_2' => (string)$xml->string[16],			
						);
				
				$this->cacheResult($data);
			}
		} catch (Exception $e) {
			Msd_Log::getInstance()->service($e->getMessage()."\n".$e->getTraceAsString());
		}
		
		return $data;
	}
	
	public function cacheResult(&$data)
	{
		$today = date('Y-m-d');
		$tommorrow = date('Y-m-d', time()+MSD_ONE_DAY);
		$nextTommorrow = date('Y-m-d', time()+MSD_ONE_DAY*2);
		$cTommorrow = date('m月d日', time()+MSD_ONE_DAY);
		$cNextTommorrow = date('m月d日', time()+MSD_ONE_DAY*2);
		
		$table = &Msd_Dao::table('service/weather');

		$row = $table->getDate($data['code'], time());
		$row || $row = array();
		$row['DataDate'] = $today;
		$row['LastUpdate'] = $data['datetime'];
		$row['Detail'] = $data['detail'];
		$row['Air'] = $data['air'];
		$row['Description'] = $data['description'];

		$AutoId = (int)$row['AutoId'];
		if ($AutoId>0) {
			unset($row['AutoId']);
			$table->doUpdate($row, $AutoId);
		} else {
			$row['Code'] = $data['code'];
			$table->insert($row);
		}
		
		$row = $table->getDate($data['code'], time()+MSD_ONE_DAY);
		$row || $row = array();
		$row['String'] = str_replace($cTommorrow.' ', '', $data['tommorrow']['string']);
		$row['Temperature'] = $data['tommorrow']['temperature'];
		$row['Cloud'] = $data['tommorrow']['cloud'];
		$row['Img1'] = $data['tommorrow']['img_1'];
		$row['Img2'] = $data['tommorrow']['img_2'];
		$row['DataDate'] = $tommorrow;
		
		$AutoId = (int)$row['AutoId'];
		if ($AutoId>0) {
			unset($row['AutoId']);
			$table->doUpdate($row, $AutoId);
		} else {
			$row['Code'] = $data['code'];
			$table->insert($row);
		}
		
		$row = $table->getDate($data['code'], time()+MSD_ONE_DAY*2);
		$row || $row = array();
		$row['String'] = str_replace($cNextTommorrow.' ', '', $data['next_tommorrow']['string']);
		$row['Temperature'] = $data['next_tommorrow']['temperature'];
		$row['Cloud'] = $data['next_tommorrow']['cloud'];
		$row['Img1'] = $data['next_tommorrow']['img_1'];
		$row['Img2'] = $data['next_tommorrow']['img_2'];
		$row['DataDate'] = $nextTommorrow;
		
		$AutoId = (int)$row['AutoId'];
		if ($AutoId>0) {
			unset($row['AutoId']);
			$table->doUpdate($row, $AutoId);
		} else {
			$row['Code'] = $data['code'];
			$table->insert($row);
		}
	}
	
	public function &getWeatherFromCache($city)
	{
		$cacher = &Msd_Cache_Remote::getInstance();
		$cacheKey = 'Webxml_Weather';
		$data = $cacher->get($cacheKey);
		$data = null;
		
		if (!$data) {
			$table = &Msd_Dao::table('service/weather');
			$data = $table->getToday($city);
			$cacher->set($cacheKey, $data, 3600*24);
		}
		
		return $data;
	}
	
	public function &getTommorrowWeatherFromCache($city)
	{
		$cacher = &Msd_Cache_Remote::getInstance();
		$cacheKey = 'Webxml_Weather_Tommorrow';
		$data = $cacher->get($cacheKey);
		$data = null;
		
		if (!$data) {
			$table = &Msd_Dao::table('service/weather');
			$data = $table->getTommorrow($city);
			$cacher->set($cacheKey, $data, 3600*24);
		}
		
		return $data;
	}
}