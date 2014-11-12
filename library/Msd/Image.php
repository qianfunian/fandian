<?php

/**
 * 图形处理
 * 
 * @author pang
 *
 */

class Msd_Image
{
	
	public static function &getHandler($file, $name='Gd')
	{
		$className = 'Msd_Image_Handler_'.ucfirst(strtolower($name));
		
		class_exists($className) || $className = 'Msd_Image_Handler_Gd';
		
		return new ${className}($file);
	}
}