<?php

/**
 * 日志记录
 * 
 * @author pang
 *
 */

class Msd_Log
{
	protected static $instance = null;
	protected $logSavePath = '';
	
	private function __construct()
	{
		$this->logSavePath = APPLICATION_PATH . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR . APPLICATION_ENV;
		if (!is_dir($this->logSavePath)) {
			mkdir($this->logSavePath , 0777);
		}
	}

	public function __call($method, $params)
	{
		if (preg_match('/^([a-zA-Z0-9]+)$/is', $method)) {
			$this->log(isset($params[0]) ? $params[0] : '', $method);
		}
	}	
	
	public static function &getInstance()
	{
		if (self::$instance==null) {
			self::$instance = new self();
		}
		
		return self::$instance;
	}
	
	/**
	 * 写入日志到文件系统
	 * 
	 * @param string $message
	 * @param string $file
	 */
	protected function log($message, $file='general')
	{
		if ($message instanceof Exception) {
			$message = $message->getMessage()."\n".$message->getTraceAsString();	
		}
		
		$logFile = realpath($this->logSavePath).DIRECTORY_SEPARATOR.$file.'.log';

		$ip = Msd_Request::clientIp();
		//$runTime = intval(Msd_Timer::end('application', true)*1000000);
		$runTime = Msd_Timer::end('application', true);
		$memoryUsage = memory_get_usage();
		$unit = array('B','KB','MB','GB','TB','PB');
		$memoryUsage = @round($memoryUsage/pow(1024,($i=floor(log( abs($memoryUsage==0?1:$memoryUsage) ,1024)))),2).$unit[$i];
				
		$logs = array(
				$ip, 
				'-',
				'-',
				'['.date('d/M/Y:H:i:s O').']',
				'"'.$_SERVER['REQUEST_METHOD'].' '.$_SERVER['REQUEST_URI'].' '.$_SERVER['SERVER_PROTOCOL'].'"',
				'200',
				'0',
				//'"'.($_SERVER['HTTP_REFERER'] ? $_SERVER['HTTP_REFERER'] : '').'"',
				'"'.($_SERVER['HTTP_USER_AGENT'] ? $_SERVER['HTTP_USER_AGENT'] : 'Unknown').'"',
				'['.$runTime.']',
				$memoryUsage,
				Msd_Dao::$wQueries,
				Msd_Dao::$rQueries,
				(Msd_Controller::isGzipped() ? 'gzip' : '-'),
				MSD_FORCE_CITY,
				$message
				);
		
		$log = implode(' ', $logs)."\n";

		error_log($log, 3, $logFile);		
	}
}