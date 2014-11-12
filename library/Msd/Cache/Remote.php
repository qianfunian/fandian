<?php

/**
 * 远端缓存处理（Memcached、Mecachedb）
 * 保存与机器无关的缓存，如某些数据库查询结果
 * 
 * @author pang
 *
 */

class Msd_Cache_Remote extends Msd_Cache_Base {
	protected static $instances = null;
	private function __construct() {
	}
	
	/**
	 * 获取远端缓存实例
	 */
	public static function &getInstance($cityId = MSD_FORCE_CITY) {
		if (self::$instances [$cityId] == null) {
			$config = Msd_Config::cityConfig ()->cache->handler->remote;
			$options = array (
					'hosts' => explode ( '|', $config->hosts ),
					'cityId' => $cityId 
			);
			self::$instances [$cityId] = self::loadHandler ( 'Memcache', $options );
		}
		
		return self::$instances [$cityId];
	}
}