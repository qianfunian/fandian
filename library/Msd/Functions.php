<?php

/**
 * 一些特别的函数
 * 
 * @author pang
 *
 */

class Msd_Functions
{
	/**
	 * 日期格式化
	 * 
	 * @param unknown $string
	 * @param string $format
	 * @return string
	 */
	public static function Dt($string, $format='datetime')
	{
		try {
			$dt = new DateTime($string);
			$string = date('Y-m-d H:i:s', $dt->getTimestamp());
		} catch (Exception $e){}
	
		switch ($format) {
			case 'date':
				$result = substr($string, 0, 10);
				break;
			case 'time':
				$result = substr($string, 11, 10);
				break;
			case 'short_datetime':
				$result = substr($string, 5, 14);
				break;
			default:
				$result = substr($string, 0, -3);
				break;
		}
	
		return $result;
	}
	
	/**
	 * 获取系统负载值
	 * 
	 */
	public static function LoadAverage()
	{
		$load = '--';
		
		if (function_exists('sys_getloadavg')) {
			$tmp = sys_getloadavg();
			$load = $tmp[0];
		}
		
		return $load;		
	}
	
	/**
	 * 自定义的数组合并
	 * 
	 * 
	 */
	public static function ArrayMerge()
	{
		$array_arr = func_get_args();
		$ss_arr = array();
		reset($array_arr);
		
		for ($i=0;$i<count($array_arr);$i++) {
			$ary=$array_arr[key($array_arr)];
			if (!is_array($ary)) {
				$ss_arr[]=$ary;
			} else {
				while (list ($key, $val) = each ($ary)) {
					$ss_arr[$key]=$val;
				}
			}
			next($array_arr);
		}
		 
		return $ss_arr;		
	}
}