<?php

class Msd_Hook_Varnish extends Msd_Hook_Base
{
	protected static $instance = null;
	
	public static function &getInstance()
	{
		if (self::$instance==null) {
			self::$instance = new self();
		}
		
		return self::$instance;
	}
	
	/**
	 * 当有菜品图片上传时，清理Varnish缓存
	 * 
	 * @param array $params
	 */
	public function NewFileSaved(array $params)
	{
		$url = $params['url'];
		Msd_Log::getInstance()->varnish($url);
		
		if ($url) {
			$http = new Msd_Http_Client();
			$http->setUri($url);
			$http->request('PURGE');
		}
	}	
}