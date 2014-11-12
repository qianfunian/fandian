<?php

abstract class Msd_Dao_Table_Server extends Msd_Dao_Table_Base
{
	public function __construct()
	{
		$this->leftKeyString = '[';
		$this->rightKeyString = ']';
		$this->prefix = '';
		$this->dbKey = 'server';
		$this->_primaryIsGuid = true;
		
		if (!$this->_orderKey) {
			$this->_orderKey = $this->_primary;
		}
		
		parent::__construct();
	}
	
	public function insert(array $data)
	{
		$result = false;
		
		if ($this->_primaryIsGuid) {
			if (!isset($data[$this->primary()])) {
				$guid = $this->genGuid();
				$data[$this->primary()] = $this->expr("CAST('".$guid."' AS UNIQUEIDENTIFIER)");
			} else {
				$data[$this->primary()] = $this->expr("CAST('".$data[$this->primary()]."' AS UNIQUEIDENTIFIER)");
				$guid = $data[$this->primary()];
			}

			$result = parent::insert($data);
			$result && $result = $guid;
		} else {
			$result = parent::insert($data);
		}
		
		return $result;
	}
}