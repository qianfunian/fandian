<?php

abstract class Msd_Dao_Table_Sms extends Msd_Dao_Table_Base
{
	public function __construct()
	{
		$this->leftKeyString = '[';
		$this->rightKeyString = ']';
		$this->prefix = 'T_';
		$this->dbKey = 'server';
		
		parent::__construct();
	}
}