<?php

class Msd_Dao_Table_Server_Coordinate extends Msd_Dao_Table_Server
{
	protected static $instance = null;
	
	public function __construct()
	{
		parent::__construct();
		
		$this->_name = $this->prefix.'Coordinate';
		$this->_primary = 'CoordGuid';
	}
	
	public static function &getInstance()
	{
		if (self::$instance==null) {
			self::$instance = new self();
		}

		return self::$instance;
	}
	
	public function &searchAddress($address)
	{
		$select = &$this->s();
		$select->from($this->sn('c'));
		$select->where('CoordName LIKE ?', $address.'%');
		$select->limit(1);

		return $this->one($select);
	}
	
	public function &nearestWithRegions($longitude, $latitude, array $Regions)
	{
		$select->from($this->sn('c'), array(
				'c.CoordName', 'c.CoordType', 'c.Longitude', 'c.Latitude',
				$this->expr('[dbo].Fn_GetDistance(c.CoordValue, '.$longitude.','.$latitude.') AS Distance')
		));
		$select->where('c.Disabled=?', '0');
		$select->where('c.RegionGuid IN (?)', $Regions);
		$select->where($this->expr('[dbo].Fn_GetDistance(c.CoordValue, '.$longitude.','.$latitude.')<=800'));
		$select->order('Distance ASC');
		$select->limit(1);
	
		return $this->one($select);
	}	

	public function &nearestWithCity($longitude, $latitude, $CityId)
	{
		$select->from($this->sn('c'), array(
				'c.CoordName', 'c.CoordType', 'c.Longitude', 'c.Latitude', 
				$this->expr('[dbo].Fn_GetDistance(c.CoordValue, '.$longitude.','.$latitude.') AS Distance')
				));
		$select->where('c.Disabled=?', '0');
		$select->where('c.CityId=?', $CityId);
		$select->order('Distance ASC');
		$select->limit(1);
		
		return $this->one($select);
	}
	
	public function &nearest($longitude, $latitude)
	{
		$select = &$this->s();
		$select->from($this->sn('c'), array(
				'c.CoordName', 'c.CoordType', 'c.Longitude', 'c.Latitude', 
				$this->expr('[dbo].Fn_GetDistance(c.CoordValue, '.$longitude.','.$latitude.') AS Distance')
				));
		$select->where('c.Disabled=?', '0');
		$select->order('Distance ASC');
		$select->limit(1);
		
		return $this->one($select);
	}

	public function &nearbyWithCity($longitude, $latitude, $CityId, $limit=18)
	{
		$bTable = &$this->t('coordforbaidu');
	
		$select = &$this->s();
		$select->from($this->sn('c'), array(
				'c.CoordGuid', 'c.CoordName', 'c.CoordType', 'c.Longitude', 'c.Latitude',
				$this->expr('[dbo].Fn_GetDistance(c.CoordValue, '.$longitude.','.$latitude.') AS Distance')
		));
	
		$select->where('c.Disabled=?', '0');
		$select->where('c.CityId=?', $CityId);
		$select->order('Distance ASC');
		$select->limit($limit);
	
		return $this->all($select);
	}

	public function &nearbyWithRegions($longitude, $latitude, array $Regions, $limit=18)
	{
		$bTable = &$this->t('coordforbaidu');
	
		$select = &$this->s();
		$select->from($this->sn('c'), array(
				'c.CoordGuid', 'c.CoordName', 'c.CoordType', 'c.Longitude', 'c.Latitude',
				$this->expr('[dbo].Fn_GetDistance(c.CoordValue, '.$longitude.','.$latitude.') AS Distance')
		));
	
		$select->where('c.Disabled=?', '0');
		$select->where('c.RegionGuid IN (?)', $Regions);
		$select->where($this->expr('[dbo].Fn_GetDistance(c.CoordValue, '.$longitude.','.$latitude.')<=800'));
		$select->order('Distance ASC');
		$select->limit($limit);

		return $this->all($select);
	}
	
	public function &nearby($longitude, $latitude, $limit=18)
	{
		$bTable = &$this->t('coordforbaidu');
		
		$select = &$this->s();
		$select->from($this->sn('c'), array(
			'c.CoordGuid', 'c.CoordName', 'c.CoordType', 'c.Longitude', 'c.Latitude',
			$this->expr('[dbo].Fn_GetDistance(c.CoordValue, '.$longitude.','.$latitude.') AS Distance')
			));
		
		$select->where('c.Disabled=?', '0');
		$select->order('Distance ASC');
		$select->limit($limit);

		return $this->all($select);
	}
	
	public function &search(&$pager, array $where=array(), array $order=array())
	{
		$rows = array();
		$count = $pager['limit'] ? (int)$pager['limit'] : 20;
		$page = $pager['page'] ? (int)$pager['page'] : 1;
		$offset = $pager['offset'] ? (int)$pager['offset'] : 0;
		
		$config = &Msd_Config::appConfig();
		
		$rTable = &$this->t('region');
		$bTable = &$this->t('coordforbaidu');
		
		$select = &$this->s();
		$cSelect = &$this->s();
		
		$select->from($this->sn('c'));
		$cSelect->from($this->sn('c'), 'COUNT(*) AS total');
		
		$select->joinleft($rTable->sn('r'), 'r.RegionGuid=c.RegionGuid', 'r.RegionName');
		$cSelect->joinleft($rTable->sn('r'), 'r.RegionGuid=c.RegionGuid', '');
		
		$select->joinleft($bTable->sn('b'), 'b.CoordGuid=c.CoordGuid', array(
			'b.Latitude AS Baidu_Latitude', 'b.Longitude AS Baidu_Longitude'	
			));
		$cSelect->joinleft($bTable->sn('b'), 'b.CoordGuid=c.CoordGuid', '');
		
		if ($where['CoordName']) {
			$select->where('c.CoordName LIKE ?', '%'.$where['CoordName'].'%');
			$cSelect->where('c.CoordName LIKE ?', '%'.$where['CoordName'].'%');
		}
		
		if ($where['RegionName']) {
			$select->where('r.RegionName LIKE ?', '%'.$where['RegionName'].'%');
			$cSelect->where('r.RegionName LIKE ?', '%'.$where['RegionName'].'%');
		}
		
		if ($where['Region']) {
			$select->where('c.RegionGuid=?', $where['Region']);
			$cSelect->where('c.RegionGuid=?', $where['Region']);
		}
		
		if (count($order)>0) {
			foreach ($order as $key=>$val) {
				$select->order($key.' '.$val);
			}
		} else {
			$select->order('c.'.$this->_orderKey.' DESC');
		}		
		
		$select->limitPage($page, $count);

		$result = $this->all($select);
		$i = 0;

		$offset = $count*($page-1);
		foreach ($result as $row) {
			$row['_seq'] = $offset + 1 + ($i++);
			$rows[] = $row;
		}

		$tmp = $this->one($cSelect);
		$pager['total'] = $tmp['total'];
		
		return $rows;
	}
	
	public function &fetchAll($regionGuid)
	{
		$select = &$this->s();
		$select->from($this->sn('c'),array('CoordGuid','CoordName','InputCode','Longitude','Latitude'));
		$select->where('c.CityID=?', $regionGuid);
		$select->where('c.Audited=?', 1);
		return $this->all($select);
	
	}
}