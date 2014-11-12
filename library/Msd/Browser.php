<?php

/**
 * 浏览器检测
 * 
 * @author pang
 *
 */
class Msd_Browser
{
	protected static $instance = null;
	protected $agent = '';
	
	public function __construct($agent='')
	{
		$this->agent = $agent ? $agent : $_SERVER['HTTP_USER_AGENT'];	
	}
	
	public static function &getInstance()
	{
		if (self::$instance==null) {
			self::$instance = new self();
		}
		
		return self::$instance;
	}
	
	public function isIOS()
	{
		return (bool)preg_match('/iphone/i', $this->agent);
	}
	
	public function isIpad()
	{
		return (bool)preg_match('/ipad/i', $this->agent);
	}
	
	public function isIphone()
	{
		return (bool)preg_match('/iphone/i', $this->agent);
	}
	
	public function isPad()
	{
		
	}
	
	public function isAndroid()
	{
		return (bool)preg_match('/android/i', $this->agent);	
	}
	
	public function isBB()
	{
		
	}
	
	public function isWindows()
	{
		
	}
}