<?php

/**
 * 有效性校验器
 * 
 * @author pang
 *
 */

class Msd_Validator
{
	public static function captcha($code)
	{
		return Msd_Image_Captcha::check($code);	
	}
	
	public static function isDefaultLngLat($lng, $lat)
	{
		$result = false;
		$config = &Msd_Config::cityConfig();
		if ($lng==$config->longitude && $lat==$config->latitude) {
			$result = true;
		}	
		
		return $result;
	}
	
	/**
	 * 校验经纬度
	 * 
	 * @param unknown_type $lng
	 * @param unknown_type $lat
	 */
	public static function isLngLat($lng, $lat)
	{
		$result = false;

		if (floatval($lng)>15 && floatval($lat)>15) {
			$result = true;
		}
		
		return $result;
	}
	
	/**
	 * 校验Guid格式
	 * 
	 * @param unknown_type $str
	 */
	public static function isGuid($str)
	{
		//	E9CF7FEA-7D45-4797-A0A3-1BF4C0B58E53
		$pat = '/^([A-F0-9]{8})-([A-F0-9]{4})-([A-F0-9]{4})-([A-F0-9]{4})-([A-Z0-9]{12})$/';
		$result = (bool)preg_match($pat, $str);
		
		return $result;
	}
	
	/**
	 * 是否是合法的手机号码
	 * 
	 * @param string $str
	 */
	public static function isCell($str)
	{
		//$pat = '/^(130|131|132|133|134|135|136|137|138|139|140|141|142|143|144|145|146|147|148|149|150|151|152|153|154|155|156|157|158|159|180|181|182|183|184|185|186|187|188|189)([0-9]{8})$/';
		$pat = '/^1[3|4|5|8][0-9]\d{8}$/';
		$result = (bool)preg_match($pat, $str);
		
		return $result;
	}
	
	public static function isEmptyString($str)
	{
		return (bool)($str!=trim($str));
	}
	
	/**
	 * 校验Email格式
	 * 
	 * @param unknown_type $str
	 */
	public static function isEmail($str)
	{
		$result = filter_var($str, FILTER_VALIDATE_EMAIL);
		
		return $result;
	}
	
	/**
	 * 校验字符串长度
	 * 
	 * @param unknown_type $str
	 * @param unknown_type $min
	 * @param unknown_type $max
	 */
	public static function inValidLength($str, $min=0, $max=50)
	{
		$len = strlen($str);
		return (bool)($len>=$min && $len<=$max);
	}
	
	/**
	 * 是否有效的经纬度
	 * 
	 * @param unknown $lng
	 * @param unknown $lat
	 * @return boolean
	 */
	public static function isValidLngLat($lng, $lat)
	{
		$result = false;
		$absLng = abs($lng);
		$absLat = abs($lat);
		
		if ($lng && $lat && $absLng>=0 && $absLng<=180 && $absLat>=0 && $absLat<=180) {
			$result = true;
		}
		
		return $result;
	}
	
	/**
	 * 根据Mime检查一个文件是否是图片格式
	 * 
	 * @param unknown_type $file
	 */
	public static function isImage($file)
	{
		return preg_match('/image/i', $file['type']);
	}
}