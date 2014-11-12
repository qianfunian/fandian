<?php

class Msd_Request
{
	protected static $ip = '';
	
	/**
	 * 请求的来源IP地址
	 * 
	 */
	public static function clientIp()
	{
		if (self::$ip=='') {
			self::$ip = getenv('HTTP_CLIENT_IP');
			if (!self::$ip) {
				$ips = explode(',', getenv('HTTP_X_FORWARDED_FOR'));
				foreach ($ips as $ip) {
					list($first, $bar) = explode('.', trim($ip));
					if ($first!='192' && $first!='127' && $first!='10') {
						self::$ip = trim($ip);
						break;
					}	
				}
				
				if (!self::$ip) {
					self::$ip = array_pop($ips);
				}
				
				if (!self::$ip) {
					self::$ip = getenv('REMOTE_ADDR');
					if (!self::$ip) {
						$ip = $_SERVER['REMOTE_ADDR'];
					}
				}
			}
		}
		
		return self::$ip;
	}
}