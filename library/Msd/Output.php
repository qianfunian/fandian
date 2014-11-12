<?php

/**
 * 内容输出
 * 
 * @author pang
 *
 */
class Msd_Output
{
	/**
	 * Gzip压缩页面内容
	 * 
	 * @param unknown_type $buffer
	 * @param unknown_type $flag
	 */
	public static function gzipContent($buffer, $flag=false)
	{
		if (preg_match('/gzip/i', $_SERVER['HTTP_ACCEPT_ENCODING'])) {
			Msd_Controller::gzipped();
			$buffer = gzencode($buffer, 2, FORCE_GZIP);
			header('Content-Encoding: gzip');
		}
		header('Content-Length: '.strlen($buffer));
		
		return $buffer;		
	}
	
	/**
	 * 准备Json类型的输出
	 * 
	 */
	public static function prepareJson()
	{
		$output = ob_get_contents();
		ob_end_clean();
		if ($output) {
			Msd_Log::getInstance()->output($output);
		}
		
		ob_start(array(
			__CLASS__, 'gzipContent'
			));
		
		header($_SERVER['SERVER_PROTOCOL'].' 200 OK');
		header('Status: 200');
		header('Cache-Control: no-store, no-cache, must-revalidate');
		header('Cache-Control: pre-check=0, post-check=0, max-age=0', false);
		header('Pragma: no-cache');
		header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
		header('Content-Type: application/json; charset=utf-8');
		header('Connection: Keep-Alive');		
	}
	
	/**
	 * 准备Html类型的输出
	 * 
	 * @param unknown_type $code
	 * @param unknown_type $charset
	 */
	public static function prepareHtml($code='200', $charset='utf-8')
	{
		$output = ob_get_contents();
		ob_end_clean();
		if ($output) {
			Msd_Log::getInstance()->output($output);
		}
		
		ob_start(array(
			 __CLASS__, 'gzipContent'
			));
		switch ($code) {
			case '404':
				header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found');
				header('Status: 404 Not Found');
				break;
			default:
				header($_SERVER['SERVER_PROTOCOL'].' 200 OK');
				header('Status: 200');
				break;
		}
		
		header('Cache-Control: no-store, no-cache, must-revalidate');
		header('Cache-Control: pre-check=0, post-check=0, max-age=0', false);
		header('Pragma: no-cache');		
		header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');	
		header('Content-Type: text/html; charset='.$charset);
	}
	
	public static function prepareTxt()
	{
		$output = ob_get_contents();
		ob_end_clean();
		if ($output) {
			Msd_Log::getInstance()->output($output);
		}
		
		ob_start(array(
			 __CLASS__, 'gzipContent'
			));
		switch ($code) {
			case '404':
				header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found');
				header('Status: 404 Not Found');
				break;
			default:
				header($_SERVER['SERVER_PROTOCOL'].' 200 OK');
				header('Status: 200');
				break;
		}
		
		header('Cache-Control: no-store, no-cache, must-revalidate');
		header('Cache-Control: pre-check=0, post-check=0, max-age=0', false);
		header('Pragma: no-cache');		
		header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');	
		header('Content-Type: text/plain; charset='.$charset);
	}
	
	/**
	 * 准备Xml类型的输出
	 * 
	 * @param unknown_type $code
	 * @param unknown_type $charset
	 */
	public static function prepareXml($code='200', $charset='utf-8')
	{
		$output = ob_get_contents();
		ob_end_clean();
		if ($output) {
			Msd_Log::getInstance()->output($output);
		}
		
		ob_start(array(
			 __CLASS__, 'gzipContent'
			));
		switch ($code) {
			case '404':
				header($_SERVER['SERVER_PROTOCOL'].' 404 Not Found');
				header('Status: 404 Not Found');
				break;
			default:
				header($_SERVER['SERVER_PROTOCOL'].' 200 OK');
				header('Status: 200');
				break;
		}
		
		header('Cache-Control: no-store, no-cache, must-revalidate');
		header('Cache-Control: pre-check=0, post-check=0, max-age=0', false);
		header('Pragma: no-cache');		
		header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');	
		header('Content-Type: text/xml; charset='.$charset);
	}
	
	/**
	 * 输出缓冲区内容
	 * 
	 */
	public static function doOutput()
	{
		ob_end_flush();
		exit(0);
	}
}