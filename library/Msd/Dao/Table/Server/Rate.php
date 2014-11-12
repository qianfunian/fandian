<?php

class Msd_Dao_Table_Server_Freight extends Msd_Dao_Table_Server
{
	protected static $instance = null;
	
	public function __construct()
	{
		parent::__construct();
		
		$this->_name = $this->prefix.'Freight';
		$this->_primary = 'FreightGuid';
	}
	
	public static function &getInstance()
	{
		if (self::$instance==null) {
			self::$instance = new self();
		}
		
		return self::$instance;
	}
	
	public function calculate($distance, $FrtGrpGuid='')
	{
		$freight = 0;
		$FrtGrpGuid || $FrtGrpGuid = Msd_Config::cityConfig()->db->freight_group->default;
		
		$select = &$this->s();
		$select->from($this->sn('r'), array(
			'r.Freight'	
			));
		$FrtGrpGuid && $select->where('r.FrtGrpGuid=?', $FrtGrpGuid);
		$select->where('r.Distance>=?', (float)$distance);
		$select->order('r.Distance ASC');
		$select->limit(1);

		$row = $this->one($select);
		$freight = (int)$row['Freight'];

		return $freight;
	}
}