<?php

/**
 * 预封装的字符集转换
 * 
 * @author pang
 *
 */

class Msd_Iconv
{
	public static function g2u($string)
	{
		return iconv('GBK', 'UTF-8', $string);
	}
	
	public static function u2g($string)
	{
		return iconv('UTF-8', 'GBK', $string);
	}
	
	public static function ustrlen($string)
	{
		return iconv_strlen($string, 'UTF-8');
	}
	
	public static function gstrlen($string)
	{
		return iconv_strlen($string, 'GBK');
	}
	
	public static function usubstr($string, $offset=0, $length=0)
	{
		$length || $length = self::ustrlen($string);
		return iconv_substr($string, $offset, $length, 'UTF-8');
	}
	
	public static function gsubstr($string, $offset=0, $length=0)
	{
		$length || $length = self::gstrlen($string);
		return iconv_substr($string, $offset, $length, 'GBK');
	}
}