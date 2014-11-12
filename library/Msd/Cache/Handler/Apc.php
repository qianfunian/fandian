<?php

/**
 * APC扩展接口封装
 * 
 * @author pang
 *
 */

class Msd_Cache_Handler_Apc extends Msd_Cache_Handler_Base
{
	public function __construct(array $options=array())
	{
		parent::__construct($options);
	}
	
	public function &get($key)
	{
		$val = apc_fetch($this->key($key));
		
		return $val;
	}
	
	public function set($key, $val=null, $ttl=0)
	{
		$result = false;
		
		if ($val==null) {
			$result = $this->delete($key);
		} else {
			$result = apc_store($this->key($key), $val, (int)$ttl);
		}
		
		return $result;
	}
	
	public function delete($key)
	{
		return apc_delete($this->key($key));
	}
}