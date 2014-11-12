<?php

class Msd_Dao_Table_Server_Vendor_Servicetime extends Msd_Dao_Table_Server
{
	protected static $instance = null;
	
	public function __construct()
	{
		parent::__construct();
		
		$this->_name = $this->prefix.'VendorServiceTime';
		$this->_primary = 'VSTGuid';
		$this->_orderKey = 'StartTime';
	}
	
	public static function &getInstance()
	{
		if (self::$instance==null) {
			self::$instance = new self();
		}

		return self::$instance;
	}
	
	public function &VendorServiceTime($VendorGuid)
	{
		$rows = array();
		$select = $this->db->select();
		
		$select->from($this->_name);
		$select->where('VendorGuid=?', $VendorGuid);
		$select->where('Disabled=?', 0);
		$select->order('StartTime ASC');

		$result = $this->all($select);
		$i = 0;
		foreach ($result as $row) {
			$row['_seq'] = 1 + ($i++);
			$rows[] = $row;
		}
		
		return $rows;
	}
	
	/**
	 * 从某个时间开始的第一个开始营业时间
	 * 
	 * @param unknown_type $VendorGuid
	 * @param unknown_type $start
	 */
	public function nextServiceTime($VendorGuid, $start)
	{
		$select = &$this->s();
		$select->from($this->sn('vst'));
		$select->where('VendorGuid=?', $VendorGuid);
		$select->where('Disabled=?', 0);
		$select->where('StartTime>?', $start);
		$select->order('StartTime ASC');
		$select->limit(1);
		
		return $this->one($select);
	}
}