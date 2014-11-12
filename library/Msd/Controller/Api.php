<?php

/**
 * Api控制器基类
 * 
 * @author pang <pang@fandian.com>
 *
 */

class Msd_Controller_Api extends Msd_Controller
{
	protected $needKeyAuth = true;
	protected $sess = null;
	protected $member = null;
	protected $uid = '';
	protected $format = 'xml';
	protected $apiKey = '';
	protected $apiData = array();
	protected $xmlRoot = 'hash';
	protected $output = array();
	protected $opCharset = 'utf-8';
	
	protected static $table = null;
	protected static $translator = null;
	protected static $version = '2.0.7';
	
	protected $outputWithoutCdata = false;
	
	const CITY_ID = '0510';
	
	public function __call($method, $params)
	{
		$this->error('error.request.url_not_exists');		
	}
	
	public function init()
	{
		parent::init();

		$this->cityId = trim($this->getRequest()->getParam('city_id', self::CITY_ID));

		self::$table || self::$table = &Msd_Dao::table('api/keys');
		self::$translator = &Msd_Api_Translator::getInstance();
		
		$params = $this->getRequest()->getParams();
		switch(strtolower($params['format'])) {
			case 'json':
				$this->format = 'json';
				break;
			default:
				$this->format = 'xml';
				break;
		}
		
		$this->apiKey = trim($params['key']);
		$this->authApiKey();
	}
	
	protected function clientInfo()
	{
		$data = array(
			'os' => 'default'	
			);
		$browser = &Msd_Browser::getInstance();
		
		if ($browser->isIOS()) {
			$data['os'] = 'ios';
		} else if ($browser->isAndroid()) {
			$data['os'] = 'android';
		}

		return $data;
	}
	
	protected function auth($exit=false)
	{
		$pass = false;

		if ($exit && (!$_SERVER['PHP_AUTH_USER'] || !$_SERVER['PHP_AUTH_PW'])) {
			header('WWW-Authenticate: Basic realm="Api Auth"');
	   		header('HTTP/1.0 401 Unauthorized'); 
	   		exit;
		} else if ($_SERVER['PHP_AUTH_USER'] && $_SERVER['PHP_AUTH_USER']) {
			$pass = $this->authMember($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']);
			if ($exit && !$pass) {
				$this->error('error.member.auth_failed', 401);
			}	
		}
		
		return $pass;
	}
	
	protected function authMember($UserName, $PassWord)
	{
		$passed = false;

		$user = &Msd_Member::createInstance($UserName, 'username');
		$DbPassWord = trim($user->Password);
		if ($user->uid() && $PassWord==$DbPassWord) {
			$passed = true;
			$this->uid = $user->uid();
		}
	
		if (!$passed) {
			$user = &Msd_Member::createInstance($UserName, 'cell');
			$DbPassWord = trim($user->Password);
			if ($user->uid() && $PassWord==$DbPassWord) {
				$passed = true;
				$this->uid = $user->uid();
			}
		}
			
		if (!$passed) {
			$user = &Msd_Member::createInstance($UserName, 'email');
			$DbPassWord = trim($user->Password);
			if ($user->uid() && $PassWord==$DbPassWord) {
				$passed = true;
				$this->uid = $user->uid();
			}
		}
		
		if ($this->uid) {
			$this->member = &Msd_Member::getInstance($this->uid);
		}
		
		return $passed;
	}
	
	/**
	 * 校验api key
	 * 
	 */
	protected function authApiKey()
	{
		if ($this->needKeyAuth) {
			if (strlen($this->apiKey)==0) {
				$this->error('error.request.api_key_needed');	
			}
			
			$data = self::$table->getByKey($this->apiKey);

			if ($data['Id']) {
				$this->apiData = $data;
			} else {
				$this->error('error.request.invalid_api_key');
			}
			
			if (!self::_authApiKeyIp($this->apiKey)) {
				$this->error('error.request.ip_proceeded_limit');
			}
		}
	}
	
	/**
	 * 检测请求是否是POST，并输出错误
	 * 
	 */
	protected function needPost()
	{
		if ($_SERVER['REQUEST_METHOD']!='POST' && !Msd_Config::appConfig()->api->passby_post_check) {
			$this->error('error.request.post_method_only');
		}
	}
	
	/**
	 * 输出一个普通消息
	 * 
	 * @param string $msg
	 */
	protected function message($msg)
	{
		$this->output = array(
			'message' => $msg	
			);
		$this->output();
	}
	
	/**
	 * 输出错误信息
	 * 
	 * @param string $msg
	 * @param interger $code
	 * @param misc $e
	 */
	protected function error($error_code='', $code=200, $e=null)
	{
		$this->output = array();
		$this->xmlRoot = 'error';
		$msg = self::$translator->t('error')->translate(array(
				'code' => $error_code	
				));
		
		$this->output[$this->xmlRoot] = array(
			'code' => $error_code,
			'url' => $_SERVER['REQUEST_URI'],
			'message' => $msg,
			);

		Msd_Log::getInstance()->apierror($error_code.':'.$msg);
		
		$this->output();
	}
	
	/**
	 * 输出结果
	 * 
	 */
	protected function output($content=null)
	{
		if ($content===null) {
			$op = &Msd_Api_Output::getInstance();
			$op->setCdata(!$this->outputWithoutCdata);
			$op->setParam('xml_root', $this->xmlRoot);
			$op->setParam('data', $this->output);
		
			$output = $op->output($this->format, $this->opPrefix);
		} else {
			$output = &$content;
		}

		$params = $this->getRequest()->getParams();
		$params['PHP_AUTH_USER'] = $_SERVER['PHP_AUTH_USER'];
		$params['PHP_AUTH_PW'] = $_SERVER['PHP_AUTH_PW'];
		$params['HEADERS'] = array();
		foreach ($_SERVER as $k=>$v) {
			$nk = substr($k, 0, 5);
			if ($nk=='HTTP_') {
				$params['HEADERS'][$k] = $v;
			}
		}
		Msd_Log::getInstance()->apioutput($output."\n===========\n".var_export($params, true));
		if ($this->acceptGz()) {
			self::gzipped();
			header('Content-Encoding: gzip');
			$output = gzencode($output, 2, FORCE_GZIP);
		}
		
		switch($this->format) {
			case 'json':
				header('Content-Type: application/json');
				break;
			default:
				header('Content-Type: text/xml; charset='.$this->opCharset);
				break;
		}
		
		header('HTTP/1.1 200 OK');
		header('Content-Length: '.strlen($output));
		header('MSD: '.FANDIAN_APP_VER);
		header('APIVER: '.self::$version);
		header('Cache-Control: no-cache');
		header('Pragma: no-cache');
		header('Expires: Fri, 01 Jan 1970 05:00:00 GMT');
				
		echo $output;
		exit(0);
	}
	
	protected function &t($t)
	{
		return Msd_Api_Translator::getInstance()->t($t);
	}
	
	protected function acceptGz()
	{
		$acceptGz = (bool)preg_match('/gzip/i', $_SERVER['HTTP_ACCEPT_ENCODING']);
		if (!$acceptGz) {
			$headers = function_exists('getallheaders') ? getallheaders() : array();
			$msdEncoding = trim($headers['MSD-Encoding']);
			if ($msdEncoding) {
				$accepted = explode(',', $msdEncoding);
				foreach ($accepted as $accept) {
					if (trim($accept)=='gzip') {
						$acceptGz = true;
						$flag = true;
						break;
					}
				}
			}
		}
		
		return $acceptGz;
	}
	
	private static function _authApiKeyIp($apiKey)
	{
		$result = false;
		$ip = Msd_Request::clientIp();
		$result = true;
		
		return $result;
	}
	
}
