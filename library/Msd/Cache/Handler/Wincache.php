<?php

/**
 * WinCache接口封装（IIS7专用）
 * 
 * @author pang
 *
 */

class Msd_Cache_Handler_Wincache extends Msd_Cache_Handler_Base
{
	public function __construct(array $options=array())
	{
		
	}
	
	public function &get($key)
	{
		$val = wincache_ucache_get($this->key($key));
		
		return $val;
	}
	
	public function set($key, $val=null, $ttl=0)
	{
		$result = false;
		
		if ($val==null) {
			$result = $this->delete($key);
		} else {
			$result = wincache_ucache_set($this->key($key), $val, (int)$ttl);
		}
		
		return $result;
	}
	
	public function delete($key)
	{
		return wincache_ucache_delete($this->key($key));
	}
}