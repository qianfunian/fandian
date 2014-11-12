<?php

class Service_LbsController extends Msd_Controller_Service
{
	public function coordinateAction()
	{
		$config = &Msd_Config::cityConfig();
		$longitude = (float)$this->getRequest()->getParam('longitude', $config->longitude);
		$latitude = (float)$this->getRequest()->getParam('latitude', $config->latitude);
		$Regions = &Msd_Waimaibao_Region::RegionGuids();
		
		$row = &Msd_Dao::table('coordinate')->nearestWithRegions($longitude, $latitude, $Regions);
		Msd_Output::prepareJson();
		echo json_encode($row);
		Msd_Output::doOutput();
	}
	
	/**
	 *	搜索附近地标
	 * 
	 */
	public function nearbycoordsAction()
	{
		$config = &Msd_Config::cityConfig();
		$longitude = (float)$this->getRequest()->getParam('longitude', $config->longitude);
		$latitude = (float)$this->getRequest()->getParam('latitude', $config->latitude);
		$Regions = &Msd_Waimaibao_Region::RegionGuids();
		
		$this->view->coords = Msd_Dao::table('coordinate')->nearbyWithRegions($longitude, $latitude, $Regions);		
	}
}