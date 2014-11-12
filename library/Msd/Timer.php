<?php

/**
 * 定时器
 * 
 * @author pang
 *
 */
class Msd_Timer
{
	protected static $timers = array();
	
	public static function start($key)
	{
		$mtime = explode(' ', microtime());
		self::$timers[$key] = $mtime[1]+$mtime[0];
	}

	public static function end($key, $keep=false)
	{
		$mtime = explode(' ', microtime());
		$end = $mtime[1]+$mtime[0];
		$start = self::$timers[$key];
	
		if (!$keep) {
			unset(self::$timers[$key]);
		}
	
		return round(($end-$start), 5);	
	}
}