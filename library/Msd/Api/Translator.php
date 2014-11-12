<?php

class Msd_Api_Translator
{
	protected static $instance = null;
	
	public static function &getInstance()
	{
		if (self::$instance==null) {
			self::$instance = new self();
		}	
		
		return self::$instance;
	}
	
	public function &t($name)
	{
		$names = array();
		$tmp = explode('_', $name);
		foreach ($tmp as $t) {
			$names[] = ucfirst(strtolower($t));
		}
		
		$className = 'Msd_Api_Translator_'.implode('_', $names);
		
		return call_user_func(array(
			$className,
			'getInstance'	
			));
	}
}