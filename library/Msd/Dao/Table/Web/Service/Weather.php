<?php

class Msd_Dao_Table_Web_Service_Weather extends Msd_Dao_Table_Web_Service_Base
{
	protected static $instance = null;
	
	public function __construct()
	{
		parent::__construct();
		
		$this->_name = $this->prefix.'ServiceWeather';
		$this->_primary = 'AutoId';
	}
	
	public static function &getInstance()
	{
		if (self::$instance==null) {
			self::$instance = new self();
		}
	
		return self::$instance;
	}
	
	public function &getDate($city, $timestamp=null)
	{
		$timestamp==null && $timestamp = time();
		
		$select = &$this->s();
		$select->from($this->name());
		$select->where('Code=?', $city);
		$select->where('String LIKE ?', date('n月j日', $timestamp).'%');
		$select->order('LastUpdate DESC');
		$select->order('AutoId DESC');
		$select->limit(1);

		$row = $this->one($select);
		return $row ? $row : array();
	}
	
	public function &getToday($city)
	{
		return $this->getDate($city, time());
	}
	
	public function &getTommorrow($city)
	{
		return $this->getDate($city, time()+86400);
	}
}