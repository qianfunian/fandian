<?php

/**
 * 持续化远端缓存处理（Memcached、Mecachedb）
 * 保存与机器无关的缓存，如某些数据库查询结果
 * 
 * @author pang
 *
 */

class Msd_Cache_Db extends Msd_Cache_Base
{
	protected static $instance = null;
	
	private function __construct()
	{
		
	}
	
	/**
	 * 获取远端缓存实例
	 * 
	 */
	public static function &getInstance()
	{
		if (self::$instance==null) {
			$config = Msd_Config::cityConfig()->cache->handler->db;
			$handler = class_exists('Memcached') ? 'memcached' : 'memcache';

			$options = array(
				'hosts' => explode('|', $config->hosts)
				);

			self::$instance = self::loadHandler($handler ? $handler : 'Memcache', $options);
		}
		
		return self::$instance;
	}
}