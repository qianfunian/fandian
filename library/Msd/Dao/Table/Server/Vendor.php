<?php

class Msd_Dao_Table_Server_Vendor extends Msd_Dao_Table_Server
{
	protected static $instance = null;
	
	public function __construct()
	{
		parent::__construct();
		
		$this->_name = $this->prefix.'Vendor';
		$this->_primary = 'VendorGuid';
		$this->_orderKey = 'VendorId';
		$this->_realPrimary = 'VendorGuid';
	}
	
	public static function &getInstance()
	{
		if (self::$instance==null) {
			self::$instance = new self();
		}

		return self::$instance;
	}
	
	public function &Categories($CityId='wx')
	{
		$rows = array();
		
		$config = &Msd_Config::appConfig();
		$CtgStdName = $config->db->n->ctg_std_name->vendor;
		
		$select = &$this->s();
		$ctgTable = &$this->t('category/group');
		$cgmTable = &$this->t('category/groupmember');
		$cTable = &$this->t('category');
		$csTable = &$this->t('category/standard');
		
		$select->from($this->sn('v'), array(
			'v.CityId'	
			));
		$select->join($cgmTable->sn('cgm'), 'cgm.CtgGroupGuid=v.CtgGroupGuid', array());
		$select->join($cTable->sn('c'), 'c.CtgGuid=cgm.CtgGuid', array(
			'c.CtgGuid', 'c.CtgName'
			));
		$select->join($csTable->sn('cs'), 'cs.CtgStdGuid=c.CtgStdGuid', array(
			'cs.CtgStdName'	
			));
		$select->group('v.CityId');
		$select->group('c.CtgGuid');
		$select->group('c.CtgName');
		$select->group('c.AddTime');
		$select->group('cs.CtgStdName');
		$select->order('c.AddTime ASC');
		$select->where('v.CityId=?', $CityId);
		$select->where('v.Disabled=?', 0);

		$rows = $this->all($select);
		
		return $rows;
	}
	
	public function getById($VendorId)
	{
		$select = &$this->s();
		$select->from($this->sn('v'));
		$select->where('VendorId=?', $VendorId);
		$select->limit(1);
		
		return $this->one($select);
	}
	
	public function &topSales(array $AreaGuids, $limit=10)
	{
		$select = &$this->s();
		$iTable = &$this->t('item');
		$ieTable = &$this->t('item/extend');
		
		$select->from($this->sn('v'), 'v.VendorName');
		$select->join($iTable->sn('i'), 'i.VendorGuid=v.VendorGuid', array());
		$select->join($ieTable->sn('ie'), 'ie.ItemGuid=i.ItemGuid', array(
			$this->expr('SUM(ie.Sales) AS Sales')	
			));
		$select->group('i.VendorGuid');
		$select->group('v.VendorName');
		$select->where('v.AreaGuid IN (?)', $AreaGuids);
		$select->where('v.Disabled=?', '0');
		$select->order('Sales DESC');
		$select->limit($limit);

		$rows = $this->all($select);
		
		return $rows;
	}
	
	public function &InService($VendorGuid, $st=null)
	{
		$st || $st = date('Y-m-d H:i:s');
		
		$select = &$this->s();
		$select->from($this->sn('v'), array(
			$this->expr("[dbo].Fn_IsInVendorServiceTime('".$VendorGuid."', '".$st."', 0, 0) AS InService")	
			));
		$select->where('VendorGuid=?', $VendorGuid);
		$row = $this->one($select);
		
		return (int)$row['InService'];
	}
	
	public function &getServiceTimeString($VendorGuid)
	{
		$select = &$this->s();
		$select->from($this->sn('v'), array(
			$this->expr('[dbo].Fn_GetVendorServiceTime(v.VendorGuid) AS ServiceTime')	
			));
		$select->where('v.VendorGuid=?', $VendorGuid);
		$select->limit(1);

		$row = $this->one($select);
		
		return $row['ServiceTime'];
	}
	
	public function &getByName($VendorName, $AreaGuid='')
	{
		$select = &$this->s();
		$select->from($this->sn('v'));
		$select->where('VendorName=N?', $VendorName);
		Msd_Validator::isGuid($AreaGuid) && $select->where('AreaGuid=?', $AreaGuid);
		$select->limit(1);

		return $this->one($select);
	}
	
	/**
	 * 首页根据区域读取商家、商圈
	 * 
	 * @param string $region
	 */
	public function &idxBizArea($region, $IsRec=null, $limit=200)
	{
		$rows = array();
		$config = &Msd_Config::appConfig();
		$cConfig = &Msd_Config::cityConfig();
		$bizArea = $config->db->biz_area_category_std;
		
		$cgTable = &$this->t('category/group');
		$cgmTable = &$this->t('category/groupmember');
		$cTable = &$this->t('category');
		$csTable = &$this->t('category/standard');
		$veTable = &$this->t('vendor/extend');
		
		$select = &$this->s();
		$select->from($this->sn('v'), array(
				'v.VendorGuid', 
				'v.VendorName'
			));
		
		$select->join($cgTable->sn('cg'), 'cg.CtgGroupGuid=v.CtgGroupGuid', '');
		$select->join($cgmTable->sn('cgm'), 'cgm.CtgGroupGuid=v.CtgGroupGuid', '');
		$select->join($cTable->sn('c'), 'c.CtgGuid=cgm.CtgGuid', '');
		$select->join($csTable->sn('cs'), 'cs.CtgStdGuid=c.CtgStdGuid', array(
			'c.CtgName'	
			));
		$select->joinleft($veTable->sn('ve'), 've.VendorGuid=v.VendorGuid', '');
		
		$select->where('v.RegionGuid=?', $region);
		$select->where('cs.CtgStdName=N?', $config->db->n->ctg_std_name->biz);
		$select->where('v.Disabled=?', '0');
		$select->where('v.VendorGuid!=?', $cConfig->db->guids->mini_market);
		
		if ($IsRec!=null) {
			$select->where('ve.IsRec=?', (int)$IsRec);
		}
		
		$select->order('ve.HotRate DESC');
		$select->limit($limit);

		$result = $this->all($select);
		$i = 0;
		foreach ($result as $row) {
			$row['_seq'] = 1 + ($i++);
			$rows[] = $row;
		}

		return $rows;
	}
	
	public function &RecVendors($limit=10)
	{
		$rows = array();
		$config = &Msd_Config::appConfig();
		
		$pager = array(
			'limit' => $limit,
			'page' => 1,
			'offset' => 0,
			'skip' => 0
			);
		$where = array(
			'Disabled' => '0',
			'exclude_yx' => true,
			'exclude_mini' => true,
			'IsRec' => 1
			);
		$order = array(
			'HotRate' => 'DESC'	
			);
		$rows = $this->newsearch($pager, $where, $order);

		return $rows;
	}
	
	public function &IdxRecVendors($AreaGuids, $limit=10)
	{
		$rows = array();
		$config = &Msd_Config::appConfig();
		
		$pager = array(
			'limit' => $limit,
			'page' => 1,
			'offset' => 0,
			'skip' => 0
			);
		$where = array(
			'Disabled' => '0',
			'exclude_yx' => true,
			'IsIdxRec' => 1,
			'AreaGuid' => $AreaGuids
			);
		$order = array(
			'OrderNo' => 'ASC',
			'HotRate' => 'DESC'	
			);
		$rows = $this->search($pager, $where, $order);

		return $rows;
	}

	/**
	 * 商家搜索
	 * 
	 * @param array $pager
	 * @param array $where
	 * @param array $order
	 */
	public function &newsearch(&$pager, array $where=array(), array $order=array())
	{
		$rows = array();
		$count = $pager['limit'] ? (int)$pager['limit'] : 20;
		$page = $pager['page'] ? (int)$pager['page'] : 1;
		$offset = $pager['skip'] ? (int)$pager['skip'] : 0;

		$config = &Msd_Config::appConfig();
		$cConfig = &Msd_Config::cityConfig();
		$cityConfig = &$cConfig;
		
		if (!isset($where['CtgStdGuid']) || !Msd_Validator::isGuid($where['CtgStdGuid'])) {
			$where['CtgStdGuid'] = $config->db->category_standard->vendor; 
		}

		$norder = array();
		$aSelected = 'a.Address';
		
		$to = array();
		$selected = "v.VendorGuid, v.VendorName, v.ServiceStatus, v.CtgGroupGuid, v.Remark, 
				[dbo].Fn_IsInVendorServiceTime(v.VendorGuid, GETDATE(), 0, 0) AS InService, 
				[dbo].Fn_IsInVendorServiceTime(v.VendorGuid, GETDATE(), -30, -30) AS OpenWebService, 
				[dbo].Fn_GetVendorServiceTime(v.VendorGuid) AS ServiceTimeString,
				r.RegionName,
				ctg.CtgGroupName,
				a.Address,
				e.HotRate,e.HasLogo, e.IsIdxRec, e.IsRec, e.OrderNo
			";
		
		$join = "
				LEFT JOIN VendorAddress AS a
					ON a.VendorGuid=v.VendorGuid
				LEFT JOIN Region AS r
					ON r.RegionGuid=v.RegionGuid
				LEFT JOIN CategoryGroup AS ctg
					ON ctg.CtgGroupGuid=v.CtgGroupGuid
				LEFT JOIN W_VendorExtend AS e
					ON e.VendorGuid=v.VendorGuid
			";

		$ands = array();
		
		if ($where['Longitude'] && $where['Latitude'] && $where['Distance']) {
			$_where = "[dbo].Fn_GetDistance(a.CoordValue, ".(float)$where['Longitude'].", ".(float)$where['Latitude'].")";
			$selected .= ','.$_where.' AS Distance';
		
			list($DistanceStart, $DistanceEnd) = explode(',', $where['Distance']);
			
			$ands[] = '('.$_where.' BETWEEN '.((int)$DistanceStart).' AND '.((int)$DistanceEnd).')';
			$order['Distance'] = 'ASC';
		}
		
		if (strlen($where['CategoryName'])) {
			$ands[] = "ctg.CtgGroupName LIKE N".$this->q('%'.$where['CategoryName'].'%')."";
		}
		
		if (strlen($where['Address'])) {
			$ands[] = "a.Address LIKE ".$this->q('%'.$where['Address'].'%')."";
		}
		
		if (isset($where['Disabled'])) {
			$ands[] = "v.Disabled='".intval($where['Disabled'])."'";
		}
		
		if ($where['Region']) {
			$ands[] = "v.RegionGuid=".$this->q($where['Region'])."";
		}

		if ($where['RegionName']) {
			$ands[] = "r.RegionName=N".$this->q($where['RegionName'])."";
		}
		
		if ($where['ServiceStatus']) {
			$ands[] = "v.ServiceStatus=".$this->q($where['ServiceStatus'])."";
		}
		
		if (isset($where['exclude_yx'])) {
			$ands[] = "v.VendorName NOT LIKE N'%".$config->db->n->vendor_name->yexiao."%'";
		}

		if (isset($where['IsRec'])) {
			$ands[] = "e.IsRec='".intval($where['IsRec'])."'";
		}
		
		if (isset($where['IsIdxRec'])) {
			$ands[] = "e.IsIdxRec='".intval($where['IsIdxRec'])."'";
		}
		
		if ($where['CtgGroup']) {
			$ands[] = "v.CtgGroupGuid=".$this->q($where['CtgGroup']);
		}
		
		if ($where['CtgName']) {
			$ands[] = "ctg.CtgGroupName like ".$this->q('%'.$where['CtgName'].'%');
		}
		
		if ($where['SortGroup']) {
			$ands[] = "v.SortGroup=".$this->q($where['SortGroup']);
		}
		
		if (is_array($where['passby_category']) && count($where['passby_category'])>0) {
			$ands[] = "c.CtgName NOT IN ('".implode("','", $where['passby_category'])."')";
		}
		
		if ($where['Regions'] && is_array($where['Regions']) && count($where['Regions'])>0) {
			$ands[] = "v.RegionGuid IN ('".implode("','", $where['Regions'])."')";
		}
		
		if ($where['AreaGuid']) {
			$ands[] = "v.AreaGuid IN ('".implode("','", (array)$where['AreaGuid'])."')";
		}
		
		if ($where['BizArea']) {
			$ands[] = "e.BizArea IN ('".implode("','", (array)$where['BizArea'])."')";
		}
		
		if ($where['CityId']) {
			$ands[] = "v.CityId=".$this->q($where['CityId']);
		}
		
		if ($where['CustGuid']) {
			$join .= "
				LEFT JOIN W_FavoritedVendors AS f
					ON f.VendorGuid=v.VendorGuid AND f.CustGuid=".$this->q($where['CustGuid'])."
			";
			$selected .= "
				,CASE 
					WHEN f.VendorGuid IS NOT NULL 
						THEN 1 
					ELSE 0 
				END 
				AS Favorited
			";
			$to[] = "Favorited DESC";
		}
		
		if ($where['VendorName']) {
			$ands[] = "(v.VendorName LIKE ".$this->q('%'.$where['VendorName'].'%')."
				OR
				(
					v.VendorGuid IN (
						SELECT DISTINCT(i.VendorGuid) AS VendorGuid
							FROM Item AS i
								INNER JOIN Vendor AS v
									ON v.VendorGuid=i.VendorGuid AND v.Disabled='0'
							WHERE i.ItemName LIKE ".$this->q('%'.$where['VendorName'].'%')."
					)
				)
			)
			";
		}
		
		if ($where['exclude_mini']) {
			$ands[] = "v.VendorGuid!=".$this->q($cConfig->db->guids->mini_market);
		}

		$sql = "SELECT ".$selected."
		FROM Vendor AS v
		";
		$sql .= $join;
		$sql .= "
		WHERE ".implode(' AND ', $ands);
		
		if ($where['service']) {
			$sql .= " AND v.VendorGuid IN (
				SELECT DISTINCT i.VendorGuid
					FROM Item AS i
						INNER JOIN Vendor AS v
							ON v.VendorGuid=i.VendorGuid
						INNER JOIN ServiceCombin AS sc
							ON sc.SrvCmbGuid=i.SrvCmbGuid
						WHERE v.Disabled=0 AND i.Disabled=0 AND sc.SrvCmbName like N".$this->q('%'.$where['service'].'%')."
			)";
		}
		
		$cSql = $sql;

		$oSql = "";
		if ($where['VendorName']) {
			$to[] = "CASE WHEN VendorName LIKE ".$this->q('%'.$where['VendorName'].'%')."  THEN 0 ELSE 1 END";	
		}
		
		if (count($order)>0) {
			foreach ($order as $key=>$val) {
				if ($key=='_random_') {
					$to[] = 'NEWID()';
				} else {
					if (strstr('.', $key)) {
						list($foo, $key) = explode('.', $key);
					}
					$to[] = $key.' '.$val;
				}
			}
				
			$oSql .= "
			ORDER BY ".implode(', ', $to)."
			";
		} else {
			$oSql .= "
			ORDER BY ".$this->_orderKey." DESC
			";
		}
		
		$sql = 'SELECT * FROM (
			SELECT ROW_NUMBER() OVER ('.$oSql.') AS "P_DB_NUMBER", * 
				FROM ('.$sql.') AS inner_tbl
		) AS outter_tbl WHERE "P_DB_NUMBER" BETWEEN '.($pager['skip']+1).' AND '.($pager['skip']+$pager['limit']);
		
		//header('Content-Type:text/html;charset=utf-8');Msd_Debug::dump($where);die(nl2br($sql));
		$result = $this->all($sql);

		$i = 0;
		foreach ($result as $row) {
			$row['_seq'] = $pager['skip'] + $i + 1;
			$rows[] = $row;
			
			$i++;
		}

		if (!isset($where['passby_pager'])) {
			$cSelect = str_replace($selected, 'COUNT(*) AS total', $cSql);
			$tmp = $this->one($cSelect);
			$pager['total'] = $tmp['total'];
			
			$pages = Msd_Dao::Pages($pager['total'], $pager['limit']);
			if ($pager['page']>$pages) {
				$rows = array();
			} else if ($pager['page']==$pages) {
				$correct = $pager['total'] - ($pages-1)*$pager['limit'];
				
				if ($correct<count($rows)) {
					$tmp = array_chunk($rows, $correct);
					$rows = $tmp[0];
				}
			}
		}
		
		return $rows;
	}
	
	/**
	 * 商家搜索
	 *
	 * @param array $pager
	 * @param array $where
	 * @param array $order
	 */
	public function &search(&$pager, array $where=array(), array $order=array())
	{
		$rows = array();
		$count = $pager['limit'] ? (int)$pager['limit'] : 20;
		$page = $pager['page'] ? (int)$pager['page'] : 1;
		$offset = $pager['skip'] ? (int)$pager['skip'] : 0;
	
		$config = &Msd_Config::appConfig();
		$cConfig = &Msd_Config::cityConfig();
		$cityConfig = &$cConfig;
	
		if (!isset($where['CtgStdGuid']) || !Msd_Validator::isGuid($where['CtgStdGuid'])) {
			$where['CtgStdGuid'] = $config->db->category_standard->vendor;
		}
	
		$norder = array();
		$aSelected = 'a.Address';
	
		$to = array();
		$selected = "v.VendorGuid, v.RegionGuid, v.VendorId, v.VendorName, v.ServiceStatus, v.CtgGroupGuid, v.Remark, v.Disabled, v.AddTime,
				[dbo].Fn_IsInVendorServiceTime(v.VendorGuid, GETDATE(), 0, 0) AS InService,
				[dbo].Fn_IsInVendorServiceTime(v.VendorGuid, GETDATE(), -30, -30) AS OpenWebService,
				[dbo].Fn_GetVendorServiceTime(v.VendorGuid) AS ServiceTimeString,
				r.RegionName,
				ctg.CtgGroupName,
				c.CtgName,
				cs.CtgStdName,
				a.Address,
				e.HotRate,e.HasLogo, e.IsIdxRec, e.IsRec, e.OrderNo
			";
	
		$join = "
				LEFT JOIN VendorAddress AS a
					ON a.VendorGuid=v.VendorGuid
				LEFT JOIN Region AS r
					ON r.RegionGuid=v.RegionGuid
				LEFT JOIN CategoryGroup AS ctg
					ON ctg.CtgGroupGuid=v.CtgGroupGuid
				LEFT JOIN CategoryGroupMember AS cgm
					ON cgm.CtgGroupGuid=v.CtgGroupGuid
				LEFT JOIN Category AS c
					ON c.CtgGuid=cgm.CtgGuid
				LEFT JOIN CategoryStandard AS cs
					ON cs.CtgStdGuid=c.CtgStdGuid
				LEFT JOIN W_VendorExtend AS e
					ON e.VendorGuid=v.VendorGuid
			";
	
		$ands = array();
	
		if ($where['Longitude'] && $where['Latitude'] && $where['Distance']) {
			$_where = "[dbo].Fn_GetDistance(a.CoordValue, ".(float)$where['Longitude'].", ".(float)$where['Latitude'].")";
			$selected .= ','.$_where.' AS Distance';
	
			list($DistanceStart, $DistanceEnd) = explode(',', $where['Distance']);
				
			$ands[] = '('.$_where.' BETWEEN '.((int)$DistanceStart).' AND '.((int)$DistanceEnd).')';
			$order['Distance'] = 'ASC';
		}
	
		if (strlen($where['CategoryName'])) {
			$ands[] = "ctg.CtgGroupName LIKE N".$this->q('%'.$where['CategoryName'].'%')."";
		}
	
		if (strlen($where['Address'])) {
			$ands[] = "a.Address LIKE ".$this->q('%'.$where['Address'].'%')."";
		}
	
		if (isset($where['Disabled'])) {
			$ands[] = "v.Disabled='".intval($where['Disabled'])."'";
		}
	
		if ($where['Region']) {
			$ands[] = "v.RegionGuid=".$this->q($where['Region'])."";
		}
	
		if ($where['RegionName']) {
			$ands[] = "r.RegionName=N".$this->q($where['RegionName'])."";
		}
	
		if ($where['ServiceStatus']) {
			$ands[] = "v.ServiceStatus=".$this->q($where['ServiceStatus'])."";
		}
	
		if (isset($where['exclude_yx'])) {
			$ands[] = "v.VendorName NOT LIKE N'%".$config->db->n->vendor_name->yexiao."%'";
		}
	
		if (isset($where['IsRec'])) {
			$ands[] = "e.IsRec='".intval($where['IsRec'])."'";
		}
	
		if (isset($where['IsIdxRec'])) {
			$ands[] = "e.IsIdxRec='".intval($where['IsIdxRec'])."'";
		}
	
		if ($where['CtgGroup']) {
			$ands[] = "v.CtgGroupGuid=".$this->q($where['CtgGroup'])."";
		}
	
		if ($where['CtgGuid']) {
			$ands[] = "c.CtgGuid=".$this->q($where['CtgGuid'])."";
		}
	
		if ($where['SortGroup']) {
			$ands[] = "v.SortGroup=".$this->q($where['SortGroup'])."";
		}
	
		if (is_array($where['passby_category']) && count($where['passby_category'])>0) {
			$ands[] = "c.CtgName NOT IN ('".implode("','", $where['passby_category'])."')";
		}
	
		if ($where['Regions'] && is_array($where['Regions']) && count($where['Regions'])>0) {
			$ands[] = "v.RegionGuid IN ('".implode("','", $where['Regions'])."')";
		}
	
		if ($where['AreaGuid']) {
			$ands[] = "v.AreaGuid IN ('".implode("','", (array)$where['AreaGuid'])."')";
		}
	
		if ($where['BizArea']) {
			$ands[] = "e.BizArea IN ('".implode("','", (array)$where['BizArea'])."')";
		}
	
		if ($where['CityId']) {
			$ands[] = "v.CityId=".$this->q($where['CityId']);
		}
	
		if ($where['CustGuid']) {
			$join .= "
				LEFT JOIN W_FavoritedVendors AS f
					ON f.VendorGuid=v.VendorGuid AND f.CustGuid=".$this->q($where['CustGuid'])."
			";
			$selected .= "
				,CASE
					WHEN f.VendorGuid IS NOT NULL
						THEN 1
					ELSE 0
				END
				AS Favorited
			";
			$to[] = "Favorited DESC";
		}
	
		if ($where['CtgStdName']) {
			$ands[] = "cs.CtgStdName=N".$this->q($where['CtgStdName']);
		} else {
			$ands[] = "cs.CtgStdName=N".$this->q($config->db->n->ctg_std_name->vendor);
		}
	
		if ($where['VendorName']) {
			$ands[] = "(v.VendorName LIKE ".$this->q('%'.$where['VendorName'].'%')."
				OR
				(
					v.VendorGuid IN (
						SELECT DISTINCT(i.VendorGuid) AS VendorGuid
							FROM Item AS i
								INNER JOIN Vendor AS v
									ON v.VendorGuid=i.VendorGuid AND v.Disabled='0'
							WHERE i.ItemName LIKE ".$this->q('%'.$where['VendorName'].'%')."
					)
				)
			)
			";
		}
	
		if ($where['exclude_mini']) {
			$ands[] = "v.VendorGuid!=".$this->q($cConfig->db->guids->mini_market);
		}
	
		$sql = "SELECT ".$selected."
		FROM Vendor AS v
		";
		$sql .= $join;
		$sql .= "
		WHERE ".implode(' AND ', $ands);
	
		if ($where['service']) {
			$sql .= " AND v.VendorGuid IN (
				SELECT DISTINCT i.VendorGuid
					FROM Item AS i
						INNER JOIN Vendor AS v
							ON v.VendorGuid=i.VendorGuid
						INNER JOIN ServiceCombin AS sc
							ON sc.SrvCmbGuid=i.SrvCmbGuid
						INNER JOIN ServiceCombinMember AS scm
							ON scm.SrvCmbGuid=sc.SrvCmbGuid
						INNER JOIN ServiceItem AS si
							ON si.SrvGuid=scm.SrvGuid
						WHERE v.Disabled=0 AND i.Disabled=0 AND si.SrvName=N".$this->q($where['service'])."
			)";
		}
	
		$cSql = $sql;
	
		$oSql = "";
		if ($where['VendorName']) {
			$to[] = "CASE WHEN VendorName LIKE ".$this->q('%'.$where['VendorName'].'%')."  THEN 0 ELSE 1 END";
		}
	
		if (count($order)>0) {
			foreach ($order as $key=>$val) {
				if ($key=='_random_') {
					$to[] = 'NEWID()';
				} else {
					if (strstr('.', $key)) {
						list($foo, $key) = explode('.', $key);
					}
					$to[] = $key.' '.$val;
				}
			}
	
			$oSql .= "
			ORDER BY ".implode(', ', $to)."
			";
		} else {
			$oSql .= "
			ORDER BY ".$this->_orderKey." DESC
			";
		}
	
		$sql = 'SELECT * FROM (
			SELECT ROW_NUMBER() OVER ('.$oSql.') AS "P_DB_NUMBER", *
				FROM ('.$sql.') AS inner_tbl
		) AS outter_tbl WHERE "P_DB_NUMBER" BETWEEN '.($pager['skip']+1).' AND '.($pager['skip']+$pager['limit']);
	
		//header('Content-Type:text/html;charset=utf-8');Msd_Debug::dump($where);die(nl2br($sql));
		$result = $this->all($sql);
	
		$i = 0;
		foreach ($result as $row) {
			$row['_seq'] = $pager['skip'] + $i + 1;
			$rows[] = $row;
				
			$i++;
		}
	
		if (!isset($where['passby_pager'])) {
			$cSelect = str_replace($selected, 'COUNT(*) AS total', $cSql);
			$tmp = $this->one($cSelect);
			$pager['total'] = $tmp['total'];
				
			$pages = Msd_Dao::Pages($pager['total'], $pager['limit']);
			if ($pager['page']>$pages) {
				$rows = array();
			} else if ($pager['page']==$pages) {
				$correct = $pager['total'] - ($pages-1)*$pager['limit'];
	
				if ($correct<count($rows)) {
					$tmp = array_chunk($rows, $correct);
					$rows = $tmp[0];
				}
			}
		}
	
		return $rows;
	}
	
	public function &ServiceVendors($ServiceName)
	{
		$rows = array();
		
		$sql = "SELECT DISTINCT i.VendorGuid
					FROM Item AS i
						INNER JOIN Vendor AS v
							ON v.VendorGuid=i.VendorGuid
						INNER JOIN ServiceCombin AS sc
							ON sc.SrvCmbGuid=i.SrvCmbGuid
						INNER JOIN ServiceCombinMember AS scm
							ON scm.SrvCmbGuid=sc.SrvCmbGuid
						INNER JOIN Service AS s
							ON s.SrvGuid=scm.SrvGuid
						WHERE v.Disabled=0 AND i.Disabled=0 AND s.SrvName=N".$this->q($ServiceName)."";
		$tmp = $this->all($sql);
		foreach ($tmp as $row) {
			$rows[] = $row['VendorGuid'];
		}
		
		return $rows;
	}
	
	public function &guids()
	{
		$guids = array();
		
		$select = &$this->s();
		$select->from($this->name(), $this->primary());
		$result = $this->all($select);
		
		foreach ($result as $row) {
			$guids[] = $row[$this->primary()];
		}
		
		return $guids;
	}
}
