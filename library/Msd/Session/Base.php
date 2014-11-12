<?php

abstract class Msd_Session_Base 
{
	public $user = array();
	
	protected $namespace = '';
	protected $handler = array();
	protected $dao = null;
	protected $sid = '';
	protected $acl = null;
	
	public static $stickTime = 31536000;

	protected function init($handler="memcache")
	{
		$config = &Msd_Config::cityConfig();
		
		if (!session_id()) {
			switch($handler) {
				case 'memcached':
					$host = $config->session->memcache->host;
					$sessionSavePath = $host.':11211';
					ini_set('session.save_handler', 'memcached');
					session_save_path($sessionSavePath); 
					break;
				case 'memcache':
					$host = $config->session->memcache->host;
					$sessionSavePath = 'tcp://'.$host.':11211';
					ini_set('session.save_handler', 'memcache');
					session_save_path($sessionSavePath); 
					break;
				case 'files':
				default:
					break;
			}
	
			ini_set('session.gc_maxlifetime', MSD_ONE_DAY*30);
			ini_set('session.cache_expir', MSD_ONE_DAY*30);
			session_set_cookie_params(MSD_ONE_DAY*30);
			session_name('fsid');
			session_start();
		}
		
		if (APPLICATION_ENV=='production') {
			session_regenerate_id();
		}
		
		if (!isset($_SESSION[$this->namespace])) {
			$_SESSION[$this->namespace] = array();
		}
		
		$this->acl = new Msd_Acl();
		$this->acl->addRole(new Msd_Acl_Role('guest'));
	}

	public function destroy()
	{
		session_destroy();
	}
	
	public function __get($var)
	{
		return $this->get($var);
	}
	
	public function __set($var,$val=null)
	{
		$this->set($var, $val);
	}
	
	public function getUid()
	{
		return $this->uid;
	}
	
	public function &acl()
	{
		return $this->acl;
	}

	public function set($var,$val=null)
	{
		$key = md5(APPLICATION_ENV.'_'.$this->namespace);
		if ($val==null) {
			unset($_SESSION[$key][$var]);
		} else {
			$_SESSION[$key][$var] = $val;
		}
	}

	public function get($var)
	{
		$key = md5(APPLICATION_ENV.'_'.$this->namespace);
		return isset($_SESSION[$key][$var]) ? $_SESSION[$key][$var] : null;
	}

}