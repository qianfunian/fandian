<?php

class Msd_Queue_Email extends Msd_Queue_Base
{
	protected static $instance = null;
	
	protected function __construct()
	{
		$this->queue_name = APPLICATION_ENV . '_email';
		parent::__construct();
	}
	
	public static function &getInstance()
	{
		if (self::$instance==null) {
			self::$instance = new self();
		}
		
		return self::$instance;
	}
}