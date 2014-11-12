<?php

/**
 * 缓存清理
 * 
 * @author pang
 *
 */

class Msd_Cache_Clear
{
	public static function Services()
	{
		Msd_Cache_Remote::getInstance()->set('services');	
		Msd_Cache_Remote::getInstance()->set('service_vendors');
	}
	
	public static function ServiceGroup()
	{
		$cacher = &Msd_Cache_Remote::getInstance();
		$key = 'service_group';
		$cacher->set($key);	
	}
	
	public static function vote($vid=null)
	{
		$config = &Msd_Config::cityConfig();
		$cacher = &Msd_Cache_Remote::getInstance();
		
		if (is_numeric($vid)) {
			$key = 'vote_'.$vid;
			$cacher->set($key);	
		} else {
			$votes = &Msd_Dao::table('votes')->all();
			if (is_array($votes)) {
				foreach ($votes as $row) {
					$key = 'vote_'.$row['AutoId'];
					$cacher->set($key);
				}
			}
			
			$modules = explode(',', $config->votes->modules);
			foreach ($modules as $Module) {
				$cacher->set('mvotes_'.md5($Module));
			}
		}
	}
	
	public static function config()
	{
		Msd_Cache_Local::getInstance()->set('application_ini', null);
	}
	
	public static function orderAnnounce()
	{
		Msd_Cache_Remote::getInstance()->set('order_announce', null);
	}
	
	public static function ics()
	{
		$cacher = &Msd_Cache_Remote::getInstance();
		$cacher->set('ics');
	}
	
	public function vendorCategories()
	{
		$cacher = &Msd_Cache_Remote::getInstance();
		$cacher->set('vendor_categories');
	}
	
	public static function index()
	{
		self::orderAnnounce();
		$config = &Msd_Config::cityConfig();
		
		$cacher = &Msd_Cache_Remote::getInstance();
		
		$cacher->set('homepage', null);
		$cacher->set('site_index', null);
		$cacher->set('articlelist_'.$config->db->article->category->service, null);
		$cacher->set('articlelist_'.$config->db->article->category->others, null);
		$cacher->set('articlelist_'.$config->db->article->category->aboutus, null);
		
		$_regions = &Msd_Cache_Loader::Regions();
		$regions = array();
		foreach ($_regions as $row) {
			$ckey = 'irv_'.$row['RegionId'];
			$cacher->set($ckey);
		}
	}
	
	public static function Sitemap()
	{
		Msd_Cache_Remote::getInstance()->set('sitemap', null);
	}
	
	public static function ServerCategories()
	{
		Msd_Cache_Remote::getInstance()->set('server_categories', null);
	}
	
	public static function vars()
	{
		Msd_Cache_Remote::getInstance()->set('vars', null);
		Msd_Cache_Remote::getInstance()->set('nsystemvars', null);
	}
	
	public static function categories()
	{
		Msd_Cache_Remote::getInstance()->set('Categories', null);
		
		Msd_Hook::run('ArticleCategoryChanged');
	}
	
	public static function ApiKeys()
	{
		Msd_Cache_Remote::getInstance()->set('ApiKeys', null);
		
		Msd_Hook::run('ApiKeysChanged');
	}
	
	public static function Regions()
	{
		Msd_Cache_Remote::getInstance()->set('Regions', null);
		Msd_Cache_Remote::getInstance()->set('coords', null);
		Msd_Hook::run('RegionChanged');
	}
	
	public static function Vendors($VendorGuid='')
	{
		$cacher = &Msd_Cache_Remote::getInstance();
		
		if ($VendorGuid) {
			$cacher->set('Vendor_Detail_'.$VendorGuid);	
			$cacher->set('vendor_big_url_'.$VendorGuid);
			$cacher->set('vendor_urls_'.$VendorGuid);
			$cacher->set('vsi_'.$VendorGuid);
		} else {
			$guids = &Msd_Dao::table('vendor')->guids();
			foreach ($guids as $guid) {
				$cacher->set('Vendor_Detail_'.$guid);
				$cacher->set('vendor_big_url_'.$guid);
				$cacher->set('vendor_urls_'.$guid);
				$cacher->set('vsi_'.$guid);
			}
		}
		
		self::Items();
	}
	
	public static function Items($ItemGuid='')
	{
		$cacher = &Msd_Cache_Remote::getInstance();
		
		if ($ItemGuid) {
			$cacher->set('iurl_'.$ItemGuid);
			$cacher->set('turl_'.$ItemGuid);
			$cacher->set('iturl_'.$ItemGuid);
			$cacher->set('iburl_'.$ItemGuid);
		} else {
			$guids = &Msd_Dao::table('item')->guids();
			foreach ($guids as $guid) {
				$cacher->set('iurl_'.$guid);
				$cacher->set('turl_'.$guid);
				$cacher->set('iturl_'.$guid);
				$cacher->set('iburl_'.$guid);
			}			
		}
	}
	
	public static function Enums()
	{
		$cacher = &Msd_Cache_Remote::getInstance();
		$key = 'Enums';
		$cacher->set($key);
		$data = &Msd_Waimaibao_Enum::load();
		$cacher->set($key, $data);
	}
	
	public static function OrderStatus()
	{
		$cacher = &Msd_Cache_Remote::getInstance();
		$key = 'noss';
		$cacher->set($key);
		
		Msd_Cache_Loader::OrderStatus();
	}
	
	public static function Gift()
	{
		Msd_Cache_Remote::getInstance()->set('gift_index', null);
	}
	
	public static function NewYear()
	{
		Msd_Cache_Remote::getInstance()->set('new_year', null);
	}
	
	public static function Lsas()
	{
		Msd_Cache_Remote::getInstance()->set('lsas', null);
	}
}
