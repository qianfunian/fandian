<?php

class Msd_File_Storage
{
	public static function &factory($protocol='file')
	{
		$class = 'Msd_File_Storage_'.ucfirst(strtolower($protocol));
		$object = new $class();
		
		return $object;		
	}
}