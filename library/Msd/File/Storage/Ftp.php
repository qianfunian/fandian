<?php

/**
 * 基于FTP的文件存储
 * 
 * @author pang
 * 
 */

set_time_limit(300);

class Msd_File_Storage_Ftp extends Msd_File_Storage_Base
{
	protected $conn = null;
	
	public function __construct()
	{
		parent::__construct();
		
		$config = &Msd_Config::cityConfig();
		$this->config['host'] = $config->attachment->save->ftp->host;
		$this->config['user'] = $config->attachment->save->ftp->user;
		$this->config['pwd'] = $config->attachment->save->ftp->pwd;
		$this->config['port'] = $config->attachment->save->ftp->port;
		$this->config['pasv'] = (bool)$config->attachment->save->ftp->pasv;
	}
	
	public function __destruct()
	{
		$this->close();
	}
	
	public function dirFiles($dir)
	{
		$files = array();

		$this->connect();
		ftp_pasv($this->conn, (bool)$this->config['pasv']);
		$files = ftp_nlist($this->conn, $dir);
		
		return $files;
	}
		
	public function initDir($dir)
	{
		$this->connect();
		
		if (!@ftp_chdir($this->conn, $dir)) {
			$this->mkdir($dir);
		}
	}
	
	public function exists($file)
	{
		return (bool)ftp_size($this->conn, $file);
	}
	
	public function save($from, $to, $func='copy')
	{
		$this->connect();
		try {
			$nFrom = '';
			if ($func=='move_uploaded_file') {
				$cConfig = &Msd_Config::cityConfig();
				$nFrom = $cConfig->system->tmp_dir.DIRECTORY_SEPARATOR.mt_rand(100000, 999999);
				move_uploaded_file($from, $nFrom);
				$from = $nFrom;
			}
			
			ftp_pasv($this->conn, $this->config['pasv']);
			$result = ftp_put($this->conn, $to, $from, FTP_BINARY, 0);
			if (!$result) {
				Msd_Log::getInstance()->ftp('Upload failed, Local: '.$from.', Remote: '.$to);
			} else {
				ftp_chmod($this->conn, 0666, $to);
				$nFrom && @unlink($nFrom);
			}
		} catch (Exception $e) {
			Msd_Log::getInstance()->ftp($e->getMessage()."\n".$e->getTraceAsString());
		}
	}
	
	public function rename($from, $to)
	{
		$this->connect();
		
		try {
			ftp_rename($this->conn, $from, $to);
		} catch (Exception $e) {
			Msd_Log::getInstance()->ftp($e->getMessage()."\n".$e->getTraceAsString());
		}
	}
	
	public function del($file)
	{
		$this->connect();
		
		try {
			ftp_delete($this->conn, $file);
		} catch (Exception $e) {
			Msd_Log::getInstance()->ftp($e->getMessage()."\n".$e->getTraceAsString());
		}
	}
	
	public function connect()
	{
		if (!is_resource($this->conn) || !$this->conn) {
			$this->conn = ftp_connect($this->config['host'], $this->config['port']);
			if (is_resource($this->conn)) {
				ftp_login($this->conn, $this->config['user'], $this->config['pwd']);
				try {
					ftp_pasv($this->conn, (bool)$this->config['pasv']);
				} catch (Exception $e) {
					
				}
			}
		}
	}
	
	public function close()
	{
		if (is_resource($this->conn)) {
			ftp_close($this->conn);
		}
	}
	
	public function mkdir($dir, $mod=0777)
	{
		$this->connect();
		try {
			ftp_mkdir($this->conn, $dir);
		} catch (Exception $e) {
			Msd_Log::getInstance()->ftp($e->getMessage()."\n".$e->getTraceAsString());
		}
	}
}