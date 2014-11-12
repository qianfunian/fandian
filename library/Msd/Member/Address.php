<?php

class Msd_Member_Address extends Msd_Member_Base
{
	protected static $instances = array();
	protected static $dao = null;
	
	protected function __construct($uid)
	{
		parent::__construct($uid);
		if (self::$dao==null) {
			self::$dao = &Msd_Dao::table('customer/address');
		}
	}
	
	public static function &getInstance($uid)
	{
		if (!isset(self::$instances[$uid])) {
			self::$instances[$uid] = new self($uid);
		}
		
		return self::$instances[$uid];
	}
	
	public function &last5Address()
	{
		$cacher = &Msd_Cache_Remote::getInstance();
		$cacheKey = 'last5_address_'.md5($this->uid);
		$data = $cacher->get($cacheKey);

		if (!$data) {
			$data = &self::$dao->last5Address($this->uid);
			if ($data) {
				$cacher->set($cacheKey, $data, 3600*24);
			}
		}
		
		$data || $data = array();
		
		return (array)$data;
	}
	
}