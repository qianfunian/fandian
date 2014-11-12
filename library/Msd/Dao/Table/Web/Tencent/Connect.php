<?php

class Msd_Dao_Table_Web_Tencent_Connect extends Msd_Dao_Table_Web_Tencent_Base
{
	protected static $instance = null;
	
	public function __construct()
	{
		parent::__construct();
		
		$this->_name = $this->prefix.'TencentConnectToken';
		$this->_primary = 'CustGuid';
		$this->_primaryIsGuid = true;
	}
	
	public static function &getInstance()
	{
		if (self::$instance==null) {
			self::$instance = new self();
		}
	
		return self::$instance;
	}
	
	public function getCustGuidByOpenId($OpenId)
	{
		$select = &$this->s();
		$select->from($this->sn('tc'));
		$select->where('OpenID=?', $OpenId);
		
		$row = $this->one($select);
		
		return $row['CustGuid'] ? $row['CustGuid'] : '';
	}
	
	public function insert(array $params)
	{
		return parent::insert($params);
	}
}