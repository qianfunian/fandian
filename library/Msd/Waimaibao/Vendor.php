<?php

class Msd_Waimaibao_Vendor extends Msd_Waimaibao_Base {
	protected $vid = '';
	protected static $instances = array ();
	protected $basic = array ();
	protected $extend = array ();
	protected $sign = array ();
	private function __construct($vid) {
		$this->vid = $vid;
		if ($this->vid) {
			$this->basic = &Msd_Dao::table ( 'vendor' )->get ( $this->vid );
		}
	}
	public static function &getInstance($vid) {
		if (! isset ( self::$instances [$vid] )) {
			self::$instances [$vid] = new self ( $vid );
		}
		
		return self::$instances [$vid];
	}
	public static function &BizAreas() {
		$config = &Msd_Config::cityConfig ();
		$areas = array ();
		$cs = $config->spec->biz_area->toArray ();
		foreach ( $cs as $row ) {
			list ( $area, $biz ) = explode ( '|', $row );
			$bizs = explode ( ',', $biz );
			foreach ( $bizs as $biz ) {
				$areas [] = $area . ',' . $biz;
			}
		}
		
		return $areas;
	}
	public static function FilterVendorName($VendorName) {
		if (MSD_FORCE_CITY == 'suzhousip') {
			$VendorName = ltrim ( $VendorName, '(HX)' );
		}
		
		return $VendorName;
	}
	
	/**
	 * 首页的商圈、商家列表
	 */
	public static function IndexRegionVendors() {
	}
	public function &basic() {
		return $this->basic;
	}
	
	/**
	 * 扩展信息
	 */
	public function &extend() {
		if (count ( $this->basic ) > 0 && count ( $this->extend ) == 0) {
			$extend = &Msd_Dao::table ( 'vendor/extend' )->get ( $this->vid );
			if (! $extend) {
				Msd_Dao::table ( 'vendor/extend' )->insert ( array (
						'VendorGuid' => $this->vid,
						'Views' => 0,
						'Favorites' => 0,
						'SmallLogo' => '',
						'BigLogo' => '',
						'CityId' => $this->basic ['CityId'] 
				) );
			} else {
				$extend ['BizAreas'] = $extend ['BizArea'] ? explode ( ',', $extend ['BizArea'] ) : array ();
			}
			
			$this->extend = &$extend;
		}
		
		return $this->extend;
	}
	
	/**
	 * 更新商家资料
	 *
	 * @param array $params        	
	 */
	public function update(array $params = array()) {
		$e = $b = array ();
		isset ( $params ['BigLogo'] ) && $e ['BigLogo'] = $params ['BigLogo'];
		isset ( $params ['SmallLogo'] ) && $e ['SmallLogo'] = $params ['SmallLogo'];
		isset ( $params ['Description'] ) && $b ['Description'] = $params ['Description'];
		isset ( $params ['Views'] ) && $e ['Views'] = $params ['Views'];
		isset ( $params ['Favorites'] ) && $e ['Favorites'] = $params ['Favorites'];
		isset ( $params ['AverageCost'] ) && $e ['AverageCost'] = $params ['AverageCost'];
		isset ( $params ['HotRate'] ) && $e ['HotRate'] = ( int ) $params ['HotRate'];
		isset ( $params ['IsRec'] ) && $e ['IsRec'] = ( int ) $params ['IsRec'];
		isset ( $params ['IsIdxRec'] ) && $e ['IsIdxRec'] = ( int ) $params ['IsIdxRec'];
		isset ( $params ['OrderNo'] ) && $e ['OrderNo'] = ( int ) $params ['OrderNo'];
		isset ( $params ['BizArea'] ) && $e ['BizArea'] = trim ( $params ['BizArea'] );
		
		if (count ( $e ) > 0) {
			$this->extend ();
			
			Msd_Dao::table ( 'vendor/extend' )->doUpdate ( $e, $this->vid );
			
			(isset ( $e ['SmallLogo'] ) && $this->extend ['SmallLogo'] && $e ['SmallLogo'] != $this->extend ['SmallLogo']) && Msd_Files::Del ( $this->extend ['SmallLogo'] );
			(isset ( $e ['BigLogo'] ) && $this->extend ['BigLogo'] && $e ['BigLogo'] != $this->extend ['BigLogo']) && Msd_Files::Del ( $this->extend ['BigLogo'] );
		}
		
		if (count ( $b ) > 0) {
			Msd_Dao::table ( 'vendor' )->doUpdate ( $b, $this->vid );
		}
	}
	
	/**
	 * 商家营业时间字符串
	 *
	 * @param string $VendorGuid        	
	 */
	public static function &C_ServiceTime($VendorGuid, $ServiceName = '普通') {
		$stTable = &Msd_Dao::table ( 'vendor/servicetime' );
		$cacher = &Msd_Cache_Remote::getInstance ();
		$key = 'Vendor_Service_Time_' . md5 ( $VendorGuid . $ServiceName );
		$serviceTimes = $cacher->get ( $key );
		
		if (! $serviceTimes) {
			$serviceTimes = $stTable->VendorServiceTime ( $VendorGuid );
			$cacher->set ( $key, $serviceTimes, MSD_ONE_DAY );
		}
		
		return $serviceTimes;
	}
	
	/**
	 * 商家是否在营业时间范围内
	 *
	 * @param string $VendorGuid        	
	 */
	public static function nInService($VendorGuid) {
		return Msd_Dao::table ( 'vendor' )->InService ( $VendorGuid );
	}
	public static function InService(array $sts, $time = null) {
		$time || $time = time ();
		$flag = false;
		$config = &Msd_Config::appConfig ();
		$left = (( int ) $config->vendor->service_time->left) * 60;
		$right = (( int ) $config->vendor->service_time->right) * 60;
		
		foreach ( $sts as $row ) {
			$StartHour = substr ( $row ['StartTime'], 0, 2 );
			$EndHour = substr ( $row ['EndTime'], 0, 2 );
			$offset = $StartHour > $EndHour ? MSD_ONE_DAY : 0;
			
			for($i = 0; $i < 2; $i ++) {
				$st = new DateTime ( date ( 'Y-m-d', time () + $offset * ($i - 1) ) . ' ' . substr ( $row ['StartTime'], 0, 8 ) );
				$ed = new DateTime ( date ( 'Y-m-d', time () + $offset * $i ) . ' ' . substr ( $row ['EndTime'], 0, 8 ) );
				
				if ($time > ($st->getTimestamp () - $left) && $time < ($ed->getTimestamp () - $right)) {
					$flag = true;
					break;
				}
			}
		}
		
		return $flag;
	}
	
	/**
	 * 商家详情
	 *
	 * @param string $VendorGuid        	
	 */
	public static function &Detail($VendorGuid, $ServiceName = '普通') {
		$data = array ();
		
		$config = &Msd_Config::appConfig ();
		$cConfig = &Msd_Config::cityConfig ();
		// $useCache = ( bool ) $cConfig->db->cache->vendor_enabled;
		$cacheKey = 'Vendor_Detail_' . $VendorGuid;
		$cacher = &Msd_Cache_Remote::getInstance ();
		
		$data = $cacher->get ( $cacheKey );
		if (! $data) {
			$table = &Msd_Dao::table ( 'vendor' );
			$basic = $table->get ( $VendorGuid );
			
			if ($basic) {
				$extendTable = &Msd_Dao::table ( 'vendorextend' );
				$addressTable = &Msd_Dao::table ( 'vendor/address' );
				$stTable = &Msd_Dao::table ( 'vendor/servicetime' );
				$itemTable = &Msd_Dao::table ( 'item' );
				$fileTable = &Msd_Dao::table ( 'attachment' );
				$ctgTable = &Msd_Dao::table ( 'category/group' );
				$cTable = &Msd_Dao::table ( 'category' );
				
				// Web扩展信息
				$extend = $extendTable->get ( $VendorGuid );
				$logo = array ();
				if (! $extend) {
					// 扩展表中没有这个商家？那就插入一个
					$extendTable->insert ( array (
							'VendorGuid' => $VendorGuid,
							'Views' => 0,
							'Favorites' => 0,
							'CityId' => $cConfig->city_id 
					) );
				} else {
					$logo ['small'] = $extend ['SmallLogoId'] ? $fileTable->get ( $extend ['SmallLogoId'] ) : array ();
					$logo ['big'] = $extend ['BigLogoId'] ? $fileTable->get ( $extend ['BigLogoId'] ) : array ();
				}
				
				// 地址信息
				$address = $addressTable->get ( $VendorGuid );
				
				// 分类信息
				$group = $basic ['CtgGroupGuid'] ? $ctgTable->get ( $basic ['CtgGroupGuid'] ) : array ();
				$groups = $basic ['CtgGroupGuid'] ? $cTable->NCategories ( $basic ['CtgGroupGuid'] ) : array ();
				
				// 服务时间
				$serviceTimes = self::C_ServiceTime ( $VendorGuid . $ServiceName );
				
				// 菜品信息
				$items = $itemTable->VendorItems ( $VendorGuid, $ServiceName );
				
				// 菜品分类
				$dbics = array ();
				$tmp = explode ( '|', $cConfig->db->item_category->sort );
				foreach ( $tmp as $_tmp ) {
					list ( $_name, $_key ) = explode ( ',', $_tmp );
					$dbics [$_name] = $_key;
				}
				$ic = array ();
				$realItems = array ();
				foreach ( $items as $item ) {
					$sort = isset ( $dbics [$item ['CtgName']] ) ? $dbics [$item ['CtgName']] : '9999';
					$sort .= '_' . $item ['CtgGuid'];
					
					if (! isset ( $ic [$sort] )) {
						$ic [$sort] = array (
								'CtgName' => $item ['CtgName'],
								'CtgGuid' => $item ['CtgGuid'] 
						);
					}
					$ic [$sort] ['items'] [] = $item;
					
					$realItems [$item ['ItemGuid']] = $item;
				}
				ksort ( $ic );
				
				// 特色菜
				$sign = $itemTable->VendorSignItems ( $VendorGuid, $ServiceName );
				if (! $sign) {
					$i = 1;
					foreach ( $items as $item ) {
						if ($i <= 7 && $item ['HasLogo']) {
							$sign [] = $item;
							$i ++;
						}
						
						if ($i > 7) {
							break;
						}
					}
				}
				
				
				// 商家属性：普通、下午茶、夜宵
				$is_normal = $is_noon = $is_night = false;
				if (preg_match ( '/夜宵/', $basic ['VendorName'] )) {
					$is_night = true;
				} else if (preg_match ( '/下午茶/', $basic ['VendorName'] )) {
					$is_noon = true;
				} else {
					$is_normal = true;
				}
				
				$data = array (
						'basic' => &$basic,
						'extend' => &$extend,
						'address' => &$address,
						'serviceTimes' => &$serviceTimes,
						'items' => &$items,
						'groups' => &$groups,
						'group' => &$group,
						'item_category' => &$ic,
						'sign' => &$sign,
						'is_normal' => $is_normal,
						'is_night' => $is_night,
						'is_noon' => $is_noon 
				);
				
				$cacher->set ( $cacheKey, $data, MSD_ONE_DAY );
			}
		}
		
		return $data;
	}
	
	/**
	 * 商家小Logo地址
	 *
	 * @param array $params        	
	 * @param unknown_type $default        	
	 */
	public static function imageUrl(array $params, $default = '') {
		$config = &Msd_Config::cityConfig ();
		
		$VendorGuid = $params ['VendorGuid'];
		$staticUrl = Msd_Controller::staticUrl ();
		$ImageUrl = $staticUrl . $config->attachment->web_url->vendor . $VendorGuid . '.jpg';
		
		preg_match ( '/^http/i', $ImageUrl ) || $ImageUrl = 'http://' . $_SERVER ['HTTP_HOST'] . $ImageUrl;
		
		return $ImageUrl;
	}
	
	/**
	 * 首页推荐商家Logo
	 *
	 * @param array $params        	
	 * @param unknown_type $default        	
	 */
	public static function imageSpecUrl(array $params, $default = '') {
		$ImageUrl = '';
		
		$VendorGuid = $params ['VendorGuid'];
		$config = &Msd_Config::cityConfig ();
		
		$staticUrl = Msd_Controller::staticUrl ();
		$ImageUrl = $staticUrl . $config->attachment->web_url->vendor_spec . $VendorGuid . '.jpg';
		
		preg_match ( '/^http/i', $ImageUrl ) || $ImageUrl = 'http://' . $_SERVER ['HTTP_HOST'] . $ImageUrl;
		
		return $ImageUrl;
	}
	
	/**
	 * 商家广告大图
	 *
	 * @param array $params        	
	 * @param unknown_type $default        	
	 */
	public static function imageBigUrl(array $params, $default = '') {
		$ImageUrl = '';
		
		$VendorGuid = $params ['VendorGuid'];
		$config = &Msd_Config::cityConfig ();
		
		$staticUrl = Msd_Controller::staticUrl ();
		$ImageUrl = $staticUrl . $config->attachment->web_url->vendor_big . $VendorGuid . '.jpg';
		
		return $ImageUrl;
	}
}
