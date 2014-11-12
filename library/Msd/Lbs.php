<?php

/**
 * LBS相关类库
 * 
 * @author pang
 *
 */

class Msd_Lbs
{
	/**
	 * 原始经纬度到Baidu地图经纬度转换
	 * 
	 * @param double $lng
	 * @param double $lat
	 */
	public static function RawLngLat2Baidu($lng, $lat)
	{
		$cacher = &Msd_Cache_Remote::getInstance();
		$key = 'rll2b_'.md5($lng.'_'.$lat);
		$result = $cacher->get($key);
		
		if (!$result) {
			
		}
		
		return $result;
	}
	
	/**
	 * 经纬度到XY转换
	 * 
	 * @param float $lng
	 * @param float $lat
	 */
	public static function LngLat2XY($lng, $lat)
	{
		$PI = pi();
		$x = round($lng/360*256 * pow(2,17));
		$y = round(log(tan(($lat*$PI/180+$PI/2)/2))*256/$PI/2 * pow(2,17));
		
		return array($x, $y);		
	}
	
	/**
	 * XY到经纬度转换
	 * @param float $x
	 * @param float $y
	 */
	public static function XY2LngLat($x, $y)
	{
		$PI = pi();
		$lng = $x/93206.7556;
		$lat = (atan(exp($y/pow(2, 17)/256*$PI*2))*2-$PI/2)*180/$PI;
		
		return array($lng, $lat);		
	}
	
	/**
	 * 根据经纬度计算两点距离
	 * 
	 * @param float $lng1
	 * @param float $lat1
	 * @param float $lng2
	 * @param float $lat2
	 */
	public static function getDistance($lng1,$lat1,$lng2,$lat2)
	{
		$PI = pi();
		$R = 6.3781e6 ;
		$x = ($lng2-$lng1)*$PI*$R*cos( (($lat1+$lat2)/2) *$PI/180)/180;
		$y = ($lat2-$lat1)*$PI*$R/180;
		$out = hypot($x,$y);
		
		return $out;
	}	
	
	/**
	 * Google地图经纬度去扰
	 * 
	 * @param float $lng
	 * @param float $lat
	 */
	public static function mixLngLatForGoogle($lng, $lat)
	{
		$result = array(
				'lng' => $lng,
				'lat' => $lat
				);
		
		list($x, $y) = self::LngLat2XY($lng, $lat);
		if ($x>0 && $y>0) {
			$lngMin = $lng - 0.02;
			$lngMax = $lng + 0.02;
			$latMin = $lng - 0.02;
			$latMax = $lng + 0.02;
			
			$row = Msd_Dao::table('ll/simple')->parse($lngMin, $latMin, $lngMax, $latMax);
			
			if (isset($row['dislon'])) {
				$result['lng'] += $row['dislng'];
				$result['lat'] += $row['dislat'];
			} else {
				$row = Msd_Dao::table('ll/all')->parse($lngMin, $latMin, $lngMax, $latMax);
				if (isset($row['dislon'])) {
					$result['lng'] += $row['dislon'];
					$result['lat'] += $row['dislat'];
				}
			}
		}
		
		return $result;
	}
}