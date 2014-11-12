<?php

/**
 * 监控工厂
 * 
 * @author pang
 *
 */
class Msd_Monitor
{
	public static function &factory($monitor)
	{
		$tmp = explode('_', $monitor);
		$ms = array();
		foreach ($tmp as $r) {
			$ms[] = ucfirst(strtolower($r));
		}
		
		$class = 'Msd_Monitor_'.implode('_', $ms);
		$obj = class_exists($class) ? new $class : null;
		
		return $obj;
	}	
}