<?php

class Msd_Dao_Table_Web_Addressbook extends Msd_Dao_Table_Web
{
	protected static $instance = null;
	
	public function __construct()
	{
		parent::__construct();
		
		$this->_name = $this->prefix.'Addressbook';
		$this->_primary = 'ABGuid';
		$this->_primaryIsGuid = true;
	}
	
	public static function &getInstance()
	{
		if (self::$instance==null) {
			self::$instance = new self();
		}
		
		return self::$instance;
	}
	
	public function &getDefaultForMember($CustGuid)
	{
		$row = array();
		
		$select = $this->s();
		$select->from($this->name());
		$select->where('CustGuid=?', $CustGuid);
		$select->where('IsDefault=?', '1');
		$select->limit(1);
		
		$row = $this->one($select);
		
		return $row;
	}
	
	public function &getForMember($id, $CustGuid)
	{
		$row = array();
		
		$select = $this->db->select();
		$select->from($this->name());
		$select->where($this->primary().'=?', $id);
		$select->where('CustGuid=?', $CustGuid);
		$select->limit(1);
		
		$row = $this->one($select);
		
		return $row;
	}
	
	public function &updateForMember(array $params, $id, $CustGuid)
	{
		$where = $this->db->quoteInto($this->primary().'=?', $id);
		$where .= ' AND '.$this->db->quoteInto('CustGuid=?', $CustGuid);
	
		return $this->update($params, $where);		
	}
	
	public function deleteFormMember($id, $CustGuid)
	{
		$where = $this->db->quoteInto($this->primary().'=?', $id);
		$where .= ' AND '.$this->db->quoteInto('CustGuid=?', $CustGuid);
		
		return $this->delete($where);		
	}
	
	public function &insertForMember(array $params, $CustGuid)
	{
		$params['CustGuid'] = $this->wrapGuid($CustGuid);
		
		return $this->insert($params);
	}
	
	public function insert(array $params)
	{
		$params['CreateTime'] = $this->expr('GETDATE()');
		$params['ABGuid'] || $params['ABGuid'] = $this->expr('NEWID()');
		
		parent::insert($params);
		
		return $params['ABGuid'];
	}	
	
	public function &search(&$pager, array $where=array(), array $order=array())
	{
		$rows = array();
		$count = $pager['limit'] ? (int)$pager['limit'] : 20;
		$page = $pager['page'] ? (int)$pager['page'] : 1;
		$offset = $pager['offset'] ? (int)$pager['offset'] : 0;

		$select = $this->s();
		$cSelect = $this->s();
	
		$select->from($this->sn('a'), 'a.*');
		$cSelect->from($this->sn('a'), 'COUNT(*) AS total');

		if ($where['CustGuid']) {
			$select->where('CustGuid=?', $where['CustGuid']);
			$cSelect->where('CustGuid=?', $where['CustGuid']);
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
	
	public function resetDefault($id, $CustGuid)
	{
		if ($id) {
			$where = $this->db->quoteInto('CustGuid=?', $CustGuid);
			$this->update(array(
					'IsDefault' => '0'
					), $where);
			
			$where = $this->db->quoteInto($this->primary().'=?', $id);
			$this->update(array(
					'IsDefault' => '1'
					), $where);
		}
	}
}