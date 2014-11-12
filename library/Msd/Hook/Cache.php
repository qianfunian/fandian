<?php

class Msd_Hook_Cache extends Msd_Hook_Base
{
	protected static $instance = null;
	
	public static function &getInstance()
	{
		if (self::$instance==null) {
			self::$instance = new self();
		}
		
		return self::$instance;
	}
	
	public function ArticleCategoryChanged(array $params=array())
	{
		Msd_Cache_Loader::categories();
	}
	
	public function ApiKeysChanged(array $params=array())
	{
		Msd_Cache_Loader::ApiKeys();
	}
	
	public function RegionChanged(array $params=array())
	{
		Msd_Cache_Loader::Regions();
	}
	
	public function ArticleChanged(array $params=array())
	{
		$key = 'article_'.$params['id'];
		$data = &$params['data'];
		
		Msd_Cache_Remote::getInstance()->set($key, $data);
		
		Msd_Cache_Clear::index();
	}
	
	public function ArticleDeleted(array $params=array())
	{
		$key = 'article_'.$params['id'];
		Msd_Cache_Remote::getInstance()->delete($key);
		
		Msd_Cache_Clear::index();
	}
	
	/**
	 * 程序启动时的缓存初始化
	 * 
	 * 
	 */
	public function BeforeBootStrap()
	{
		Msd_Cache_Loader::Services();
		Msd_Cache_Loader::ServiceGroup();
		Msd_Cache_Loader::Enums();
		Msd_Cache_Loader::Regions();
		Msd_Cache_Loader::OrderStatus();
		Msd_Cache_Loader::categories();
		Msd_Cache_Loader::Systemvars();
		Msd_Cache_Loader::ServerCategories();
		Msd_Cache_Loader::MiniMarket();
		Msd_Cache_Loader::ServiceVendors();
	}
}
