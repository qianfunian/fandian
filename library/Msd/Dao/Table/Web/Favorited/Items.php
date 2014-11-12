<?php

class Msd_Dao_Table_Web_Favorited_Items extends Msd_Dao_Table_Web
{
	protected static $instance = null;
	
	public function __construct()
	{
		parent::__construct();
		
		$this->_name = $this->prefix.'FavoritedItems';
		$this->_primary = 'AutoId';
	}
	
	public static function &getInstance()
	{
		if (self::$instance==null) {
			self::$instance = new self();
		}
		
		return self::$instance;
	}
	
	public function isFavorited($ItemGuid, $CustGuid)
	{
		$select = &$this->s();
		$select->from($this->_name);
		$select->where('CustGuid=?', $CustGuid);
		$select->where('ItemGuid=?', $ItemGuid);
		$select->limit(1);
		
		$row = $this->one($select);
		
		return $row ? $row['AutoId'] : false;
	}
	
	public function &batchIsFavorited(array $ItemGuids, $CustGuid)
	{
		if (count($ItemGuids)>0) {
			$select = &$this->s();
			$select->from($this->_name);
			$select->where('CustGuid=?', $CustGuid);
			$select->where('ItemGuid IN (?)', $ItemGuids);
			
			$rows = $this->all($select);
		} else {
			$rows = array();
		}
		
		return $rows ? $rows : array();
	}

	public function insert(array $params)
	{
		(isset($params['CustGuid']) && !($params['CustGuid'] instanceof Zend_Db_Expr)) && $params['CustGuid'] = $this->wrapGuid($params['CustGuid']);
		(isset($params['ItemGuid']) && !($params['ItemGuid'] instanceof Zend_Db_Expr)) && $params['ItemGuid'] = $this->wrapGuid($params['ItemGuid']);
		
		$params['CreateTime'] = $this->expr('GETDATE()');
		
		return parent::insert($params);
	}
	
	public function &mine(&$pager, $CustGuid)
	{
		$rows = array();
		$count = $pager['limit'] ? (int)$pager['limit'] : 20;
		$page = $pager['page'] ? (int)$pager['page'] : 1;
		$offset = $pager['offset'] ? (int)$pager['offset'] : 0;
		
		$iTable = &Msd_Dao::table('item');
		$ctgTable = &Msd_Dao::table('category/group');
		$sTable = &Msd_Dao::table('sort/group');
		$vTable = &Msd_Dao::table('vendor');
		$iuTable = &Msd_Dao::table('item/unit');
		
		$select = $this->s();
		$cSelect = $this->s();
		
		$select->from($this->sn('f'), array(
				'f.CreateTime'
				));
		$cSelect->from($this->sn('f'), 'COUNT(*) AS total');
		
		$select->join($iTable->sn('i'), 'i.ItemGuid=f.ItemGuid', 'i.*');
		$cSelect->join($iTable->sn('i'), 'i.ItemGuid=f.ItemGuid', '');
		
		$select->joinleft($iuTable->sn('u'), 'i.UnitGuid=u.UnitGuid', array(
			'u.UnitName'
			));
		
		$select->joinleft($sTable->sn('s'), 'i.SortGroupGuid=s.SortGroupGuid', array(
			's.SortIndex'	
			));
		
		$select->join($vTable->sn('v'), 'i.VendorGuid=v.VendorGuid', array(
			'v.VendorName'
			));
		$cSelect->join($vTable->sn('v'), 'i.VendorGuid=v.VendorGuid', '');
		
		$select->join($ctgTable->sn('ctg'), 'i.CtgGroupGuid=ctg.CtgGroupGuid', array(
			'ctg.CtgGroupName'
			));
		$cSelect->join($ctgTable->sn('ctg'), 'i.CtgGroupGuid=ctg.CtgGroupGuid', '');

		$select->where('i.Disabled=?', 0);
		$cSelect->where('i.Disabled=?', 0);
		
		$select->where('f.CustGuid=?', $CustGuid);
		$cSelect->where('f.CustGuid=?', $CustGuid);

		$select->order('s.SortIndex DESC');

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