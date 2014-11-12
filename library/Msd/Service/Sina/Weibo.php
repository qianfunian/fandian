<?php

require_once 'Sina/Weibo.php';

class Msd_Service_Sina_Weibo extends Msd_Service_Sina_Base
{
	protected static $oauthInstance = null;
	protected static $instance = null;
	protected static $clients = array();
	
	private function __construct()
	{
		self::OAuth();
	}
	
	public static function &getInstance()
	{
		if (self::$instance==null) {
			self::$instance = new self();
		}
		
		return self::$instance;
	}
	
	public static function &client($token)
	{
		if (!isset(self::$clients[$token])) {
			$config = Msd_Config::cityConfig()->service->sina->weibo;
			$client = new SaeTClientV2(
					$config->akey , 
					$config->skey,
					$token
				);
		}
		
		return $client;
	}
	
	public static function &OAuth()
	{
		if (self::$oauthInstance==null) {
			$config = Msd_Config::cityConfig()->service->sina->weibo;
			
			self::$oauthInstance = new SaeTOAuthV2(
					$config->akey , 
					$config->skey
					);
		}
		
		return self::$oauthInstance;
	}
	
	public function getOAuthUrl()
	{
		$config = Msd_Config::cityConfig()->service->sina->weibo;
		$authUrl = self::$oauthInstance->getAuthorizeURL($config->callback_url);
		
		return $authUrl;
	}
}