<?php

abstract class Msd_Dao_Base
{
	protected $primaryKey = '';
	protected $dbConnection = null;
	protected $dbServer = '';
	protected $keyPrefix = '';
	
	protected function init()
	{
			
	}

	public function &connect()
	{
		$this->dbConnection = &Msd_Dao::assignDbConnection($this->dbServer);
		
		return $this;
	}
	
	public function insert(array $params)
	{
		$data = array();
		foreach ($params as $key=>$val) {
			$data[$this->keyPrefix.$key] = $val;
		}

		return parent::insert($data);
	}	
}