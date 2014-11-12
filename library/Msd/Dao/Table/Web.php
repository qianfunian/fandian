<?php

abstract class Msd_Dao_Table_Web extends Msd_Dao_Table_Base
{
	public function __construct()
	{
		$this->leftKeyString = '[';
		$this->rightKeyString = ']';
		$this->prefix = 'W_';
		$this->dbKey = 'server';
		
		parent::__construct();
	}
}