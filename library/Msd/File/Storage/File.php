<?php

/**
 * 基于常规文件系统的文件存储
 * 
 * @author pang
 *
 */
class Msd_File_Storage_File extends Msd_File_Storage_Base
{
	public function __construct()
	{
		parent::__construct();
	}
	
	public function dirFiles($dir)
	{
		$files = array();
		
		return $files;
	}
		
	public function initDir($dir)
	{
		$this->connect();
		
		if (!is_dir($dir)) {
			$this->mkdir($dir);
		}
	}
	
	public function exists($file)
	{
		return (bool)file_exists($file);
	}
	
	public function save($from, $to, $func='copy')
	{
		$this->connect();
		
		$func($from, $to);
	}
	
	public function rename($from, $to)
	{
		$this->connect();
		
		rename($from, $to);
	}
	
	public function del($file)
	{
		$this->connect();
		
		unlink($file);
	}
	
	public function connect()
	{
		
	}
	
	public function close()
	{
		
	}
	
	public function mkdir($dir, $mod=0777)
	{
		$this->connect();
		
		mkdir($dir, 0777, true);
	}
}