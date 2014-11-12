<?php

/**
 * 12580合作伙伴
 * 
 * @author pang
 *
 */
class Partner_V12580Controller extends Msd_Controller_Partner
{
	protected $cityId = '0510';
	protected $cityConfig = array();
	
	public function init()
	{
		parent::init();

		$config = &Msd_Config::appConfig();

		$city = trim(urldecode($this->getRequest()->getParam('city')));
		$cs = $config->cities->toArray();
		$_c = 0;
		foreach ($cs as $_city=>$data) {
			if ($city==$data['zone']) {
				$_c = $data['zone'];
				$this->cityConfig = $data;
				break;
			}
		}
		
		if ($_c<=0) {
			$this->error('error.partner.v12580.city_required');
		} else {
			$this->cityId = $_c;
		}

		$params = $this->getRequest()->getParams();
		Msd_Log::getInstance()->v12580(var_export($params, true));
	}
	
	public function __call($method, $params)
	{
		$this->error('error.partner.v12580.method_not_supported');
		exit;
	}
	
	public function vendorsAction()
	{
		$this->checkCityOpen('vendors');
		$this->_baseAction();
	}
	
	public function itemsAction()
	{
		$this->checkCityOpen('items');
		$this->_baseAction();
	}
	
	public function orderAction()
	{
		$this->validateSign($this->getRequest()->getParams());
		$this->_baseAction();
	}
	
	public function refundAction()
	{
		$this->validateSign($this->getRequest()->getParams());
		
		$this->_baseAction();
	}
	
	private function _baseAction()
	{
		$params = $this->getRequest()->getParams();

		Msd_Log::getInstance()->v12580(var_export($params, true));

		$params['key'] = $this->cityConfig['api_key'];
		
		$uri_params = $this->getApiUri();
		$http_params = $uri_params['http_params'];
		$uri = $uri_params['uri'];

		$client = new Msd_Http_Client($uri, array());
		$client->setParameterPost($params);
		$client->setHeaders($http_params);

		$response = $client->request('POST');

		$body = $response->getBody();
		Msd_Log::getInstance()->v12580output($body);
		
		$this->output($body);
	}
	
	protected function getApiUri()
	{
		$uri = '';
		$params = $this->getRequest()->getParams();
		$method = trim(strtolower($params['action']));
		$http_params = array(
			'Accept-Encoding' => 'gzip,deflate'	
			);
		
		if (isset($this->cityConfig['api_ip']) && $this->cityConfig['api_ip']) {
			$uri = 'http://'.$this->cityConfig['api_ip'].'/api/v12580/'.$method.'.'.$this->format;
			$http_params['Host'] = $this->cityConfig['api_host'];
		} else {
			$uri = 'http://'.$this->cityConfig['api_host'].'/api/v12580/'.$method.'.'.$this->format;
		}

		return array(
			'uri' => $uri,
			'http_params' => $http_params	
			);
	}
	
	protected function checkCityOpen($xmlRoot)
	{
		if (!$this->cityConfig['enabled']) {
			$this->xmlRoot = $xmlRoot;
			$this->output();
		}
	}
	
	/**
	 * 验证加密字符串
	 * 
	 * @param unknown_type $signedString
	 */
	protected function validateSign(array $params)
	{
		$result = false;
		$signParams = $params;
		$sign = '';
		
		if (isset($signParams['signature'])) {
			$sign = $signParams['signature'];
			unset($signParams['signature']);
		}
		unset($signParams['module']);
		unset($signParams['controller']);
		unset($signParams['action']);
		unset($signParams[3]);
		unset($signParams[4]);
		unset($signParams['format']);
		unset($signParams['key']);
		
		Msd_Log::getInstance()->v12580("SignParameters: ".var_export($signParams, true));
		
		$signBaseString = Msd_Partner_V12580::BuildSignBaseString($signParams);
		Msd_Log::getInstance()->v12580("SignBaseString: ".$signBaseString);

		$tsign = Msd_Partner_V12580::SignString($signBaseString);
		Msd_Log::getInstance()->v12580('Sign:'."\n".$sign."\nTSign:\n".$tsign);

		if ($sign!=$tsign) {
			$this->error('error.v12580.sign_invalid');
		}
	}
}