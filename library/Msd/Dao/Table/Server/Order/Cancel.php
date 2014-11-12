<?php

class Msd_Dao_Table_Server_Order_Cancel extends Msd_Dao_Table_Server_Order_Base
{
	protected static $instance = null;
	
	public function __construct()
	{
		parent::__construct();
		
		$this->_name = $this->prefix.'OrderCancel';
		$this->_primary = 'OrderGuid';
	}
	
	public static function &getInstance()
	{
		if (self::$instance==null) {
			self::$instance = new self();
		}
		
		return self::$instance;
	}
	
	public function getCancelRemark($OrderGuid)
	{
		$crTable = &$this->t('cancelreasons');
		
		$select = &$this->s();
		$select->from($this->sn('oc'));
		$select->join($crTable->sn('cr'), 'cr.CancelGuid=oc.CancelGuid', array(
			'cr.Reason'	
			));
		$select->where('oc.OrderGuid=?', $OrderGuid);
		$select->limit(1);
		
		return $this->one($select);
	}
}