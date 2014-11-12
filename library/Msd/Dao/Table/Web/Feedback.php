<?php

class Msd_Dao_Table_Web_Feedback extends Msd_Dao_Table_Web
{
	protected static $instance = null;
	
	public function __construct()
	{
		parent::__construct();
		
		$this->_name = $this->prefix.'Feedback';
		$this->_primary = 'AutoId';
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
		$params['CreateTime'] || $params['CreateTime'] = $this->expr('GETDATE()');
		$params['DisplayFlag'] = isset($params['DisplayFlag']) ? (int)$params['DisplayFlag'] : 0;
		$params['ReplyContent'] = isset($params['ReplyContent']) ? $params['ReplyContent'] : '';
		isset($params['CustGuid']) && $params['CustGuid'] = $this->wrapGuid($params['CustGuid']);
		
		return parent::insert($params);
	}
	
	public function doUpdate(array $params, $keyVal)
	{
		if (isset($params['ReplyTime'])) {
			$params['ReplyTime'] = $this->expr('GETDATE()');
		}
		
		return parent::doUpdate($params, $keyVal);
	}	
	
	public function &search(&$pager, array $where=array(), array $order=array())
	{
		$rows = array();
		$count = $pager['limit'] ? (int)$pager['limit'] : 20;
		$page = $pager['page'] ? (int)$pager['page'] : 1;
		$offset = $pager['offset'] ? (int)$pager['offset'] : 0;

		$categoryTable = &Msd_Dao::table('article/category');
		$attachTable = &Msd_Dao::table('attachment');
		$aTable = &Msd_Dao::table('user');
		$rTable = &Msd_Dao::table('region');
		
		$select = $this->s();
		$cSelect = $this->s();
	
		$select->from($this->sn('f'), 'f.*');
		$cSelect->from($this->sn('f'), 'COUNT(*) AS total');
		
		$select->joinleft($aTable->sn('u'), 'u.CustGuid=f.CustGuid', array(
			'u.Avatar'	
			));
		$cSelect->joinleft($aTable->sn('u'), 'u.CustGuid=f.CustGuid', '');
		
		$select->joinleft($rTable->sn('r'), 'r.RegionGuid=f.RegionGuid', array(
			'r.RegionName'	
			));
		$cSelect->joinleft($rTable->sn('r'), 'r.RegionGuid=f.RegionGuid', '');
	
		if ($where['Content']) {
			$select->where('f.Content LIKE ?', '%'.$where['Content'].'%');
			$cSelect->where('f.Content LIKE ?', '%'.$where['Content'].'%');
		}
		
		if ($where['ReplyContent']) {
			$select->where('f.ReplyContent LIKE ?', '%'.$where['ReplyContent'].'%');
			$cSelect->where('f.ReplyContent LIKE ?', '%'.$where['ReplyContent'].'%');
		}
		
		if ($where['Regions'] && is_array($where['Regions']) && count($where['Regions'])>0) {
			$select->where('f.RegionGuid IN (?)', $where['Regions']);
			$cSelect->where('f.RegionGuid IN (?)', $where['Regions']);
		}
		
		if ($where['CustGuid']) {
			if (is_array($where['CustGuid']) && count($where['CustGuid'])) {
				$select->where('f.CustGuid IN (?)', $where['CustGuid']);
				$cSelect->where('f.CustGuid IN (?)', $where['CustGuid']);
			} else {
				$select->where('f.CustGuid=?', $where['CustGuid']);
				$cSelect->where('f.CustGuid=?', $where['CustGuid']);
			}
		}

		if (strlen($where['DisplayFlag'])) {
			$select->where('f.DisplayFlag=?', (int)$where['DisplayFlag']);
			$cSelect->where('f.DisplayFlag=?', (int)$where['DisplayFlag']);
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