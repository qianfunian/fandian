<?php

class Msd_Dao_Table_Web_Creditlogs extends Msd_Dao_Table_Web
{
	protected static $instance = null;
	
	public function __construct()
	{
		parent::__construct();
		
		$this->_name = $this->prefix.'Creditlogs';
		$this->_primary = 'AutoId';
		
	}
	
	public static function &getInstance()
	{
		if (self::$instance==null) {
			self::$instance = new self();
		}
		
		return self::$instance;
	}

	
	public function &search(&$pager, array $where=array(), array $order=array())
	{
		$config = &Msd_Config::appConfig();
		$cConfig = &Msd_Config::cityConfig();
		
		$CategoryId = $cConfig->db->article->category->credit;
		$rows = array();
		$count = $pager['limit'] ? (int)$pager['limit'] : 20;
		$page = $pager['page'] ? (int)$pager['page'] : 1;
		$offset = $pager['offset'] ? (int)$pager['offset'] : 0;

		$extTable = &Msd_Dao::table('article/credit');
		$aTable = &Msd_Dao::table('article');
		$attachTable = &Msd_Dao::table('attachment');
		
		$select = $this->s();
		$cSelect = $this->s();

		$select->from($this->sn('l'), 'l.*');
		$cSelect->from($this->sn('l'), 'COUNT(*) AS total');
		
		$select->joinleft($aTable->sn('a'), 'a.ArticleId=l.ArticleId', array(
			'a.Title'	
			));
		$cSelect->joinleft($aTable->sn('a'), 'a.ArticleId=l.ArticleId', '');
		
		$select->join($extTable->sn('e'), 'e.ArticleId=a.ArticleId', array(
			'e.Credit',	
			));
		$cSelect->join($extTable->sn('e'), 'e.ArticleId=a.ArticleId', '');

		$select->joinleft($attachTable->sn('at'), 'at.FileId=a.FirstAttach', array(
				'at.MimeType',
				'at.Name AS FileName'
				));
		$cSelect->joinleft($attachTable->sn('at'), 'at.FileId=a.FirstAttach', '');

		if ($where['Title']) {
			$select->where('a.Title LIKE ?', '%'.$where['Title'].'%');
			$cSelect->where('a.Title LIKE ?', '%'.$where['Title'].'%');
		}
		
		$select->where('a.CategoryId=?', $CategoryId);
		$cSelect->where('a.CategoryId=?', $CategoryId);
		
		if (isset($where['ActFlag'])) {
			$select->where('a.ActFlag=?', (int)$where['ActFlag'] ? '1' : '0');
			$cSelect->where('a.ActFlag=?', (int)$where['ActFlag'] ? '1' : '0');
		}
		
		if ($where['Contactor']) {
			$select->where('l.Contactor=?', $where['Contactor']);
			$cSelect->where('l.Contactor=?', $where['Contactor']);
		}
		
		if ($where['Address']) {
			$select->where('l.Address=?', $where['Address']);
			$cSelect->where('l.Address=?', $where['Address']);
		}
		
		if ($where['CellPhone']) {
			$select->where('l.CellPhone=?', $where['CellPhone']);
			$cSelect->where('l.CellPhone=?', $where['CellPhone']);
		}

		if (count($order)>0) {
			foreach ($order as $key=>$val) {
				$select->order($key.' '.$val);
			}
		} else {
			$select->order('l.'.$this->primary().' DESC');
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

	public function insert(array $params)
	{
		$params['CreateTime'] = $this->expr('GETDATE()');
		
		return parent::insert($params);
	}
}