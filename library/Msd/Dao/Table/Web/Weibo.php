<?php

class Msd_Dao_Table_Web_Weibo extends Msd_Dao_Table_Web
{
	protected static $instance = null;
	
	public function __construct()
	{
		parent::__construct();
		
		$this->_name = $this->prefix.'WeiboOauthToken';
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
	
	public function getCustGuidByUid($uid)
	{
		$select = &$this->s();
		$select->from($this->sn('wot'));
		$select->where('WeiboUid=?', $uid);
		
		$row = $this->one($select);
		
		return $row['CustGuid'] ? $row['CustGuid'] : '';
	}
}