<?php

class Msd_Dao_Table_Server_Item extends Msd_Dao_Table_Server
{
	protected static $instance = null;
	
	public function __construct()
	{
		parent::__construct();
		
		$this->_name = $this->prefix.'Item';
		$this->_primary = 'ItemGuid';
		$this->_orderKey = 'ItemId';
		$this->_realPrimary = 'ItemGuid';
	}
	
	public static function &getInstance()
	{
		if (self::$instance==null) {
			self::$instance = new self();
		}

		return self::$instance;
	}
	
	/**
	 * @param string $CustGuid 登录用户id
	 * @param string $VendorGuid 餐店id
	 * @return array $rows 返回所有会员在此店所有预定过的餐品
	 */
	public function &getOrderedItems($CustGuid,$VendorGuid)
	{
		$sql="SELECT distinct i.ItemGuid,i.ItemId,i.ItemName,i.UnitPrice,i.MinOrderQty,i.Disabled,i.VendorGuid, i.ItemQty, i.BoxQty, i.BoxUnitPrice, i.Description, i.AddTime,u.UnitName, v.VendorName, ctg.CtgGroupName, c.CtgName, c.CtgGuid, ie.HasLogo, ie.IsRec, ie.Sales, ie.Persisted,ie.Detail, s.ServiceName FROM [Wuxi_Fandian].[dbo].[Item] AS i 
			INNER JOIN [Wuxi_Fandian].[dbo].ItemUnit AS u
				ON u.UnitGuid=i.UnitGuid
			INNER JOIN [Wuxi_Fandian].[dbo].Vendor AS v
				ON v.VendorGuid=i.VendorGuid
			INNER JOIN [Wuxi_Fandian].[dbo].CategoryGroup AS ctg
				ON ctg.CtgGroupGuid=i.CtgGroupGuid
			INNER JOIN [Wuxi_Fandian].[dbo].CategoryGroupMember AS cgm
				ON cgm.CtgGroupGuid=i.CtgGroupGuid
			INNER JOIN [Wuxi_Fandian].[dbo].Category AS c
				ON c.CtgGuid=cgm.CtgGuid
			INNER JOIN [Wuxi_Fandian].[dbo].ServiceCombin AS sc
				ON sc.SrvCmbGuid=i.SrvCmbGuid
			INNER JOIN [Wuxi_Fandian].[dbo].ServiceCombinMember AS scm
				ON scm.SrvCmbGuid=sc.SrvCmbGuid
			INNER JOIN [Wuxi_Fandian].[dbo].Service AS s
				ON s.ServiceGuid=scm.ServiceGuid
			INNER JOIN [Wuxi_Fandian].[dbo].CategoryStandard AS cs
				ON cs.CtgStdGuid=c.CtgStdGuid
			LEFT JOIN [Wuxi_Fandian].[dbo].W_ItemExtend AS ie
				ON ie.ItemGuid=i.ItemGuid
			INNER JOIN [Wuxi_Fandian].[dbo].[OrderItem] AS oi 
				ON oi.ItemGuid=i.ItemGuid 
			INNER JOIN [Wuxi_Fandian].[dbo].[OrderVersion] AS ov 
				ON ov.OrderGuid=oi.OrderGuid 
			INNER JOIN [Wuxi_Fandian].[dbo].[SalesVersion] AS sv 
				ON sv.SalesVerGuid=ov.SalesVerGuid 
			INNER JOIN [Wuxi_Fandian].[dbo].[Sales] AS ss 
				ON ss.SalesGuid=sv.SalesGuid 
			INNER JOIN [Wuxi_Fandian].[dbo].[Purchase] AS p 
				ON p.OrderGuid=ov.OrderGuid
			 WHERE (ss.CustGuid='".$CustGuid."') AND (p.VendorGuid='".$VendorGuid."') AND (s.ServiceName='普通')";
		
		return $this->all($sql);
	}
	
	public function getById($ItemId)
	{
		$select = &$this->s();
		$select->from($this->sn('i'));
		$select->where('ItemId=?', $ItemId);
		$select->limit(1);
		
		return $this->one($select);
	}
	
	public function getByName($name, $VendorGuid='')
	{
		$select = &$this->s();
		$select->from($this->sn('i'));
		$select->where('ItemName=ltrim(rtrim(?))', $name);
		if ($VendorGuid) {
			$select->where('VendorGuid=?', $VendorGuid);	
		}
		$select->limit(1);

		return $this->one($select);
	}
	
	public function &changedVendors($from)
	{
		$rows = array();
		
		$select = &$this->s();
		$select->from($this->sn('i'), array(
			$this->expr('DISTINCT(i.VendorGuid) AS VendorGuid')	
			));
		$select->where('i.AddTime>', $from);
		$rows = $this->all($select);
		
		return $rows;
	}
	
	public function &byGuids(array $ItemGuids)
	{
		$pager = $rows = array();
		$pager['limit'] = 50;
		$pager['page'] = 1;
		$pager['offset'] = 0;
		
		$rows = $this->search($pager, array(
			'Disabled' => 0,
			'ItemGuids' => $ItemGuids,
			'passby_pager' => 1	
			), array());
		
		return $rows;
	}
	
	public function &IdxHot($AreaGuids)
	{
		$pager = $rows = array();
		$pager['limit'] = 10;
		$pager['page'] = 1;
		$pager['offset'] = 0;
		
		$rows = $this->search($pager, array(
				'Disabled' => 0,
				'passby_pager' => 1,
				'passby_minimarket' => 1,
				'AreaGuid' => $AreaGuids,
				'IsRec' => 1,
				'HasLogo' => 1,
				'ServiceName' => '普通'
			), array(
				'_random_' => 1	
				));
		
		return $rows;
	}
	
	public function &VendorItems($VendorGuid, $Service='普通')
	{
		$rows = $pager = array();
		$pager['limit'] = 9999;
		$pager['page'] = 1;
		$pager['offset'] = 0;

		$rows = $this->search($pager, array(
				'Vendor' => $VendorGuid,
				'ServiceName' => $Service,
				'Disabled' => 0,
				'passby_pager' => 1
				), array(
					'HasLogo' => 'DESC',
					//'Sales' => 'DESC'
				));
		
		return $rows;
	}
	
	public function &VendorSignItems($VendorGuid,$ServiceName)
	{
		$config = &Msd_Config::appConfig();
		
		$rows = $pager = $where = $order = array();
		
		$pager = array(
			'page' => 1,
			'limit' => 999	
			);
		
		$where = array(
			'Vendor' => $VendorGuid,
			'Disabled' => 0,
			'HasLogo' => 1,
			'passby_pager' => 1,
			'ServiceName' =>$ServiceName,
			'CtgName' => $config->db->n->ctg_name->sign
			);
		$order = array(
			'_random_' => 'DESC'
			);
		
		$rows = $this->search($pager, $where, $order);
		
		return $rows;
	}
	
	public function &v12580(&$pager)
	{
		$rows = array();
		$count = $pager['limit'] ? (int)$pager['limit'] : 20;
		$page = $pager['page'] ? (int)$pager['page'] : 1;
		$offset = $pager['skip'] ? (int)$pager['skip'] : 0;
		$ends = $pager['skip']+$pager['limit'];
		
		$config = &Msd_Config::appConfig();
		$cConfig = &Msd_Config::cityConfig();
		
		$ands = array();
		$selected = "i.ItemGuid,i.ItemId,i.ItemName,i.UnitPrice,i.MinOrderQty,i.Disabled,i.VendorGuid, i.ItemQty, i.BoxQty, i.BoxUnitPrice, i.Description,
		u.UnitName, v.VendorName, ctg.CtgGroupName, c.CtgName, c.CtgGuid, ie.HasLogo, ie.IsRec";
		$join = "
		INNER JOIN ItemUnit AS u
			ON u.UnitGuid=i.UnitGuid
		INNER JOIN Vendor AS v
			ON v.VendorGuid=i.VendorGuid
		INNER JOIN CategoryGroup AS ctg
			ON ctg.CtgGroupGuid=i.CtgGroupGuid
		INNER JOIN CategoryGroupMember AS cgm
			ON cgm.CtgGroupGuid=i.CtgGroupGuid
		INNER JOIN Category AS c
			ON c.CtgGuid=cgm.CtgGuid
		INNER JOIN CategoryStandard AS cs
			ON cs.CtgStdGuid=c.CtgStdGuid
		INNER JOIN ServiceCombin AS sc
			ON sc.SrvCmbGuid=i.SrvCmbGuid
		INNER JOIN ServiceCombinMember AS scm
			ON scm.SrvCmbGuid=sc.SrvCmbGuid
		INNER JOIN Service AS s
			ON s.SrvGuid=scm.SrvGuid
		INNER JOIN W_ItemExtend AS ie
			ON ie.ItemGuid=i.ItemGuid
		";

		$ands[] = $this->db->quoteInto('v.VendorGuid!=?', $cConfig->db->guids->mini_market);
		$ands[] = $this->db->quoteInto('cs.CtgStdName=?', $config->db->n->ctg_std_name->item);
		$ands[] = $this->db->quoteInto('v.Disabled=?', '0');
		$ands[] = $this->db->quoteInto('s.ServiceName=N?', '普通');

		$sql = "SELECT ".$selected."
		FROM Item AS i
		";
		$sql .= $join;
		$sql .= "
		WHERE ".implode(' AND ', $ands);
		
		$cSql = "SELECT COUNT(*) AS total
		FROM Item AS i
		";
		$cSql .= $join;
		$cSql .= "
		WHERE ".implode(' AND ', $ands);
		
		$oSql = "
		ORDER BY IsRec DESC
		";

		$sql = 'SELECT * 
			FROM (
				SELECT ROW_NUMBER() OVER (
					'.$oSql.'
				) AS "P_DB_NUMBER", * FROM (
				'.$sql.'
				) AS inner_tbl
			) AS outter_tbl
			WHERE "P_DB_NUMBER" BETWEEN '.($pager['skip']+1).' AND '.($ends).'
		';

		$result = $this->all($sql);
		
		$i = 0;
		foreach ($result as $row) {
			$row['_seq'] = $pager['skip'] + $i + 1;
			$rows[] = $row;
				
			$i++;
		}
		
		if (!isset($where['passby_pager'])) {
			$tmp = $this->one($cSql);
			$pager['total'] = $tmp['total'];
			$pages = Msd_Dao::Pages($pager['total'], $pager['limit']);
			if ($pager['page']>$pages && $pages>0) {
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
	
	public function &search(&$pager, array $where=array(), array $order=array())
	{
		$rows = array();
		$count = $pager['limit'] ? (int)$pager['limit'] : 20;
		$page = $pager['page'] ? (int)$pager['page'] : 1;
		$offset = $pager['skip'] ? (int)$pager['skip'] : 0;
		$ends = $pager['skip']+$pager['limit'];

		$config = &Msd_Config::appConfig();
		$cConfig = &Msd_Config::cityConfig();

		$selected = "i.ItemGuid,i.ItemId,i.ItemName,i.UnitPrice,i.MinOrderQty,i.Disabled,i.VendorGuid, i.ItemQty, i.BoxQty, i.BoxUnitPrice, i.Description, i.AddTime, 
		u.UnitName, v.VendorName, ctg.CtgGroupName, c.CtgName, c.CtgGuid, ie.HasLogo, ie.IsRec, ie.Sales, ie.Persisted,ie.Detail, s.SrvName";
		$ands = array();
		$join = "
			INNER JOIN ItemUnit AS u
				ON u.UnitGuid=i.UnitGuid
			INNER JOIN Vendor AS v
				ON v.VendorGuid=i.VendorGuid
			INNER JOIN CategoryGroup AS ctg
				ON ctg.CtgGroupGuid=i.CtgGroupGuid
			INNER JOIN CategoryGroupMember AS cgm
				ON cgm.CtgGroupGuid=i.CtgGroupGuid
			INNER JOIN Category AS c
				ON c.CtgGuid=cgm.CtgGuid
			INNER JOIN ServiceCombin AS sc
				ON sc.SrvCmbGuid=i.SrvCmbGuid
			INNER JOIN ServiceCombinMember AS scm
				ON scm.SrvCmbGuid=sc.SrvCmbGuid
			INNER JOIN Service AS s
				ON s.SrvGuid=scm.SrvGuid
			INNER JOIN CategoryStandard AS cs
				ON cs.CtgStdGuid=c.CtgStdGuid
			LEFT JOIN W_ItemExtend AS ie
				ON ie.ItemGuid=i.ItemGuid
		";
		
		if ($where['ServiceName']) {
			$ands[] = $this->db->quoteInto('s.SrvName=N?', $where['ServiceName']);
		}

		if ($where['ItemGuid']) {
			if (is_array($where['ItemGuid'])) {
				$ands[] = $this->db->quoteInto('i.ItemGuid IN (?)', $where['ItemGuid']);
			} else {
				$ands[] = $this->db->quoteInto('i.ItemGuid=?', $where['ItemGuid']);
			}
		}
		
		if (isset($where['ItemGuids']) && is_array($where['ItemGuids']) && count($where['ItemGuids'])>0) {
			$ands[] = $this->db->quoteInto('i.ItemGuid IN (?)', $where['ItemGuids']);
		}
		
		if (isset($where['HasLogo']) && $where['HasLogo']) {
			$ands[] = $this->db->quoteInto('ie.HasLogo=?', 1);
		}
		
		if ($where['ItemName']) {
			$ands[] = $this->db->quoteInto('i.ItemName LIKE N?', '%'.$where['ItemName'].'%');
		}
		
		if ($where['Vendor']) {
			$ands[] = $this->db->quoteInto('v.VendorGuid=?', $where['Vendor']);
		}
		
		if ($where['CtgGroup']) {
			$ands[] = $this->db->quoteInto('i.CtgGroup=?', $where['CtgGroup']);
		}
		
		if ($where['SortGroup']) {
			$ands[] = $this->db->quoteInto('i.SortGroupGuid=?', $where['SortGroup']);
		}
		
		if ($where['Regions'] && is_array($where['Regions']) && count($where['Regions'])>0) {
			$ands[] = "v.RegionGuid IN ('".implode("','", $where['Regions'])."')";
		}
		
		if (isset($where['passby_minimarket'])) {
			$ands[] = $this->db->quoteInto('v.VendorGuid!=?', $cConfig->db->guids->mini_market);
			$ands[] = $this->db->quoteInto('i.ItemName NOT LIKE ?', '%'.$config->db->n->item_name->mifan.'%');
		}
		
		if (!$where['CtgStdName']) {
			$ands[] = $this->db->quoteInto('cs.CtgStdName=N?', $config->db->n->ctg_std_name->item);
		} else {
			$ands[] = $this->db->quoteInto('cs.CtgStdName=N?', $where['CtgStdName']);
		}

		if (isset($where['Disabled'])) {
			$ands[] = $this->db->quoteInto('i.Disabled=?', (int)$where['Disabled']);
			$ands[] = $this->db->quoteInto('v.Disabled=?', (int)$where['Disabled']);
		}
		
		if (isset($where['Vendor_Disabled'])) {
			$ands[] = $this->db->quoteInto('v.Disabled=?', (int)$where['Vendor_Disabled']);
		}
		
		if ($where['CtgGuid']) {
			$ands[] = $this->db->quoteInto('c.CtgGuid=?', $where['CtgGuid']);
		}
		
		if ($where['CtgName']) {
			$ands[] = $this->db->quoteInto('c.CtgName=N?', $where['CtgName']);
		}
		
		if ($where['CityId']) {
			$ands[] = $this->db->quoteInto('i.CityId=?', $where['CityId']);
		}
		
		if (isset($where['IsRec'])) {
			$ands[] = $this->db->quoteInto('ie.IsRec=?', (int)$where['IsRec']);
		}
		
		if ($where['AreaGuid']) {
			$ands[] = "v.AreaGuid IN (".$this->q(implode("','", (array)$where['AreaGuid'])).")";
		}
		
		$sql = "SELECT TOP ".$ends.' '.$selected."
		FROM Item AS i
		";
		$sql .= $join;
		$sql .= "
		WHERE ".implode(' AND ', $ands);
		
		$cSql = "SELECT COUNT(*) AS total
		FROM Item AS i
		";
		$cSql .= $join;
		$cSql .= "
		WHERE ".implode(' AND ', $ands);
		
		$oSql = "";
		if (count($order)>0) {
			$to = array();
		
			foreach ($order as $key=>$val) {
				if ($key=='_random_') {
					$to[] = 'NEWID() ASC';
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

		$sql = 'SELECT * 
			FROM (
				SELECT ROW_NUMBER() OVER (
					'.$oSql.'
				) AS "P_DB_NUMBER", * FROM (
				'.str_replace($oSql, '', $sql).'
				) AS inner_tbl
			) AS outter_tbl
			WHERE "P_DB_NUMBER" BETWEEN '.($pager['skip']+1).' AND '.($ends).'
			'.$oSql.'
		';
		
		$result = $this->all($sql);

		$i = 0;
		foreach ($result as $row) {
			$row['_seq'] = $pager['skip'] + $i + 1;
			$rows[] = $row;
			
			$i++;
		}

		if (!isset($where['passby_pager'])) {
			$tmp = $this->one($cSql);
			$pager['total'] = $tmp['total'];
		}

		return $rows;
	}
	
	
	public function &searchVendor(&$pager, array $where=array(), array $order=array())
	{
		$rows = array();
		$count = $pager['limit'] ? (int)$pager['limit'] : 20;
		$page = $pager['page'] ? (int)$pager['page'] : 1;
		$offset = $pager['skip'] ? (int)$pager['skip'] : 0;
		$ends = $pager['skip']+$pager['limit'];
	
		$config = &Msd_Config::appConfig();
		$cConfig = &Msd_Config::cityConfig();
	
		$selected = "i.Disabled,i.VendorGuid,v.VendorName";
		$ands = array();
		$join = "
			INNER JOIN Vendor AS v
				ON v.VendorGuid=i.VendorGuid
			INNER JOIN CategoryGroupMember AS cgm
				ON cgm.CtgGroupGuid=i.CtgGroupGuid
			INNER JOIN Category AS c
				ON c.CtgGuid=cgm.CtgGuid
			INNER JOIN ServiceCombin AS sc
				ON sc.SrvCmbGuid=i.SrvCmbGuid
			INNER JOIN ServiceCombinMember AS scm
				ON scm.SrvCmbGuid=sc.SrvCmbGuid
			INNER JOIN Service AS s
				ON s.SrvGuid=scm.SrvGuid
			INNER JOIN CategoryStandard AS cs
				ON cs.CtgStdGuid=c.CtgStdGuid
		";
	
		if ($where['ServiceName']) {
			$ands[] = $this->db->quoteInto('s.ServiceName=N?', $where['ServiceName']);
		}
	
		if (isset($where['Disabled'])) {
			$ands[] = $this->db->quoteInto('i.Disabled=?', (int)$where['Disabled']);
			$ands[] = $this->db->quoteInto('v.Disabled=?', (int)$where['Disabled']);
		}
	
    	if ($where['CtgName']) {
			$ands[] = $this->db->quoteInto('c.CtgName=N?', $where['CtgName']);
		}
	
		$sql = "SELECT DISTINCT TOP ".$ends.' '.$selected."
		FROM Item AS i
		";
		$sql .= $join;
		$sql .= "
		WHERE ".implode(' AND ', $ands);
	
		$cSql = "SELECT COUNT(*) AS total
		FROM Item AS i
		";
		$cSql .= $join;
		$cSql .= "
		WHERE ".implode(' AND ', $ands);
	
		$oSql = "";
		if (count($order)>0) {
			$to = array();
	
			foreach ($order as $key=>$val) {
				if ($key=='_random_') {
					$to[] = 'NEWID() ASC';
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
	
		$sql = 'SELECT *
			FROM (
				SELECT ROW_NUMBER() OVER (
					'.$oSql.'
				) AS "P_DB_NUMBER", * FROM (
				'.str_replace($oSql, '', $sql).'
				) AS inner_tbl
			) AS outter_tbl
			WHERE "P_DB_NUMBER" BETWEEN '.($pager['skip']+1).' AND '.($ends).'
			'.$oSql.'
		';
	
		$result = $this->all($sql);
	
		$i = 0;
		foreach ($result as $row) {
			$row['_seq'] = $pager['skip'] + $i + 1;
			$rows[] = $row;
				
			$i++;
		}
	
		if (!isset($where['passby_pager'])) {
			$tmp = $this->one($cSql);
			$pager['total'] = $tmp['total'];
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
