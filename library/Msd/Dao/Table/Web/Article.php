<?php

class Msd_Dao_Table_Web_Article extends Msd_Dao_Table_Web
{
	protected static $instance = null;
	
	public function __construct()
	{
		parent::__construct();
		
		$this->_name = $this->prefix.'Article';
		$this->_primary = 'ArticleId';
		
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
		if (!isset($params['PubTime'])) {
			$params['PubTime'] = $this->expr('GETDATE()');
		}
		
		$params['StartTime'] = $this->expr('GETDATE()');
		$params['EndTime'] = $this->expr('GETDATE()');
		
		return parent::insert($params);
	}	
	
	public function &search(&$pager, array $where=array(), array $order=array())
	{
		$rows = array();
		$count = $pager['limit'] ? (int)$pager['limit'] : 20;
		$page = $pager['page'] ? (int)$pager['page'] : 1;
		$offset = $pager['offset'] ? (int)$pager['offset'] : 0;

		$categoryTable = &Msd_Dao::table('article/category');
		$attachTable = &Msd_Dao::table('attachment');
		$rTable = &Msd_Dao::table('region');
		
		$select = $this->s();
		$cSelect = $this->s();

		$select->from($this->sn('a'), 'a.*');
		$cSelect->from($this->sn('a'), 'COUNT(*) AS total');

		$select->join($categoryTable->sn('c'), 'a.CategoryId=c.CategoryId', 'c.CategoryName');
		$select->joinleft($attachTable->sn('at'), 'at.FileId=a.FirstAttach', array(
				'at.MimeType',
				'at.Name AS FileName'
				));
		$select->joinleft($rTable->sn('r'), 'r.RegionGuid=a.RegionGuid', array(
			'r.RegionName'	
			));
		
		$cSelect->joinleft($attachTable->sn('at'), 'at.FileId=a.FirstAttach', '');
		$cSelect->join($categoryTable->sn('c'), 'a.CategoryId=c.CategoryId','');
		$cSelect->joinleft($rTable->sn('r'), 'r.RegionGuid=a.RegionGuid', '');
	
		if ($where['CategoryName']) {
			$select->where('c.CategoryName LIKE ?', '%'.$where['CategoryName'].'%');
			$cSelect->where('c.CategoryName LIKE ?', '%'.$where['CategoryName'].'%');
		}
		
		if ($where['Regions'] && is_array($where['Regions']) && count($where['Regions'])>0) {
			$select->where('a.RegionGuid IN (?)', $where['Regions']);
			$cSelect->where('a.RegionGuid IN (?)', $where['Regions']);
		}
		
		if ($where['Title']) {
			$select->where('a.Title LIKE ?', '%'.$where['Title'].'%');
			$cSelect->where('a.Title LIKE ?', '%'.$where['Title'].'%');
		}
		
		if ($where['CategoryId']) {
			if (is_array($where['CategoryId']) && count($where['CategoryId'])) {
				$select->where('a.CategoryId IN (?)', $where['CategoryId']);
				$cSelect->where('a.CategoryId IN (?)', $where['CategoryId']);
			}
		}
		
		if (isset($where['PubFlag'])) {
			$select->where('a.PubFlag=?', (int)$where['PubFlag'] ? '1' : '0');
			$cSelect->where('a.PubFlag=?', (int)$where['PubFlag'] ? '1' : '0');
		}
		
		if (trim($where['Detail'])!='') {
			$select->where('a.Detail LIKE ?', '%'.$where['Detail'].'%');
			$cSelect->where('a.Detail LIKE ?', '%'.$where['Detail'].'%');
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
	
		if (!isset($where['passby_pager'])) {
			$tmp = $this->one($cSelect);
			$pager['total'] = $tmp['total'];
		}

		return $rows;
	}	
}