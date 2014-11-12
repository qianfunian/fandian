<?php

class Msd_Service_Google_Geocoding_Reverse extends Msd_Service_Google_Geocoding_Base
{
	public function __construct()
	{
		$this->service_url = 'http://maps.googleapis.com/maps/api/geocode/{FORMAT}?latlng={LAT},{LON}&sensor=true';
	}
	
	public function fetch()
	{
		$this->calUrl();

		$http = new Msd_Http_Client($this->service_url, array());
		try {
			$response = $http->request();
			$html = $response->getBody();
			$result = json_decode($html);
			if ($result->status=='OK') {
				$city = $result->results[0]->address_components[3]->short_name;
				if ($city) {
					$this->result['city'] = $city;
				}
			} else {
				Msd_Log::getInstance()->google('Geocoding Reverse Failed: '."\n".$html);
			}
			
		} catch (Exception $e) {
			Msd_Log::getInstance()->google($e->getMessage());
		}
		
		return $this->result;		
	}
	
	public function calUrl()
	{
		parent::calUrl();
		
		$this->service_url = str_replace('{LON}', $this->params['lon'], $this->service_url);
		$this->service_url = str_replace('{LAT}', $this->params['lat'], $this->service_url);

		return $this->service_url;		
	}
}