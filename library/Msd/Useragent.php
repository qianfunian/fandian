<?php

/**
 * 用户代理检测
 * 
 * @author pang
 *
 */

class Msd_Useragent
{
	protected $ua = '';
	protected $bot = '';
	protected $browser = '';
	protected $browserVersion = '';
	protected $prov = '';
	protected $city = '';
	protected $ip = '';
	
	protected static $instance = null;
	
	public function __construct($agent='')
	{
		$agent || $this->ua = getenv('IS_BOT') ? 'Googlebot' : $_SERVER['HTTP_USER_AGENT'];
		
		$this->parse();
	}
	
	public static function &getInstance()
	{
		if (self::$instance==null) {
			self::$instance = new self();
		}
		
		return self::$instance;
	}
	
	public function is_bot()
	{
		return (bool)$this->bot;
	}
	
	protected function parse()
	{
		//	Ip
		$this->ip = Msd_Request::clientIp();
		
		//	Bot
		if (preg_match('/googlebot/i', $this->ua)) {
			$this->bot = 'Googlebot';
		} else if (preg_match('/baiduspider/i', $this->ua)) {
			$this->bot = 'BaiduSpider';
		} else if (preg_match('/bingbot/i', $this->ua)) {
			$this->bot = 'Bing';
		} else if (preg_match('/sosospider/i', $this->ua)) {
			$this->bot = 'Soso';
		} else if (preg_match('/sogouspider/i', $this->ua)) {
			$this->bot = 'SogouSpider';
		} else if (preg_match('/yahoo/i', $this->ua) || preg_match('/slurp/i', $this->ua)) {
			$this->bot = 'YahooSpider';
		} else if (preg_match('/youdaobot/i', $this->ua)) {
			$this->bot = 'YoudaoBot';
		} else if (preg_match('/bot/i', $this->ua) || preg_match('/spider/i', $this->ua)) {
			$this->bot = 'UnknownBot';
		}
		
		// Browser
		if (preg_match('/maxthon/i', $this->ua)) {
			$this->browser = 'Maxthon';
		} else if (preg_match('/palemoon/i', $this->ua)) {
			$this->browser = 'Palemoon';
		} else if (preg_match('/opera/i', $this->ua)) {
			$this->browser = 'Opera';
			
			$t = explode('/', $this->ua);
			$t = explode(' ', $t[1]);
			$this->browserVersion = $t[0];
		} else if (preg_match('/chrome/i', $this->ua)) {
			$this->browser = 'Google Chrome';
		} else if (preg_match('/safari/i', $this->ua)) {
			$this->browser = 'Safari';
		} else if (preg_match('/mozilla/i', $this->ua) && preg_match('/msie/i', $this->ua)) {
			$this->browser = 'Internet Explorer';
			$this->simple_browserVersion();
		} else if (preg_match('/mozilla/i', $this->ua) && preg_match('/firefox/i', $this->ua)) {
			$this->browser = 'Mozilla Firefox';
		}
		
		// OS
		if (preg_match('/win/i', $this->ua)) {
			if (preg_match('/95/', $this->ua)) {
				$this->os = 'Windows 95';
			} else if (preg_match('/98/', $this->ua)) {
				$this->os = 'Windows 98';
			} else if (preg_match('/nt 5.1/i', $this->ua)) {
				$this->os = 'Windows XP';
			} else if (preg_match('/nt 5.2/i', $this->ua)) {
				$this->os = 'Windows 2003';
			} else if (preg_match('/nt 6.0/i', $this->ua)) {
				$this->os = 'Windows Vista';
			} else if (preg_match('/nt 6.1/i', $this->ua)) {
				$this->os = 'Windows 7';
			} else if (preg_match('/32/i', $this->ua)) {
				$this->os = 'Windows 32';
			} else {
				$this->os = 'Windows Unknown';
			}
		} else if (preg_match('/linux/i', $this->ua)) {
			$this->os = 'Linux';
		} else if (preg_match('/mac/i', $this->ua)) {
			$this->os = 'Mac';
		} else if (preg_match('/freebsd/i', $this->ua)) {
			$this->os = 'FreeBSD';
		} else if (preg_match('/sun/i', $this->ua) && preg_match('/os/i', $this->ua)) {
			$this->os = 'SunOS';
		} else {
			$this->os = 'unknown';
		}
	}
	
	protected function simple_browserVersion()
	{
		$t = explode('(', $this->ua);
		$t = explode(';', $t[1]);
		$t = explode(' ', $t[1]);
		
		$this->browserVersion = preg_replace('/([d.]+)/','1', $t[2]);		
	}
	
}