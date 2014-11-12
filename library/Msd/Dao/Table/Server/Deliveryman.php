<?php

class Msd_Dao_Table_Server_Deliveryman extends Msd_Dao_Table_Server
{
	protected static $instance = null;
	
	public function __construct()
	{
		parent::__construct();
		
		$this->_name = $this->prefix.'Deliveryman';
		$this->_primary = 'DlvManGuid';
	}
	
	public static function &getInstance()
	{
		if (self::$instance==null) {
			self::$instance = new self();
		}

		return self::$instance;
	}
	
	public function &getDlvManId($DlvManId)
	{
		$dcDao = &$this->t('distribution/center');
		
		$select = &$this->s();
		$select->from($this->sn('dm'));
		$select->join($dcDao->sn('dc'), 'dc.DCGuid=dm.DCGuid', array(
			'dc.CityGuid'	
			));
		$select->where('dm.DlvManId=?', $DlvManId);
		$select->limit(1);
		
		return $this->one($select);
	}
}