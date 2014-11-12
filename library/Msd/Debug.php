<?php

/**
 * 程序调试器
 * 用于在页面中dump一些变量信息
 * 
 * @author pang
 *
 */

require_once 'Zend/Debug.php';

class  Msd_Debug extends Zend_Debug
{
	protected static $heartBeat = 0;
	
	public static function heartBeart()
	{
		echo self::$heartBeat++;
	}
	
	public static function dump($val)
	{
		@header('Content-Type:text/html; charset=utf-8');
		return parent::dump($val);
	}
}