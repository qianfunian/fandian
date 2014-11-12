<?php

class Msd_Article_Category
{
	protected static $instance = array();
	protected $CategoryId = 0;
	protected $data = array();
	
	private function __construct($CategoryId)
	{
		$this->CategoryId = (int)$CategoryId;
		if ($this->CategoryId) {
			$table = &Msd_Dao::table('article/category', 'web');
			$this->data = $table->get($this->CategoryId);
		} else {
			$this->data = array(
					'CategoryId' => 0,
					'CategoryName' => '',
					'OrderNo' => '9999'
					);
		}
	}
	
	private function _init()
	{
		$this->CategoryId = 0;
		$this->data = array(
				'CategoryId' => 0,
				'CategoryName' => '',
				'OrderNo' => '9999'
				);
	}
	
	public static function &getInstance($CategoryId)
	{
		if (!isset(self::$instance[$CategoryId])) {
			self::$instance[$CategoryId] = new self($CategoryId);
		}	
		
		return self::$instance[$CategoryId];
	}
	
	public static function &search(array $params=array())
	{
		$rows = array();

		$i = 0;
		$tmp = &Msd_Dao::table('article/category')->fetchAll(array(
				'order' => array(
						'OrderNo' => 'ASC'
						)
				));
		foreach ($tmp as $row) {
			$row['_seq'] = $i++;
			
			$rows[] = $row;
		}
		
		return $rows;
	}
	
	public function delete()
	{
		$table = &Msd_Dao::table('article/category', 'web');
		$table->doDelete($this->CategoryId);
		
		$this->_init();
	}
	
	public function &data()
	{
		return $this->data;
	}
}