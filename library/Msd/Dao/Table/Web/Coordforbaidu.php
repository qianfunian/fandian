<?php

class Msd_Dao_Table_Web_Coordforbaidu extends Msd_Dao_Table_Web
{
	protected static $instance = null;
	
	public function __construct()
	{
		parent::__construct();
		
		$this->_name = $this->prefix.'CoordForBaidu';
		$this->_primary = 'CoordGuid';
		$this->_primaryIsGuid = true;
	}
	
	public static function &getInstance()
	{
		if (self::$instance==null) {
			self::$instance = new self();
		}
		
		return self::$instance;
	}
/*
	public function insert(array $params)
	{
		$params['CoordValue'] = $this->expr("geography::STGeomFromText('POINT(".$params['Latitude']." ".$params['Longitude'].")')");
		
		return parent::insert($params);
	}
	
	public function doUpdate(array $params, $keyVal)
	{
		$params['CoordValue'] = $this->expr("geography::STGeomFromText('POINT(".$params['Latitude']." ".$params['Longitude'].")')");
		
		return parent::doUpdate($params, $keyVal);
	}*/
}