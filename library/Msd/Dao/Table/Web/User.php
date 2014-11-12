<?php

class Msd_Dao_Table_Web_User extends Msd_Dao_Table_Web
{
	protected static $instance = null;
	
	public function __construct()
	{
		parent::__construct();
		
		$this->_name = $this->prefix.'Users';
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
	
	public function insert(array $params)
	{
		return parent::insert($params);
	}
	
	public function &search(&$pager, array $where=array(), array $order=array())
	{
		$rows = array();
		$count = $pager['limit'] ? (int)$pager['limit'] : 20;
		$page = $pager['page'] ? (int)$pager['page'] : 1;
		$offset = $pager['offset'] ? (int)$pager['offset'] : 0;
		
		$select = $this->s();
		$cSelect = $this->s();
		
		$cTable = $this->t('customer');
		$pTable = $this->t('customer/phone');
		$aTable = $this->t('attachment');
		
		$select->from($this->sn('u'), 'u.*');
		$cSelect->from($this->sn('u'), 'COUNT(*) AS total');
		
		$select->join($cTable->sn('c'), 'u.CustGuid=c.CustGuid', array(
				'c.CustName',
				'c.Mail'
				));
		$cSelect->join($cTable->sn('c'), 'u.CustGuid=c.CustGuid', '');
		
		$select->joinleft($aTable->sn('a'), 'u.Avatar=a.Hash AND a.Usage=2', array(
				'a.FileId AS AvatarId',
			));
		$cSelect->joinleft($aTable->sn().' AS a', 'u.Avatar=a.Hash', '');
		/*
		$select->joinleft($pTable->sn('p'), 'u.CustGuid=p.CustGuid AND p.PhoneType=1', array(
				'p.PhoneNumber'
				));
		$cSelect->joinleft($pTable->sn('p'), 'u.CustGuid=p.CustGuid AND p.PhoneType=1', '');		
		*/
		if ($where['Username']) {
			$select->where('u.Username LIKE ?', '%'.$where['Username'].'%');
			$cSelect->where('u.Username LIKE ?', '%'.$where['Username'].'%');
		}
		
		if ($where['RealName']) {
			$select->where('c.CustName LIKE ?', '%'.$where['RealName'].'%');
			$cSelect->where('c.CustName LIKE ?', '%'.$where['RealName'].'%');
		}
		
		if ($where['Email']) {
			$select->where('c.Mail LIKE ?', '%'.$where['Email'].'%');
			$cSelect->where('c.Mail LIKE ?', '%'.$where['Email'].'%');
		}
		/*
		if ($where['Cellphone']) {
			$select->where('p.PhoneNumber LIKE ?', '%'.$where['Cellphone'].'%');
			$cSelect->where('p.PhoneNumber LIKE ?', '%'.$where['Cellphone'].'%');
		}*/
		
		if (count($order)>0) {
			foreach ($order as $key=>$val) {
				$select->order($key.' '.$val);
			}
		} else {
			$select->order('u.'.$this->primary().' DESC');
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
	
	public function updateActive($randomstring,$custGuid,$verify)
	{
		$data = array(
				'Active' => '1',
				'VerifyString' => $randomstring
		);
		
		$where = $this->db->quoteInto('Verifystring =?', $verify);
		$where .= ' AND '.$this->db->quoteInto('custGuid =?', $custGuid);
		return $this->update($data, $where);
	}
	public function doUpdate(array $params, $keyVal)
	{
		isset($params['LastLogin']) && $params['LastLogin'] = $this->expr('GETDATE()');
		
		return parent::doUpdate($params, $keyVal);
	}
}
