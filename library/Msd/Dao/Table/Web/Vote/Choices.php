<?php

class Msd_Dao_Table_Web_Vote_Choices extends Msd_Dao_Table_Web
{
	protected static $instance = null;
	
	public function __construct()
	{
		parent::__construct();
		
		$this->_name = $this->prefix.'VoteChoices';
		$this->_primary = 'AutoId';
		
	}
	
	public static function &getInstance()
	{
		if (self::$instance==null) {
			self::$instance = new self();
		}
		
		return self::$instance;
	}
	
	public function addChoice($cid, $offset=1)
	{
		$sql = "UPDATE ".$this->sn()." SET Choosed=Choosed+".$offset." WHERE AutoId='".$this->q($cid)."'";
		return $this->db->query($sql);
	}
	
	public function &getChoices($vid)
	{
		$rows = array();
		
		$select = &$this->s();
		$select->from($this->sn('vc'));
		$select->where('VoteId=?', $vid);
		$select->where('ChoiceTitle!=?', '');
		$select->order('vc.AutoId ASC');
		
		$rows = $this->all($select);
		
		return $rows;
	}
	
	public function delChoices($vid)
	{
		$rows = $this->getChoices($vid);
		foreach ($rows as $row) {
			$this->doDelete($row['AutoId']);
		}
	}
}