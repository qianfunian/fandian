<?php
/**
 * @desc 缓存加载器
 * @author frank
 *
 */
class Msd_Cache_Loader
{
	public static function &ServiceVendors()
	{
		$config = &Msd_Config::appConfig();
		$cacher = &Msd_Cache_Remote::getInstance();
		$key = 'service_vendors';
		$data = $cacher->get($key);

		if (!$data) {
			$data = array(
					'normal' => array(),
					'night' => array(),
					'noon' => array()
			);
			$vTable = &Msd_Dao::table('vendor');

			$data['normal'] = $vTable->ServiceVendors($config->db->n->service_name->normal);
			$data['night'] = $vTable->ServiceVendors($config->db->n->service_name->night);
			$data['noon'] = $vTable->ServiceVendors($config->db->n->service_name->afternoon);
			$data['fiest'] = $vTable->ServiceVendors($config->db->n->service_name->newyear);
				
			$cacher->set($key, $data);
		}

		return $data;
	}

	/**
	 * 业务
	 *
	 */
	public static function &Services()
	{
		$config = &Msd_Config::cityConfig();
		$cacher = &Msd_Cache_Remote::getInstance();
		$key = 'services';
		$data = $cacher->get($key);

		if (!$data) {
			$data = array();
			$CityId = $config->city_id;

			$rows = Msd_Dao::table('service/item')->getCityServices($CityId,$config->db->guids->service_group);
			
			foreach ($rows as $row) {
				$ServiceGuid = (string)$row['SrvGuid'];
				$row['Disabled'] || $data[$ServiceGuid] = $row;
			}
			
			$cacher->set($key, $data);
		}
		
		return $data;
	}

	public static function &ServiceGroup()
	{
		$cacher = &Msd_Cache_Remote::getInstance();
		$key = 'service_group';
		$data = $cacher->get($key);

		if (!$data) {
			$data = array();
			$rows = &Msd_Dao::table('service/group')->all();
			foreach ($rows as $row) {
				$SrvGrpGuid = (string)$row['SrvGrpGuid'];
				$data[$SrvGrpGuid] = $row;
			}
				
			$cacher->set($key, $data);
		}

		return $data;
	}

	public static function &Vote($vid)
	{
		$cacher = &Msd_Cache_Remote::getInstance();
		$key = 'vote_'.$vid;
		$data = $cacher->get($key);

		if (!$data) {
			$obj = Msd_Votes::getInstance($vid);
			$vote = $obj->getVote();
			$choices = $obj->getChoices();

			$data = array(
		 		'vote' => $vote,
		 		'choices' => $choices
			);
			$cacher->set($key, $data);
		}
			
		return $data;
	}

	public static function &SalesAttributes()
	{

	}

	public static function &Sitemap()
	{
		$config = &Msd_Config::appConfig();
		$cConfig = &Msd_Config::cityConfig();
		$cacher = &Msd_Cache_Remote::getInstance();
		$key = 'sitemap';
		$sitemap = $cacher->get($key);

		$pager = array(
				'page' => 1,
				'limit' => 200,
				'skip' => 0
			);

		if (!$sitemap) {
			$sitemap = array(
					'vendors' => array(),
					'anns' => array(),
					'ctgs' => array()
			);

			$ctgs = &Msd_Waimaibao_Category::Vendor();

			foreach ($ctgs as $ctg) {
				if ($ctg['CtgStdName']==$config->db->n->ctg_std_name->vendor) {
					$rows[] = array(
							'CtgName' => $ctg['CtgName'],
							'url' => 'vendor/index/category/'.$ctg['CtgName'],
							'priority' => '0.9',
							'lastmod' => date('Y-m-d'),
							'changefreq'	=> 'daily'
					);
				}
			}
				
			$sitemap['ctgs'] = $rows;
				
			$VendorGuids = array();
			$tmp = &Msd_Dao::table('vendor')->search($pager, array(
					'Disabled' => 0,
					'AreaGuid' => $cConfig->db->guids->area->toArray(),
					'CityId' => $cConfig->city_id
				), array(
					'HotRate' => 'DESC'
				));
			foreach ($tmp as $row) {
				if (!in_array($row['VendorGuid'], $VendorGuids)) {
					$sitemap['vendors'][] = array(
							'VendorName' => $row['VendorName'],
							'url' => 'vendor/'.$row['VendorName'],
							'priority' => '0.8',
							'lastmod' => date('Y-m-d'),
							'changefreq' => 'daily',
					);
					$VendorGuids[] = $row['VendorGuid'];
				}
			}

			$pager['limit'] = 5;
			$table = &Msd_Dao::table('article');
			//	公告
			$sitemap['anns'] = $table->search($pager,  array(
					'CategoryId' => array(
							$cConfig->db->article->category->announce
					),
					'Regions' => Msd_Waimaibao_Region::RegionGuids(),
					'PubFlag' => '1',
					'passby_pager' => true,
			), array(
					'OrderNo' => 'ASC',
					'PubTime' => 'DESC'
			));

			//	关于我们
			$sitemap['aboutus'] = $table->search($pager,  array(
					'CategoryId' => array(
							$cConfig->db->article->category->aboutus
					),
					'Regions' => Msd_Waimaibao_Region::RegionGuids(),
					'PubFlag' => '1',
					'passby_pager' => true,
			), array(
					'OrderNo' => 'ASC',
					'PubTime' => 'DESC'
			));

			//	站内帮助
			$sitemap['help'] = $table->search($pager,  array(
					'CategoryId' => array(
							$cConfig->db->article->category->help
					),
					'Regions' => Msd_Waimaibao_Region::RegionGuids(),
					'PubFlag' => '1',
					'passby_pager' => true,
			), array(
					'OrderNo' => 'ASC',
					'PubTime' => 'DESC'
			));

			//	服务说明
			$sitemap['service'] = $table->search($pager,  array(
					'CategoryId' => array(
							$cConfig->db->article->category->service
					),
					'Regions' => Msd_Waimaibao_Region::RegionGuids(),
					'PubFlag' => '1',
					'passby_pager' => true,
			), array(
					'OrderNo' => 'ASC',
					'PubTime' => 'DESC'
			));

			//	其他
			$sitemap['others'] = $table->search($pager,  array(
					'CategoryId' => array(
							$cConfig->db->article->category->others
					),
					'Regions' => Msd_Waimaibao_Region::RegionGuids(),
					'PubFlag' => '1',
					'passby_pager' => true,
			), array(
					'OrderNo' => 'ASC',
					'PubTime' => 'DESC'
			));

			$cacher->set($key, $sitemap, MSD_ONE_DAY);
		}

		return $sitemap;
	}

	public static function &ExpressForce()
	{
		$cConfig = &Msd_Config::cityConfig();

		$data = array();
		$cacher = &Msd_Cache_Remote::getInstance();
		$cacheKey = 'express_force';
		$data = $cacher->get($cacheKey);

		if (!$data) {
			$data = Msd_Dao::table('expressforce')->last($cConfig->db->guids->root_region);
			$cacher->set($cacheKey, $data, 300);
		}

		return $data;
	}
	
	/**
	 * Mini超市数据缓存
	 */
	public static function &MiniMarket() {
		$cConfig = &Msd_Config::cityConfig ();
		$VendorGuid = $cConfig->db->guids->mini_market;
		
		$data = &Msd_Waimaibao_Vendor::Detail ( $VendorGuid );
		return $data;
	}

	/**
	 *
	 * @return Ambigous <multitype:, unknown>
	 */
	public static function &ServerCategories()
	{
		$cacher = &Msd_Cache_Remote::getInstance();
		$data = array();
		$cacheKey = 'server_categories';
		$data = $cacher->get($cacheKey);

		if (!$data) {
			$table = &Msd_Dao::table('category');
			$rows = $table->fetchAll(null, null, 9999, 0);
			$data = array();
				
			foreach ($rows as $row) {
				$CtgStdGuid = (string)$row['CtgStdGuid'];
				$CtgGuid = (string)$row['CtgGuid'];
				$data[$CtgStdGuid][$CtgGuid] = $row['CtgName'];
			}
				
			$cacher->set($cacheKey, $data);
		}

		return $data;
	}

	/**
	 * @desc 系统参数配置缓存
	 * @return multitype:unknown
	 */
	public static function &Systemvars() {
		$cacher = &Msd_Cache_Remote::getInstance ();
		$cacheKey = 'nsystemvars';
		$data = $cacher->get ( $cacheKey );
		
		if (! $data) {
			$table = &Msd_Dao::table ( 'systemvars' );
			$rows = $table->all ( Msd_Config::cityConfig ()->db->guids->root_region );
			$data = array ();
			
			foreach ( $rows as $row ) {
				$data [$row ['DataKey']] = $row ['DataValue'];
			}
			
			$cacher->set ( $cacheKey, $data );
		}
		
		return $data;
	}

	/**
	 * 商家菜品分类
	 */
	public static function &ics()
	{
		$cacher = &Msd_Cache_Remote::getInstance();
		$key = 'ics';
		$data = $cacher->get($key);

		if (!$data) {
			$pager = array(
					'limit' => 999,
					'page' => 1,
					'skip' => 0
			);
			$rows = &Msd_Dao::table('category')->search($pager, array(
					'CtgStdName' => Msd_Config::cityConfig()->db->n->ctg_std_name->item
			), array());
			$data = array();
				
			foreach ($rows as $row) {
				$CtgGuid = (string)$row['CtgGuid'];
				$data[$CtgGuid] = $row['CtgName'];
			}
				
			$cacher->set($key, $data);
		}

		return $data;
	}

	/**
	 * 订单播报
	 *
	 */
	public static function &orderAnnounce()
	{
		$cacher = &Msd_Cache_Remote::getInstance();
		$key = 'order_announce';
		$data = &$cacher->get($key);
    
		if (!$data) {
			$data = &Msd_Dao::table('order/announce')->last21(Msd_Waimaibao_Region::RegionGuids());
			$cacher->set($key, $data, 3600);
		}

		return $data;
	}
	
	/**
	 * @desc 网站首页相关缓存
	 * @param unknown_type $cityId
	 * @return Ambigous <multitype:, multitype:unknown >
	 */
	public static function &siteIndex($cityId = null) {
		$systemvars = &self::Systemvars ();
		$cityConfig = &Msd_Config::cityConfig ();
		
		$AreaGuids = $cityConfig->db->guids->area->toArray ();
		
		$cacher = &Msd_Cache_Remote::getInstance ();
		$cacheKey = 'site_index';
		$data = $cacher->get ( $cacheKey );
		$newCacheLoaded = false;
		
		if (! $data ['categories']) {
			$config = &Msd_Config::appConfig ();
			$ctgs = &Msd_Waimaibao_Category::Vendor ();
			$categories = array ();
			foreach ( $ctgs as $ctg ) {
				if ($ctg ['CtgStdName'] == $config->db->n->ctg_std_name->vendor && ! in_array ( $ctg ['CtgName'], $categories )) {
					$categories [$ctg ['CtgGuid']] = $ctg ['CtgName'];
				}
			}
			$data ['categories'] = $categories;
			$newCacheLoaded = true;
		}
		
		if (! $data ['vendor_ranks']) {
			$rows = &Msd_Dao::table ( 'vendor' )->topSales ( $AreaGuids );
			$data ['vendor_ranks'] = &$rows;
			$newCacheLoaded = true;
		}
		
		if (! $data ['announce']) {
			$pager = array (
					'limit' => 5,
					'pg' => 1 
			);
			
			$table = &Msd_Dao::table ( 'article' );
			
			$params = array ();
			$params ['PubFlag'] = '1';
			$params ['CategoryId'] = array (
					Msd_Config::cityConfig ()->db->article->category->announce 
			);
			$params ['Regions'] = &$AreaGuids;
			
			$sort = array (
					'OrderNo' => 'ASC',
					'PubTime' => 'DESC' 
			);
			
			$announce = $table->search ( $pager, $params, $sort );
			$data ['announce'] = &$announce;
			$newCacheLoaded = true;
		}
		
		if (! $data ['banners']) {
			$data ['banners'] = unserialize ( $systemvars ['nhomepage'] );
			
			$newCacheLoaded = true;
		}
		
		if (! $data ['idx_sb']) {
			$data ['idx_sb'] = unserialize ( $systemvars ['scroll_banner'] );
			
			$newCacheLoaded = true;
		}
		
		if (! $data ['rec_vendors']) {
			$data ['rec_vendors'] = &Msd_Dao::table ( 'vendor' )->IdxRecVendors ( $AreaGuids );
			$newCacheLoaded = true;
		}
		
		if (! $data ['hot_bcs']) {
			$data ['hot_bcs'] = &Msd_Dao::table ( 'item' )->IdxHot ( $AreaGuids );
			$newCacheLoaded = true;
		}
		
		if (! $data ['biz_area']) {
			$data ['biz_area'] = array ();
			$bizAreas = $cityConfig->spec->biz_area->toArray ();
			$_pager = array (
					'limit' => 999,
					'page' => 1,
					'offset' => 0,
					'skip' => 0 
			);
			
			foreach ( $bizAreas as $bizArea ) {
				list ( $AreaName, $subArea ) = explode ( '|', $bizArea );
				$subAreas = explode ( ',', $subArea );
				$_data = array (
						'RegionName' => $AreaName 
				);
				$_areas = array ();
				foreach ( $subAreas as $area ) {
					$_areas [] = $AreaName . ',' . $area;
					$_vendors = &Msd_Dao::table ( 'vendor' )->search ( $_pager, array (
							'BizArea' => $AreaName . ',' . $area,
							'Disabled' => 0,
							'passby_pager' => 1,
							'exclude_mini' => true 
					), array (
							'HotRate' => 'DESC' 
					) );
					$_data ['vendors'] [$area] = $_vendors;
				}
				
				$_rPager = $_pager;
				$_rPager ['limit'] = 5;
				$_rvendors = &Msd_Dao::table ( 'vendor' )->search ( $_rPager, array (
						'BizArea' => $_areas,
						'Disabled' => 0,
						'IsRec' => 1,
						'passby_pager' => 1 
				), array (
						'HotRate' => 'DESC' 
				) );
				
				$_data ['rec_vendors'] = $_rvendors;
				$data ['biz_area'] [] = $_data;
			}
			
			$newCacheLoaded = true;
		}
		
		if ($newCacheLoaded) {
			$cacher->set ( $cacheKey, $data );
		}
		
		return $data;
	}

	/**
	 * 文章分类缓存
	 *
	 */
	public static function &categories()
	{
		$cityConfig = &Msd_Config::cityConfig();
		$cacher = &Msd_Cache_Remote::getInstance();
		$cacheKey = 'Categories';
		$data = $cacher->get($cacheKey);

		if (!$data) {
			$table = &Msd_Dao::table('article/category');
				
			$pager = array();
			$pager['limit'] = 9999;
			$pager['page'] = 1;
				
			$rows = $table->search($pager, array(
					'CityId' => $cityConfig->city_id
			), array(
					'OrderNo' => 'ASC'
			));
			$data = array();
			foreach ($rows as $row) {
				$data[$row['CategoryId']] = $row['CategoryName'];
			}

			$result = $cacher->set($cacheKey, $data);
		}

		return $data;
	}

	/**
	 * 对外API Key缓存
	 *
	 */
	public static function &ApiKeys()
	{
		$cacher = &Msd_Cache_Remote::getInstance();
		$cacheKey = 'ApiKeys';
		$data = $cacher->get($cacheKey);

		if (!$data) {
			$table = &Msd_Dao::table('api/keys');
				
			$pager = array();
			$pager['limit'] = 9999;
			$pager['page'] = 1;
				
			$rows = $table->search($pager, array(), array());
			$data = array();
			foreach ($rows as $row) {
				$data[$row['ApiKey']] = $row;
			}

			$result = $cacher->set($cacheKey, $data);
		}

		return $data;
	}

	/**
	 * 地区分类缓存
	 *
	 */
	public static function &Regions()
	{
		$cacher = &Msd_Cache_Remote::getInstance();
		$cacheKey = 'Regions';

		$regions = $cacher->get($cacheKey);
		if (!$regions) {
			$regions = &Msd_Waimaibao_Region::GetSiteRegions();
			$cacher->set($cacheKey, $regions);
		}

		return $regions;
	}

	/**
	 * 接单系统字典缓存
	 *
	 */
	public static function &Enums()
	{
		$cacher = &Msd_Cache_Remote::getInstance();
		$cacheKey = 'Enums';
		$data = $cacher->get($cacheKey);

		if (!$data) {
			$data = &Msd_Waimaibao_Enum::load();
			$cacher->set($cacheKey, $data);
		}

		return $data;
	}

	/**
	 * 订单状态
	 *
	 * @return array
	 */
	public static function &OrderStatus()
	{
		$cacher = &Msd_Cache_Remote::getInstance();
		$cacheKey = 'noss';
		$data = $cacher->get($cacheKey);

		if (!$data) {
			$data = &Msd_Waimaibao_Order_Status::all();
			$flag = $cacher->set($cacheKey, $data);
		}

		return $data;
	}
}
