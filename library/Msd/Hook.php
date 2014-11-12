<?php

/**
 * Hook系统
 * 
 * @author pang
 *
 */
class Msd_Hook {
	protected static $hooks = array ();
	private function __construct() {
	}
	
	/**
	 * 注册Hook
	 */
	protected static function registerHooks() {
		if (count ( self::$hooks ) == 0) {
			$hooks = explode ( ',', Msd_Config::cityConfig ()->enabled_hooks );
			foreach ( $hooks as $hook ) {
				$lHook = strtolower ( $hook );
				$className = 'Msd_Hook_' . ucfirst ( $lHook );
				
				self::$hooks [$lHook] = &call_user_func ( array (
						$className,
						'getInstance' 
				) );
			}
		}
	}
	
	/**
	 * 运行Hook事件
	 *
	 * @param string $event        	
	 * @param array $params        	
	 */
	public static function run($event, array $params = array()) {
		self::registerHooks ();
		
		foreach ( self::$hooks as $hook ) {
			if (method_exists ( $hook, $event )) {
				call_user_method ( $event, $hook, $params );
			}
		}
	}
}