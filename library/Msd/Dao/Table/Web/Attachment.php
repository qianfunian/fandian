<?php

class Msd_Dao_Table_Web_Attachment extends Msd_Dao_Table_Web
{
	protected static $instance = null;
	
	public function __construct()
	{
		parent::__construct();
		
		$this->_name = $this->prefix.'Attachment';
		$this->_primary = 'FileId';
	}
	
	public static function &getInstance()
	{
		if (self::$instance==null) {
			self::$instance = new self();
		}

		return self::$instance;
	}
	
	public function &avatarByHash($hash)
	{
		$config = &Msd_Config::appConfig()->attachment->usage;
		$rows = array();
		
		$select = $this->db->select();
		$select->from($this->_name);
		$select->where('Hash=?', $hash);
		$select->where('Usage IN (?)', array(
				$config->avatar,
				$config->avatar_normal,
				$config->avatar_small
				));

		$result = $this->all($select);
		foreach ($result as $row) {
			$rows[$row['Usage']] = $row;
		}		
		
		return $rows;
	}

	public function insert(array $params)
	{
		$params['UploadTime'] = $this->expr('GETDATE()');
		
		return parent::insert($params);
	}
	
	public function &getByHash($hash)
	{
		$rows = array();
		
		$select = $this->db->select();
		$select->from($this->_name);
		$select->where('Hash=?', $hash);
		$select->order('OrderNo ASC');
		$select->order('UploadTime DESC');

		$result = $this->all($select);
		$i = 0;
		foreach ($result as $row) {
			$row['_seq'] = 1 + ($i++);
			$rows[] = $row;
		}		
		
		return $rows;
	}
	
	public function &search(&$pager, array $where=array(), array $order=array())
	{
		$rows = array();
		$count = $pager['limit'] ? (int)$pager['limit'] : 20;
		$page = $pager['page'] ? (int)$pager['page'] : 1;
		$offset = $pager['offset'] ? (int)$pager['offset'] : 0;
		
		$select = $this->s();
		$cSelect = $this->s();
		
		$select->from($this->_name);
		$cSelect->from($this->_name, 'COUNT(*) AS total');
		
		if ($where['Name']) {
			$select->where('Name LIKE ?', '%'.$where['Name'].'%');
			$cSelect->where('Name LIKE ?', '%'.$where['Name'].'%');
		}
		
		if ($where['Uid']) {
			$select->where('Uid=?', $where['Uid']);
			$cSelect->where('Uid=?', $where['Uid']);
		}
		
		if ($where['Usage']) {
			$select->where('Usage IN (?)', (array)$where['Usage']);
			$cSelect->where('Usage IN (?)', (array)$where['Usage']);
		}
		
		if (count($order)>0) {
			foreach ($order as $key=>$val) {
				$select->order($key.' '.$val);
			}
		} else {
			$select->order($this->primary().' DESC');
		}
		
		$select->limitPage($page, $count);

		$result = $this->all($select);
		$i = 0;
		foreach ($result as $row) {
			$row['_seq'] = $offset + 1 + ($i++);
			$rows[] = $row;
		}
	
		$tmp = $this->one($cSelect);
		$pager['total'] = $tmp['total'];
		
		return $rows;
	}
}