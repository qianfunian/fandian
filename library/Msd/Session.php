<?php

/**
 * Session会话处理
 * 
 * @author pang
 *
 */

class Msd_Session
{
	protected static $instance = array();
	
	public static function &getInstance($module='production')
	{
		if (self::$instance[$module]==null) {
			$env = ucfirst(strtolower($module));
			$className = class_exists('Msd_Session_'.$env) ? 'Msd_Session_'.$env : 'Msd_Session_Web';
			
			self::$instance[$module] = &call_user_func(array(
					$className,
					'getInstance'
					));
		}
		
		return self::$instance[$module];
	}
}