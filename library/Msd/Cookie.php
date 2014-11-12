<?php

/**
 * Cookie处理
 * 
 * @author pang
 *
 */
class Msd_Cookie
{
	private function __construct()
	{
		
	}
	
	public static function oc($key, $prefix='')
	{
		return $_COOKIE[$prefix.$key];
	}
	
	public static function set($key, $val, $params=array())
	{
		$expire = isset($params['expire']) ? (int)$params['expire'] : time()+MSD_ONE_DAY*7;
		$domain = isset($params['domain']) ? $params['domain'] : $_SERVER['domain'];
		$path = isset($params['path']) ? $params['path'] : '/';
		
		$flag = setcookie($key, $val, $expire, $path, $domain);
		$_COOKIE[$key] = $val;
		
		return $flag;
	}
}