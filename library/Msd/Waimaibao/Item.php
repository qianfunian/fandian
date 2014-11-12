<?php

class Msd_Waimaibao_Item extends Msd_Waimaibao_Base
{
	public static function Sales($ItemGuid)
	{
		
	}
	
	public static function imageUrl($params, $default='')
	{
		$config = &Msd_Config::cityConfig();
		$staticUrl = Msd_Controller::staticUrl();

		if ($params['ItemGuid'] && $params['VendorGuid'] && $params['HasLogo']) {
			$ItemGuid = $params['ItemGuid'];
			$VendorGuid = $params['VendorGuid'];
			
			$ImageUrl = $staticUrl.$config->attachment->web_url->items .$VendorGuid . '/'.$ItemGuid.'.jpg';
		} else {
			$ImageUrl = $staticUrl.$config->attachment->web_url->item_default;
		}
		
		preg_match('/^http/i', $ImageUrl) || $ImageUrl = 'http://'.$_SERVER['HTTP_HOST'].$ImageUrl;
		
		return $ImageUrl;
	}

	public static function imageBigUrl($params, $default='')
	{
		$config = &Msd_Config::cityConfig();
		$staticUrl = Msd_Controller::staticUrl();
	
		if ($params['ItemGuid'] && $params['VendorGuid'] && $params['HasLogo']) {
			$ItemGuid = $params['ItemGuid'];
			$VendorGuid = $params['VendorGuid'];
	
			$ImageUrl = $staticUrl.$config->attachment->web_url->items_big .$VendorGuid . '/'.$ItemGuid.'.jpg';
		} else {
			$ImageUrl = $staticUrl.$config->attachment->web_url->item_default;
		}
	
		preg_match('/^http/i', $ImageUrl) || $ImageUrl = 'http://'.$_SERVER['HTTP_HOST'].$ImageUrl;
	
		return $ImageUrl;
	}

	public static function imageSpecialUrl($params, $default='')
	{
		$config = &Msd_Config::cityConfig();
		$staticUrl = Msd_Controller::staticUrl();
	
		Msd_Log::getInstance()->debug('item: '.$params['ItemGuid'].', vendor: '.$params['VendorGuid']);
		if ($params['ItemGuid'] && $params['VendorGuid']) {
			$ItemGuid = $params['ItemGuid'];
			$VendorGuid = $params['VendorGuid'];
	
			$ImageUrl = $staticUrl.$config->attachment->web_url->items_special .$VendorGuid . '/'.$ItemGuid.'.jpg';
		} else {
			$ImageUrl = $staticUrl.$config->attachment->web_url->item_default;
		}
	
		preg_match('/^http/i', $ImageUrl) || $ImageUrl = 'http://'.$_SERVER['HTTP_HOST'].$ImageUrl;
	
		return $ImageUrl;
	}
	
	public static function imageTuanUrl($params, $default='')
	{
		$ImageUrl = '';
		
		if ($params['ItemGuid'] && $params['VendorGuid']) {
			$ItemGuid = $params['ItemGuid'];
			$VendorGuid = $params['VendorGuid'];
			$config = &Msd_Config::cityConfig();
			$staticUrl = Msd_Controller::staticUrl();

			if ((int)$params['HasLogo']>0) {
				$SavePath = $config->attachment->save_path->items_tuan;
				$SavePath .= $VendorGuid.DIRECTORY_SEPARATOR.$ItemGuid.'.jpg';
				
				$ImageUrl = $staticUrl.$config->attachment->web_url->items_tuan .$VendorGuid . '/'.$ItemGuid.'.jpg';
			} else {
				$ImageUrl = $staticUrl.$config->attachment->web_url->item_default;
			}
				
		} else {
			$ImageUrl = $staticUrl.$config->attachment->web_url->item_default;
		}
		
		preg_match('/^http/i', $ImageUrl) || $ImageUrl = 'http://'.$_SERVER['HTTP_HOST'].$ImageUrl;
		
		return $ImageUrl;
	}
}
