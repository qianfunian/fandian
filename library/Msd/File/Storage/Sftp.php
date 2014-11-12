<?php

/**
 * 基于Sftp的文件存储
 * 
 */

set_time_limit(300);

class Msd_File_Storage_Sftp extends Msd_File_Storage_Base
{
	protected $conn = null;
	protected $sftp = null;

	public function __construct()
	{
		parent::__construct();

		$config = &Msd_Config::cityConfig();
		$this->config['host'] = $config->attachment->save->sftp->host;
		$this->config['user'] = $config->attachment->save->sftp->user;
		$this->config['pwd'] = $config->attachment->save->sftp->pwd;
		$this->config['port'] = $config->attachment->save->sftp->port;
	}

	public function __destruct()
	{
		$this->close();
	}
	
	public function dirFiles($dir)
	{
		$files = array();
	
		return $files;
	}
	
	public function initDir($dir)
	{
		$this->connect();
	
		try {
			ssh2_sftp_mkdir($this->sftp, $dir, 0777, true);
		} catch (Exception $e) {
			Msd_Log::getInstance()->ssh2($e->getMessage()."\n".$e->getTraceAsString());
		}
	}
	
	public function exists($file)
	{
		return (bool)ssh2_sftp_stat($this->sftp, $file);
	}
	
	public function save($from, $to, $func='copy',$myfilename="1.jpg")
	{
		$this->connect();
		try {
			$nFrom = '';
			if ($func=='move_uploaded_file') {
				$nFrom = "/var/tmp/".$myfilename;
				move_uploaded_file($from, $nFrom);
				$from = $nFrom;
			}
				
			$result = ssh2_scp_send($this->conn, $from, $to, 0777);
			if (!$result) {
				Msd_Log::getInstance()->ssh2('Upload failed, Local: '.$from.', Remote: '.$to);
				return 0;
			} else {
				$nFrom && @unlink($nFrom);
				return 1;
			}
		} catch (Exception $e) {
			Msd_Log::getInstance()->ssh2($e->getMessage()."\n".$e->getTraceAsString());
			return 0;
		}
	}
	
	public function rename($from, $to)
	{
		$this->connect();
	
		try {
			ssh2_sftp_rename($this->sftp, $from, $to);
		} catch (Exception $e) {
			Msd_Log::getInstance()->ssh2($e->getMessage()."\n".$e->getTraceAsString());
		}
	}
	
	public function del($file)
	{
		$this->connect();
	
		try {
			ssh2_sftp_unlink($this->sftp, $file); 
		} catch (Exception $e) {
			Msd_Log::getInstance()->ssh2($e->getMessage()."\n".$e->getTraceAsString());
		}
	}
	
	public function connect()
	{
		if (!is_resource($this->conn) || !$this->conn) {
			$this->conn = ssh2_connect($this->config['host'], $this->config['port']);
			if (is_resource($this->conn)) {
				$flag = ssh2_auth_password($this->conn, $this->config['user'], $this->config['pwd']);
				if (!$flag) {
					Msd_Log::getInstance()->ssh2("SSH Login failed: ".var_export($this->config, true));
				} else {
					$this->sftp = ssh2_sftp($this->conn);
				}
			}
		}
	}
	
	public function close()
	{
		if (is_resource($this->conn)) {
			ssh2_exec($this->conn, 'exit');
		}
	}
	
	public function mkdir($dir, $mod=0777)
	{
		$this->connect();
		try {
			ssh2_sftp_mkdir($this->sftp, $dir, $mod, true);
		} catch (Exception $e) {
			Msd_Log::getInstance()->ssh2($e->getMessage()."\n".$e->getTraceAsString());
		}
	}	
}
