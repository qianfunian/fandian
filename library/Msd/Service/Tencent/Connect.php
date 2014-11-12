<?php

class Msd_Service_Tencent_Connect extends Msd_Service_Tencent_Base
{
	protected static $instance = null;
	
	private function __construct()
	{
		
	}
	
	public static function &getInstance()
	{
		if (self::$instance==null) {
			self::$instance = new self();
		}
		
		return self::$instance;
	}
	
	public function getAuthUrl()
	{
		$config = Msd_Config::cityConfig()->service->tencent->connect;
		$authUrl = $config->authorize_url.'?response_type=code&client_id='.$config->app_id;
		$authUrl .= '&redirect_uri='.urlencode($config->callback_url).'&state='.md5(uniqid(mt_rand(), true));
		$authUrl .= '&scope='.$config->scope;
		
		return $authUrl;
	}
	
	public function getAccessToken($code, $state)
	{
		$config = Msd_Config::cityConfig()->service->tencent->connect;    	
		$access_token = $expires_in = '';
			
		$tokenUrl = $config->token_url;
    	$tokenUrl .= '?grant_type=authorization_code&client_id='.$config->app_id.'&client_secret='.$config->app_key;
    	$tokenUrl .= '&code='.$code.'&state='.$state.'&redirect_uri='.urlencode($config->token_callback_url);
    	
    	$http = new Msd_Http_Client($tokenUrl, array());
    	$result = $http->request()->getBody();
    	parse_str($result);
    	
    	return array(
    			'token' => $access_token,
    			'expires' => $expires_in
    			);
	}
	
	public function getOpenID($token)
	{
		$config = Msd_Config::cityConfig()->service->tencent->connect;
		$url = $config->me_url.'?access_token='.$token;
		
		$http = new Msd_Http_Client($url);
		$result = $http->request()->getBody();
		$json = json_decode(trim(preg_replace('/^callback\((.*)\);$/is', '\\1', $result)), true);
		
		return $json['openid'];
	}
	
	public function getUserInfo($openID, $accessToken)
	{
		$config = Msd_Config::cityConfig()->service->tencent->connect;
		
		$url = $config->userinfo_url.'?access_token='.$accessToken.'&oauth_consumer_key='.$config->app_id.'&openid='.$openID;
		$http = new Msd_Http_Client($url);
		$result = $http->request()->getBody();
		
		return json_decode($result, true);
	}
}