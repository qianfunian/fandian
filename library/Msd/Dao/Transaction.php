<?php

/**
 * DAO 数据库事务
 * 
 * @author pang
 *
 */
class Msd_Dao_Transaction extends Msd_Dao_Base
{
	protected $db = null;
	
	public function __construct(&$db)
	{
		$this->db = &$db;	
	}
	
	public function start()
	{
		return $this->db->beginTransaction();
	}
	
	public function commit()
	{
		return $this->db->commit();
	}
	
	public function rollback()
	{
		return $this->db->rollBack();
	}
}