<?php

class Msd_Waimaibao_Enum extends Msd_Waimaibao_Base
{
	protected static $enums = array();
	
	public static function &load()
	{
		$data = array();
		$rows = Msd_Dao::table('enum')->fetchAll('', '', 999);

		foreach ($rows as $row) {
			$key = md5($row['Language'].$row['EnumName']);
			$data[$key][$row['SortId']] = $row->toArray();
		}

		return $data;
	}
	
	public static function get($key, $lang='zh-CN')
	{
		if (count(self::$enums)==0) {
			$cacher = &Msd_Cache_Remote::getInstance();
			$cacheKey = 'Enums';
			$data = $cacher->get($cacheKey);
			if (!$data) {
				$data = &self::load();
				$cacher->set($cacheKey, $data);
			}
			
			self::$enums = &$data;
		}
		$key = md5($lang.$key);
		
		return isset(self::$enums[$key]) ? (array)self::$enums[$key] : array();
	}
}