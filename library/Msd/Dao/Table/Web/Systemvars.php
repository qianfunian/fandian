<?php

class Msd_Dao_Table_Web_Systemvars extends Msd_Dao_Table_Web
{
	protected static $instance = null;
	
	public function __construct()
	{
		parent::__construct();
		
		$this->_name = $this->prefix.'SystemVars';
		$this->_primary = 'DataKey';
	}
	
	public static function &getInstance()
	{
		if (self::$instance==null) {
			self::$instance = new self();
		}
		
		return self::$instance;
	}
	
	public function getByRegion($key, $RegionGuid)
	{
		$select = &$this->s();
		$select->from($this->sn('s'));
		$select->where('RegionGuid=?', $RegionGuid);
		$select->where('DataKey=?', $key);
		$select->limit(1);
		
		return $this->one($select);
	}

	public function &all($RootRegion)
	{
		$rows = array();
		
		$select = &$this->s();
		$select->from($this->sn('s'));
		$select->where('RegionGuid=?', $RootRegion);

		$rows = parent::all($select);
		return $rows;
	}
	
	public function insert(array $params)
	{
		$params['LastUpdate'] = $this->expr('GETDATE()');
		
		return parent::insert($params);
	}
	
	public function doUpdate(array $params, $keyVal)
	{
		$params['LastUpdate'] = $this->expr('GETDATE()');
		
		return parent::doUpdate($params, $keyVal);
	}
	
	public function updateRVars(array $params, $keyVal, $RegionGuid)
	{
        $var = $this->getByRegion($keyVal, $RegionGuid);
        if (empty($var)) {
            $params['LastUpdate'] = $this->expr('GETDATE()');
            $params['DataKey'] = $keyVal;
            $params['RegionGuid'] = $RegionGuid;
            return $this->insert($params);
        } else {
            $params['LastUpdate'] = $this->expr('GETDATE()');

            $where = $this->db->quoteInto($this->_primary . '=?', $keyVal);
            $where .= ' AND ' . $this->db->quoteInto('RegionGuid=?', $RegionGuid);

            return $this->update($params, $where);
        }
	}
}