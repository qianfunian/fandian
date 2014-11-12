<?php

/**
 * 配置项解析、加载
 * 
 */
require_once 'Zend/Config/Ini.php';
require_once 'Msd/Cache/Local.php';

class Msd_Config {
	protected static $config = null;
	protected static $cityConfig = array ();
	private function __construct() {
	}
	public static function getInstance() {
		if (! self::$config) {
			if (APPLICATION_ENV == 'production' && ! MSD_FORCE_RELOAD_CONFIG) {
				$cacher = &Msd_Cache_Local::getInstance ();
				self::$config = $cacher->get ( 'application_ini' );
				if (! self::$config) {
					self::$config = new Zend_Config_Ini ( APPLICATION_PATH . '/configs/application.ini', APPLICATION_ENV );
					$cacher->set ( 'application_ini', self::$config );
				}
			} else {
				self::$config = new Zend_Config_Ini ( APPLICATION_PATH . '/configs/application.ini', APPLICATION_ENV );
			}
		}
		
		return self::$config;
	}
	
	/**
	 * 获取应用程序配置，即msd开头的配置项
	 *
	 * @return Zend_Config_Ini
	 */
	public static function &appConfig() {
		self::getInstance ();
		$msd = self::$config->msd;
		return $msd;
	}
	
	/**
	 * 获取城市相关配置
	 *
	 * @param string $city        	
	 * @return Zend_Config_Ini:
	 */
	public static function &cityConfig($city = MSD_FORCE_CITY) {
		if (! isset ( self::$cityConfig [$city] )) {
			if (APPLICATION_ENV == 'production' && ! MSD_FORCE_RELOAD_CONFIG) {
				$cacher = &Msd_Cache_Local::getInstance ();
				self::$cityConfig [$city] = $cacher->get ( 'city_ini_' . $city );
				if (! self::$cityConfig [$city]) {
					self::$cityConfig [$city] = new Zend_Config_Ini ( APPLICATION_PATH . '/configs/city/' . $city . '.ini', APPLICATION_ENV );
					$cacher->set ( 'city_ini_' . $city, self::$cityConfig [$city] );
				}
			} else {
				self::$cityConfig [$city] = new Zend_Config_Ini ( APPLICATION_PATH . '/configs/city/' . $city . '.ini', APPLICATION_ENV );
			}
		}
		
		return self::$cityConfig [$city];
	}
}