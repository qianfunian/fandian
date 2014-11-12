<?php

/**
 * 自动加载器
 * 
 * @author pang
 *
 */

class Msd_Autoloader
{
	protected static $instance = null;
	protected static $included = array();
	
	private function __construct()
	{
		
	}
	
	public static function &getInstance()
	{
		if (self::$instance==null) {
			self::$instance = new self();
		}
		
		return self::$instance;
	}
	
	public function register()
	{
		spl_autoload_register(array($this, 'loadClass'));
	}
	
	public function loadClass($class)
	{
		$result = true;
		$className = strtolower($class);
		
		if (!class_exists($className) && !interface_exists($className) && !class_exists($class) && !interface_exists($class) || $class==__CLASS__) {
			$result = self::loadMsdClass($class);
		}
		
		return $result;
	}
	
	/**
	 * 加载MSD_开头的类库
	 * 
	 * @param string $class
	 */
	protected static function loadMsdClass($class)
	{
		if (!in_array($class, self::$included)) {
			try {
				$file = APPLICATION_PATH . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'library' . DIRECTORY_SEPARATOR .implode(DIRECTORY_SEPARATOR, explode('_', $class)).'.php';
				if (file_exists($file)) {
					include_once $file;
					self::$included[] = $class;
				}
			} catch (Exception $e) {
				
			}
		}	
		
		return true;
	}
}