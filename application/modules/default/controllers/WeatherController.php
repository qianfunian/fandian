<?php

class WeatherController extends Msd_Controller_Default
{
	public function indexAction()
    {
		$this->view->tommorrow = Msd_Service_Webxml_Weather::getInstance()->getTommorrowWeatherFromCache(
				Msd_Config::cityConfig()->service->webxml->weather->city
			);
    }
}