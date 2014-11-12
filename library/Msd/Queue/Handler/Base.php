<?php

abstract class Msd_Queue_Handler_Base
{
	protected $params = array();
	
	public function __construct()
	{
	}
	
	public function setParams(array $params)
	{
		foreach ($params as $key=>$val) {
			$this->params[$key] = $val;
		}
	}
	
	abstract public function get($queueName);
	abstract public function put($queueName, $queueData);
	abstract public function status($queueName);
	abstract public function clear($queueName);
	
	public function popup($queueName)
	{
		return $this->get($queueName);
	}
}