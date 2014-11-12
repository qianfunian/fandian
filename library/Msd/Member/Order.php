<?php

class Msd_Member_Order extends Msd_Member_Base
{
	protected static $instances = array();
	protected static $dao = null;
	
	protected function __construct($uid)
	{
		parent::__construct($uid);
		if (self::$dao==null) {
			self::$dao = &Msd_Dao::table('order');
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
			$data = &Msd_Dao::table('sales')->last5Address($this->uid);
			if ($data) {
				$cacher->set($cacheKey, $data);
			}
		}

		return (array)$data;
	}
	
}