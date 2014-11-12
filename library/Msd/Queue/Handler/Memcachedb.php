<?php

class Msd_Queue_Handler_Memcachedb extends Msd_Queue_Handler_Base
{
	protected $memcachedb = null;
	
	public function __construct()
	{
		parent::__construct();
		
		$this->memcachedb = &Msd_Cache_Remote::getInstance();
	}
	
	public function get($queueName)
	{	
		return $this->memcachedb->get('queue_'.$queueName);
	}
	
	public function put($queueName, $queue_data)
	{
		$result = false;
		
		$arr = $this->memcachedb->get('queue_'.$queueName);
		$arr || $arr = array();
		$arr[] = $queue_data;

		$this->memcachedb->set('queue_'.$queueName, $arr);
		$result = true;
		
		return $result;
	}
	
	public function status($queueName)
	{
		return 'ok';		
	}
	
	public function view($queueName, $queue_pos)
	{
		return 'ok';		
	}
	
	public function reset($queueName)
	{
		$this->memcachedb->set('queue_'.$queueName);
		
		return true;		
	}
	
	public function clear($queueName)
	{
		return $this->reset($queueName);
	}
}