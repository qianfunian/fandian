<?php

abstract class Msd_Queue_Base
{
	protected static $handler = null;
	protected $queue_name = '';
	
	abstract public static function getInstance();
	
	protected function __construct()
	{
		$className = 'Msd_Queue_Handler_'.ucfirst(strtolower(Msd_Config::cityConfig()->queue->handler->name));
		
		$this->handler = & new $className();
	}
	
	public function setHandler(&$handler)
	{
		$this->handler = &$handler;
	}
	
	public function put($data)
	{
		return $this->handler->put($this->queue_name, $data);
	}
	
	public function get()
	{
		return $this->handler->get($this->queue_name);
	}
	
	public function clear()
	{
		return $this->handler->clear($this->queue_name);
	}
}