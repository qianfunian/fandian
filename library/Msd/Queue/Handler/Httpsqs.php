<?php

require_once 'Zend/Http/Client.php';

class Msd_Queue_Handler_Httpsqs extends Msd_Queue_Handler_Base
{
	public function __construct()
	{
		parent::__construct();
		
		$config = &Msd_Config::cityConfig()->queue->handler;
		
		$this->params['host'] = $config->host ? $config->host : '127.0.0.1';
		$this->params['auth'] = $config->auth ? $config->auth : '';
		$this->params['port'] = $config->port ? $config->port : '1218';
		$this->params['charset'] = $config->charset ? $config->charset : 'utf-8';
	}
	
	public function get($queueName)
	{
		$url = 'http://'.$this->params['host'].':'.$this->params['port'].'/';
		$url .= '?auth='.$this->params['auth'].'&charset='.$this->params['charset'].'&name='.$queueName;
		$url .= '&opt=get';
		
		$client = new Zend_Http_Client($url);
		$client->request();
		
		$html = $client->getLastResponse()->getBody();
		if ($html=='HTTPSQS_ERROR' || $html=='HTTPSQS_GET_END') {
			$html = false;
		}
		
		return $html;
	}
	
	public function put($queueName, $queue_data)
	{
		$result = false;
		
		$url = 'http://'.$this->params['host'].':'.$this->params['port'].'/';
		$url .= '?auth='.$this->params['auth'].'&charset='.$this->params['charset'].'&name='.$queueName;
		$url .= '&opt=put';		
		
		$client = new Zend_Http_Client($url);
		$client->setMethod(Zend_Http_Client::POST);
		$client->setRawData($queue_data);
		$client->request();
		
		$html = $client->getLastResponse()->getBody();
		
		if ($html=='HTTPSQS_PUT_OK') {
			$result = true;
		} else if ($html=='HTTPSQS_PUT_END') {
			$result = $html;
		}
		
		return $result;
	}
	
	public function status($queueName)
	{
		$url = 'http://'.$this->params['host'].':'.$this->params['port'].'/';
		$url .= '?auth='.$this->params['auth'].'&charset='.$this->params['charset'].'&name='.$queueName;
		$url .= '&opt=status';
		
		$client = new Zend_Http_Client($url);
		$client->request();
		
		$html = $client->getLastResponse()->getBody();
		if ($html=='HTTPSQS_ERROR') {
			$html = false;
		}
		
		return $html;		
	}
	
	public function view($queueName, $queue_pos)
	{
		$url = 'http://'.$this->params['host'].':'.$this->params['port'].'/';
		$url .= '?auth='.$this->params['auth'].'&charset='.$this->params['charset'].'&name='.$queueName;
		$url .= '&opt=view&pos='.$queue_pos;
		
		$client = new Zend_Http_Client($url);
		$client->request();
		
		$html = $client->getLastResponse()->getBody();
		if ($html=='HTTPSQS_ERROR') {
			$html = false;
		}
		
		return $html;		
	}
	
	public function reset($queueName)
	{
		$result = false;
		
		$url = 'http://'.$this->params['host'].':'.$this->params['port'].'/';
		$url .= '?auth='.$this->params['auth'].'&charset='.$this->params['charset'].'&name='.$queueName;
		$url .= '&opt=reset';
		
		$client = new Zend_Http_Client($url);
		$client->request();
		
		$html = $client->getLastResponse()->getBody();
		if ($html=='HTTPSQS_RESET_OK') {
			$result = true;
		}
		
		return $result;		
	}
	
	public function status_json($queueName)
	{
		$url = 'http://'.$this->params['host'].':'.$this->params['port'].'/';
		$url .= '?auth='.$this->params['auth'].'&charset='.$this->params['charset'].'&name='.$queueName;
		$url .= '&opt=status_json';
	
		$client = new Zend_Http_Client($url);
		$client->request();
	
		$html = $client->getLastResponse()->getBody();
		if ($html=='HTTPSQS_ERROR') {
			$html = false;
		}
	
		return $html;
	}	
	
	public function syncTime($num)
	{
		$result = false;
		
		$url = 'http://'.$this->params['host'].':'.$this->params['port'].'/';
		$url .= '?auth='.$this->params['auth'].'&charset='.$this->params['charset'].'&name=httpsqs_synctime';
		$url .= '&opt=synctime&num='.$num;
	
		$client = new Zend_Http_Client($url);
		$client->request();
	
		$html = $client->getLastResponse()->getBody();
		if ($html=='HTTPSQS_SYNCTIME_OK') {
			$result = true;
		}
	
		return $result;
	}	
	
	public function clear($queueName)
	{
		return $this->reset($queueName);
	}
}