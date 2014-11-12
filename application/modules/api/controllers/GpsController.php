<?php

class Api_GpsController extends Msd_Controller_Api
{
	public function init()
	{
		parent::init();
	
		Zend_Controller_Front::getInstance()->setParam('noViewRenderer', 0);
	}
	
	/**
	 * 根据经纬度获取附近的10个地标
	 * 
	 */
	public function placemarksAction()
	{
		$this->xmlRoot = 'placemarks';
		
		$lon = (float)$this->getRequest()->getParam('lon', 0);
		$lat = (float)$this->getRequest()->getParam('lat', 0);
		
		$translator = &Msd_Api_Translator::getInstance()->t('placemark');
		$rows = Msd_Dao::table('coordinate')->nearby($lon, $lat);
		foreach ($rows as $row) {
			$this->output[$this->xmlRoot][] = array(
				'placemark' => 	$translator->translate($row)
				);
		}
		
		$this->output();
	}
}