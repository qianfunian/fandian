<?php

/**
 * 缓存处理器基类
 * 
 * @author pang
 *
 */

abstract class Msd_Cache_Handler_Base
{
	protected $options = array();
	protected $cityId = '';
	
	public function __construct(array $options=array())
	{
		$this->cityId = $options['cityId'] ? $options['cityId'] : MSD_FORCE_CITY;
	}
	
	protected function key($key)
	{
		$config = Msd_Config::cityConfig($this->cityId)->cache->prefix;
		return $config.$key.FANDIAN_APP_BRANCH;
	}
	
	abstract public function &get($key);
	abstract public function set($key, $val=null, $ttl=0);
	abstract public function delete($key);

	public function increase($key, $step=1)
	{
		return $this->set($key, intval($this->get($key))+$step);
	}
}