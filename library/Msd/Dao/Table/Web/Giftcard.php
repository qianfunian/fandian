<?php
class Msd_Dao_Table_Web_Giftcard extends Msd_Dao_Table_Web
{
	protected static $instance = null;

	public function __construct()
	{
		parent::__construct();

		$this->_name = $this->prefix.'Giftcard';
		$this->_primary = 'GiftId';
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
		$params['AddTime'] || $params['AddTime'] = $this->expr('GETDATE()');
		//$params['GiftId'] = isset($params['GiftId']) ? $params['GiftId'] : 0;
		$params['Value'] = isset($params['Value']) ? $params['Value'] : '';
		return parent::insert($params);
	}

	public function doUpdate(array $params, $keyVal)
	{
		return parent::doUpdate($params, $keyVal);
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