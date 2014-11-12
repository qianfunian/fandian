<?php

class Msd_Dao_Table_Web_Credit extends Msd_Dao_Table_Web_Article
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
		$attachTable = &Msd_Dao::table('attachment');
		
		$select = $this->s();
		$cSelect = $this->s();

		$select->from($this->sn('a'), 'a.*');
		$cSelect->from($this->sn('a'), 'COUNT(*) AS total');
		
		$select->join($extTable->sn('e'), 'e.ArticleId=a.ArticleId', array(
			'e.Credit',	'e.Remains', 'e.Total', 'e.Category'
			));
		$cSelect->join($extTable->sn('e'), 'e.ArticleId=a.ArticleId', '');

		$select->joinleft($attachTable->sn('at'), 'at.FileId=a.FirstAttach', array(
				'at.MimeType',
				'at.Name AS FileName'
				));
		
		$cSelect->joinleft($attachTable->sn('at'), 'at.FileId=a.FirstAttach', '');
	
		if ($where['CategoryName']) {
			$select->where('c.CategoryName LIKE ?', '%'.$where['CategoryName'].'%');
			$cSelect->where('c.CategoryName LIKE ?', '%'.$where['CategoryName'].'%');
		}
		
		if ($where['Title']) {
			$select->where('a.Title LIKE ?', '%'.$where['Title'].'%');
			$cSelect->where('a.Title LIKE ?', '%'.$where['Title'].'%');
		}
		
		$select->where('a.CategoryId=?', $CategoryId);
		$cSelect->where('a.CategoryId=?', $CategoryId);
		
		if (isset($where['PubFlag'])) {
			$select->where('a.PubFlag=?', (int)$where['PubFlag'] ? '1' : '0');
			$cSelect->where('a.PubFlag=?', (int)$where['PubFlag'] ? '1' : '0');
		}
		
		if (trim($where['Detail'])!='') {
			$select->where('a.Detail LIKE ?', '%'.$where['Detail'].'%');
			$cSelect->where('a.Detail LIKE ?', '%'.$where['Detail'].'%');
		}
		
		if (trim($where['Category'])) {
			$select->where('e.Category=?', $where['Category']);
			$cSelect->where('e.Category=?', $where['Category']);
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
}