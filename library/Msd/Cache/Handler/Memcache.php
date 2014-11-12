<?php

/**
 * Memcache扩展接口封装
 * 
 * @author pang
 *
 */

class Msd_Cache_Handler_Memcache extends Msd_Cache_Handler_Base
{
	protected $memcache = null;
	
	public function __construct(array $options=array())
	{
		parent::__construct($options);
		
		$this->memcache = new Memcache();

		if (is_array($options['hosts'])) {
			foreach ($options['hosts'] as $host) {
				list($h, $p) = explode(':', $host);
				$p || $p = 11211;
				$this->memcache->addServer($h, $p);
			}
		}
	}
	
	public function &get($key)
	{
		$val = $this->memcache->get($this->key($key));

		return $val;
	}
	
	public function set($key, $val=null, $ttl=0, $zip=0)
	{
		$result = false;
		
		if ($val==null) {
			$result = $this->delete($key);
		} else {
			$result = $this->memcache->set($this->key($key), $val, 0, $ttl);
		}

		return $result;
	}
	
	public function delete($key)
	{
		return $this->memcache->delete($this->key($key));
	}
	
	public function increase($key, $step=1)
	{
		return $this->memcache->increment($this->key($key), $step);
	}
}
