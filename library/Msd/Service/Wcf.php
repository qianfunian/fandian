<?php

class Msd_Service_Wcf extends Msd_Service_Base
{
	public static function &factory($service)
	{
		$class = 'Msd_Service_Wcf_'.ucfirst(strtolower($service));
		$service = null;
		
		if (class_exists($class)) {
			$service = &call_user_func(array(
					$class,
					'getInstance'
				));
		} else {
			throw new Msd_Exception('Service '.$service.' not exists!');
		}
		
		return $service;
	}
}