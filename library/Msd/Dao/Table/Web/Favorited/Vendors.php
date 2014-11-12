<?php

class Msd_Dao_Table_Web_Favorited_Vendors extends Msd_Dao_Table_Web
{
	protected static $instance = null;
	
	public function __construct()
	{
		parent::__construct();
		
		$this->_name = $this->prefix.'FavoritedVendors';
		$this->_primary = 'AutoId';
	}
	
	public static function &getInstance()
	{
		if (self::$instance==null) {
			self::$instance = new self();
		}
		
		return self::$instance;
	}
	
	public function isFavorited($VendorGuid, $CustGuid)
	{
		$select = &$this->s();
		$select->from($this->_name);
		$select->where('CustGuid=?', $CustGuid);
		$select->where('VendorGuid=?', $VendorGuid);
		$select->limit(1);
		
		$row = $this->one($select);
		
		return $row ? $row['AutoId'] : false;
	}

	public function insert(array $params)
	{
		(isset($params['CustGuid']) && !($params['CustGuid'] instanceof Zend_Db_Expr)) && $params['CustGuid'] = $this->wrapGuid($params['CustGuid']);
		(isset($params['VendorGuid']) && !($params['VendorGuid'] instanceof Zend_Db_Expr)) && $params['VendorGuid'] = $this->wrapGuid($params['VendorGuid']);
		
		$params['CreateTime'] = $this->expr('GETDATE()');
		
		return parent::insert($params);
	}
	
	public function &search(&$pager, array $where=array(), array $order=array())
	{
		$rows = array();
		$count = $pager['limit'] ? (int)$pager['limit'] : 20;
		$page = $pager['page'] ? (int)$pager['page'] : 1;
		$offset = $pager['offset'] ? (int)$pager['offset'] : 0;
		
		$vTable = &$this->t('vendor');
		$aTable = &$this->t('vendor/address');
		$rTable = &$this->t('region');
		$ctgTable = &$this->t('category/group');
		$cTable = &$this->t('category');
		$eTable = &$this->t('vendor/extend');
		
		$select = $this->s();
		$cSelect = $this->s();

		$aSelected = 'a.Address';
		
		if ($where['Longitude'] && $where['Latitude'] && $where['Distance']) {
			$_where = "dbo.Fn_GetDistance(a.CoordValue, ".(float)$where['Latitude'].", ".(float)$where['Longitude'].")";
			$aSelected .= ','.$_where.' AS Distance';
			
			list($DistanceStart, $DistanceEnd) = explode(',', $where['Distance']);
			$select->where($this->expr(
					'('.$_where.' BETWEEN '.$DistanceStart.' AND '.$DistanceEnd.')'
				));
			$cSelect->where($this->expr(
					'('.$_where.' BETWEEN '.$DistanceStart.' AND '.$DistanceEnd.')'
				));			
		}
		
		$select->from($this->sn('f'));
		$select->join($vTable->sn('v'), 'f.VendorGuid=v.VendorGuid', 'v.*');;
		$select->joinleft($aTable->sn('a'), 'a.VendorGuid=v.VendorGuid', $aSelected);
		$select->joinleft($rTable->sn('r'), 'r.RegionGuid=v.RegionGuid', 'r.RegionName');
		$select->joinleft($ctgTable->sn('ctg'), 'ctg.CtgGroupGuid=v.CtgGroupGuid', 'ctg.CtgGroupName');
		$select->joinleft($eTable->sn('e'), 'e.VendorGuid=v.VendorGuid', array(
				'e.SmallLogo', 'e.BigLogo'
				));

		$cSelect->from($this->sn('f'),'COUNT(*) AS total');
		$cSelect->join($vTable->sn('v'), 'f.VendorGuid=v.VendorGuid', '');
		$cSelect->joinleft($aTable->sn('a'), 'a.VendorGuid=v.VendorGuid', '');
		$cSelect->joinleft($rTable->sn('r'), 'r.RegionGuid=v.RegionGuid', '');
		$cSelect->joinleft($ctgTable->sn('ctg'), 'ctg.CtgGroupGuid=v.CtgGroupGuid', '');
		$cSelect->joinleft($eTable->sn('e'), 'e.VendorGuid=v.VendorGuid', '');

		if (strlen($where['CategoryName'])) {
			$select->where('ctg.CtgGroupName LIKE ?', '%'.$where['CategoryName'].'%');
			$cSelect->where('ctg.CtgGroupName LIKE ?', '%'.$where['CategoryName'].'%');
		}
		
		if (strlen($where['Address'])) {
			$select->where('a.Address LIKE ?', '%'.$where['Address'].'%');
			$cSelect->where('a.Address LIKE ?', '%'.$where['Address'].'%');
		}
		
		if (isset($where['Disabled'])) {
			$select->where('v.Disabled=?', (int)$where['Disabled']);
			$cSelect->where('v.Disabled=?', (int)$where['Disabled']);
		}
		
		if ($where['Region']) {
			$select->where('v.RegionGuid=?', $where['Region']);
			$cSelect->where('v.RegionGuid=?', $where['Region']);
		}
		
		if ($where['RegionName']) {
			$select->where('r.RegionName=?', $where['RegionName']);
			$cSelect->where('r.RegionName=?', $where['RegionName']);
		}
		
		if ($where['VendorName']) {
			$select->where('v.VendorName LIKE ?', '%'.$where['VendorName'].'%');
			$cSelect->where('v.VendorName LIKE ?', '%'.$where['VendorName'].'%');
		}
		
		if (isset($where['Disabled'])) {
			$select->where('v.Disabled=?', $where['Disabled']);
			$cSelect->where('v.Disabled=?', $where['Disabled']);
		}
		
		if (isset($where['ServiceStatus'])) {
			$select->where('v.ServiceStatus=?', $where['ServiceStatus']);
			$cSelect->where('v.ServiceStatus=?', $where['ServiceStatus']);
		}
		
		if ($where['CtgGroup']) {
			$select->where('v.CtgGroupGuid=?', $where['CtgGroup']);
			$cSelect->where('v.CtgGroupGuid=?', $where['CtgGroup']);
		}
		
		if ($where['SortGroup']) {
			$select->where('v.SortGroup=?', $where['SortGroup']);
			$cSelect->where('v.SortGroup=?', $where['SortGroup']);
		}
		
		if ($where['CustGuid']) {
			$select->where('f.CustGuid=?', $where['CustGuid']);
			$cSelect->where('f.CustGuid=?', $where['CustGuid']);
		}

		if (count($order)>0) {
			foreach ($order as $key=>$val) {
				$select->order($key.' '.$val);
			}
		} else {
			$select->order('f.CreateTime DESC');
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