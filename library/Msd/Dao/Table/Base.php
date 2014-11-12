<?php

require_once 'Zend/Db/Table.php';

abstract class Msd_Dao_Table_Base extends Zend_Db_Table
{
	protected $prefix = '';
	protected $db = null;
	protected $dbKey = '';
	protected $leftKeyString = '`';
	protected $rightKeyString = '`';
	protected $offset = 0;
	protected $count = 20;
	protected $_orderKey = '';
	protected $_primaryIsGuid = false;
	protected $_realPrimary = '';
	protected $nullKeys = array();
	protected $compatInsert = false;
	
	public function __construct()
	{
		parent::__construct();
	}
	
	protected function _setup()
	{
		$this->db = &Msd_Dao::assignDbConnection($this->dbKey);
		$this->_setAdapter($this->db);
		
		parent::_setup();
	}
	
	public function name()
	{
		return $this->_name instanceof Zend_Db_Expr ? $this->_name : $this->expr($this->_name);
	}
	
	public function &__call($method, $params)
	{
		$result = false;

		if (preg_match('/^([a-zA-Z0-9]+)$/is', $method)) {
			$result = call_user_func(array(
					&$this,
					'get'
					), $params[0], $method);
		}

		Msd_Log::getInstance()->dbcall(get_class($this).':'.$method);
		
		return $result;
	}
	
	public function fetchAll($where='', $order='', $count=-1, $offset=-1)
	{
		$count<0 && $count = $this->count;
		$offset<0 && $offset = $this->offset;
		
		if (is_array($where) && count($where)>0) {
			$p = $where;
			$order = '';
			if (isset($p['order']) && is_array($p['order']) && count($p['order'])) {
				$tmp = array();
				foreach ($p['order'] as $key=>$val) {
					$tmp[] = $this->db->quoteInto($key.' '.$val, '');
				}
				$order = implode(',', $tmp);
				unset($p['order']);
			}
			
			$where = '';
			foreach ($p as $key=>$val) {
				$where .= $this->db->quoteInto($key.'=?', $val);
			}
		}
		
		$where || $where = null;
		$order || $order = null;
		
		Msd_Dao::$rQueries++;
		
		return parent::fetchAll($where, $order, $count, $offset);
	}
	
	/**
	 * 获取DAO Class所配置的主键
	 * @NOTE Zend Framework貌似有个bug，其_primary并非总是一个字符串，有可能也是数组，这不是我们期望的
	 * 
	 * @return string
	 */
	public function primary()
	{
		if (!$this->_realPrimary) {
			if (is_array($this->_primary)) {
				$this->_realPrimary = array_pop($this->_primary);
			} else {
				$this->_realPrimary = $this->_primary;
			}
		}
		
		return $this->_realPrimary;
	}
	
	public function &get($val, $key=null)
	{
		$row = array();
		
		try {
			$key || $key = $this->primary();
			if (preg_match('/^([a-zA-Z0-9]+)$/', $key)) {
				$select = $this->db->select();
				$select->from($this->expr($this->sn(null)));
				$select->where($key.'=?', $val);
				$select->limit(1);
		
				$row = $this->one($select);
			}
		} catch (Exception $e) {
			$row = array();

			Msd_Log::getInstance()->database($e->getMessage()."\n".$e->getTraceAsString());
		}
		
		return $row;
	}
	
	/**
	 * 尝试通过缓存获取数据，失败后再尝试真实数据库读取
	 * 
	 * @param unknown $val
	 * @param string $key
	 * @return Ambigous <multitype:, unknown>
	 */
	public function &cget($val, $key=null)
	{
		$row = array();
		$key || $key = $this->primary();
		
		if (preg_match('/^([a-zA-Z0-9]+)$/', $key)) {
			$ckey = $this->dbKey.'_'.$this->_name.'_'.$key.'_'.md5($val);
			$cacher = &Msd_Cache_Remote::getInstance();
			$row = $cacher->get($ckey);
			
			if (!$row) {
				$row = $this->get($val, $key);
				$cacher->set($ckey, $row);
			}
		}
		
		return $row;
	}
	
	/**
	 * 插入数据
	 * 
	 * @param array $params
	 */
	public function insert(array $params)
	{
		//	先把一些不允许NULL的字段做一下初始化
		if (count($this->nullKeys)>0) {
			foreach ($params as $key=>$value) {
				if (in_array($key, $this->nullKeys) && trim($value)=='') {
					$params[$key] = $this->expr('NULL');
				}
			}
		}
		
		Msd_Dao::$wQueries++;

		if ($this->compatInsert) {
			$keys = array();
			$values = array();
			foreach ($params as $key=>$value) {
				$keys[] = "[".$key."]";
				$values[] = $value instanceof Zend_Db_Expr ? $value->__toString() : $this->q((string)$value);
			}
			
			$sql = "INSERT INTO [".$this->_name."] (".implode(',', $keys).") VALUES (".implode(',', $values).")";
			$rs = $this->db->query($sql);
		} else {
			$rs = parent::insert($params);
		}			

		return $rs;
	}
	
	/**
	 * 根据主键删除数据
	 * 
	 * @param unknown $val
	 */
	public function doDelete($val)
	{
		$where = $this->db->quoteInto($this->primary().'=?', $this->_primaryIsGuid ? $this->wrapGuid($val) : $val);
		Msd_Dao::$wQueries++;
		
		return $this->delete($where);
	}
	
	public function getPrimaryFromRow($data)
	{
		return isset($data[$this->primary()]) ? $data[$this->primary()] : null;
	}
	
	public function doUpdate(array $params, $keyVal)
	{
		if (count($this->nullKeys)>0) {
			foreach ($params as $key=>$value) {
				if (in_array($key, $this->nullKeys) && trim($value)=='') {
					$params[$key] = $this->expr('NULL');
				}
			}
		}
				
		$where = $this->db->quoteInto($this->primary().'=?', $this->_primaryIsGuid ? $this->wrapGuid($keyVal) : $keyVal);
		Msd_Dao::$wQueries++;
		
		return $this->update($params, $where);
	}
	
	/**
	 * 模拟MySQL的Replace
	 * 
	 * @param array $data
	 * @param string $_where
	 * @return number
	 */
	public function replace(array $data, $_where='')
	{
		$this->db->beginTransaction();
		$count = 0;
		
		try {
			$where = '';
			
			if (is_array($_where) && count($_where)>0) {
				$tmp = array();
				foreach ($_where as $key=>$val) {
					$tmp[] = $this->db->quoteInto($key.' = ?', $val);
				}
				$where = implode(' AND ', $tmp);
			} else if ($_where) {
				$where = $_where;
			}
			
			$count = (int)$this->db->update($this->name(), $data, $where);
			Msd_Dao::$wQueries++;
			
			if ($count==0) {
				$count = $this->db->insert($this->name(), $data);
				Msd_Dao::$wQueries++;
			}
			
			$this->db->commit();
		} catch (Exception $e) {
			$this->db->rollBack();
			Msd_Log::getInstance()->sql($e->getMessage()."\n".$e->getTraceAsString());
		}

		return $count;
	}
	
	public function increase($key, $priVal, $step=1)
	{
		$sql = $this->db->quoteInto("UPDATE ".$this->wrap($this->_name)." SET ".$this->wrap($key)."=".$this->wrap($key)."+".((int)$step)." WHERE ".$this->wrap($this->primary())."=?", $priVal);
		Msd_Dao_Debug::$sql[] = $sql;
		Msd_Dao::$wQueries++;
		
		return $this->db->query($sql);
	}
	
	public function decrease($key, $priVal, $step=1)
	{
		$sql = $this->db->quoteInto("UPDATE ".$this->wrap($this->_name)." SET ".$this->wrap($key)."=".$this->wrap($key)."-".((int)$step)." WHERE ".$this->wrap($this->primary())."=?", $priVal);
		Msd_Dao_Debug::$sql[] = $sql;
		Msd_Dao::$wQueries++;
		
		return $this->db->query($sql);
	}
	
	public function query($sql)
	{
		
		return $this->db->fetchAll($sql);
	}
	public function q($string)
	{
		return $this->db->quote($string);
	}
	
	public function wrap($string)
	{
		return $this->_wrap($string);
	}
	
	protected function _wrap($string)
	{
		return $this->leftKeyString.$string.$this->rightKeyString;
	}
	
	public function transaction()
	{
		return new Msd_Dao_Transaction($this->db);
	}
	
	public function wrapGuid($guid)
	{
		return $this->expr("CAST('".$guid."' AS UNIQUEIDENTIFIER)");
	}
	
	public function wrapPoint($longitude, $latitude)
	{
		return $this->expr("[dbo].Fn_WrapPoint(".$longitude.",".$latitude.")");
	}
	
	public function expr($string)
	{
		return new Zend_Db_Expr($string);
	}
	
	protected function &s()
	{
		return new Msd_Db_Select($this->db);
	}
	
	public function sn($as='')
	{
		return $this->leftKeyString.Msd_Dao::dbName($this->dbKey).$this->rightKeyString.'.'.$this->leftKeyString.'dbo'.$this->rightKeyString.'.'.$this->leftKeyString.$this->_name.$this->rightKeyString.($as!=null ? ($as!='' ?  ' AS '.$as : ' AS '.$this->_name) : '');
	}
	
	protected function &t($n)
	{
		return Msd_Dao::table($n);
	}
	
	public function genGuid()
	{
		$select = 'SELECT NEWID()';
		$row = $this->one($select);
		
		Msd_Dao::$rQueries++;
		
		return array_pop($row);
	}
	
	public function &one($select)
	{
		if ($select instanceof Zend_Db_Select) {
			Msd_Dao_Debug::$sql[] = $select->__toString();
		} else {
			Msd_Dao_Debug::$sql[] = $select;
		}
		
		Msd_Dao::$rQueries++;
		
		$row = $this->db->fetchRow($select);
		
		return $row ? $row : array();
	}
	
	public function &all($select=null)
	{
		if ($select) {
			if ($select instanceof Zend_Db_Select) {
				Msd_Dao_Debug::$sql[] = $select->__toString();
			} else {
				Msd_Dao_Debug::$sql[] = $select;
			}
		} else {
			$select = &$this->s();
			$select->from($this->_name);

			Msd_Dao_Debug::$sql[] = $select->__toString();
		}

		try {
			$rows = $this->db->fetchAll($select);
		} catch (Exception $e) {
			Msd_Log::getInstance()->database($select."\nMessage: ".$e->getMessage()."\nTrace:".$e->getTraceAsString());
		}
		
		Msd_Dao::$rQueries++;
		
		return $rows ? $rows : array();		
	}
	
	public function lastInsertId()
	{
		return $this->db->lastInsertId();
	}
}
