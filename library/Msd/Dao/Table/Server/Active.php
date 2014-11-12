<?php

class Msd_Dao_Table_Server_Active extends Msd_Dao_Table_Server
{
	protected static $instance = null;
	
	public function __construct()
	{
		parent::__construct();
		
		$this->_name = $this->prefix.'Active';
		$this->_primary = 'ID';
		$this->_orderKey = 'ID';
		$this->_realPrimary = 'ID';
		$this->_primaryIsGuid = false;
	}
	
	public static function &getInstance()
	{
		if (self::$instance==null) {
			self::$instance = new self();
		}
	
		return self::$instance;
	}
	
	public function insert(array $params)
	{
		$params['CreateTime'] = $this->expr('GETDATE()');
	
		return parent::insert($params);
	}
	
	public function &getAll($params='Poll',$active=0)
	{
		$rows = array();
	
		$select = &$this->s();
		$select->from($this->sn('c'));
		if($active)
		{
			$select->where('c.[Active] =?', 1);
		}
		$select->order('c.'.$params.' DESC');
		$rows = &$this->all($select);
	
		return $rows;
	}
	
	public function updatePoll($id, $offset=1)
	{
		$sql = "UPDATE ".$this->sn()." SET Poll=Poll+".$offset." WHERE ID='".$id."'";
		return $this->db->query($sql);
	}
	
	public function updateActive($tag,$uid)
	{
		$sql = "UPDATE ".$this->sn()." SET Active='".$tag."' WHERE ID='".$uid."'";
		return $this->db->query($sql);
	}
}