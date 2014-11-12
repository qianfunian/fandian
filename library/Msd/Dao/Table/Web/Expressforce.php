<?php

class Msd_Dao_Table_Web_Expressforce extends Msd_Dao_Table_Web
{
	protected static $instance = null;
	
	public function __construct()
	{
		parent::__construct();
		
		$this->_name = $this->prefix.'ExpressForce';
		$this->_primary = 'AutoId';
		$this->_primaryIsGuid = false;
	}
	
	public static function &getInstance()
	{
		if (self::$instance==null) {
			self::$instance = new self();
		}

		return self::$instance;
	}
	
	public function last()
	{
		$select = &$this->s();
		$select->from($this->sn('ef'));
		$select->order('ef.AutoId DESC');
		$select->limit(1);
		
		$row = $this->one($select);
		
		$row || $row = array(
				'Force' => 0,
				'AddTime' => date('Y-m-d H:i:s')
				);
		
		return $row;
	}

	public function insert(array $params)
	{
		$params['AddTime'] = $this->expr('GETDATE()');
		
		return parent::insert($params);
	}
}