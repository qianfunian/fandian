<?php

class Msd_Dao_Table_Web_Resetpasswordhash extends Msd_Dao_Table_Web
{
	protected static $instance = null;
	
	public function __construct()
	{
		parent::__construct();
		
		$this->_name = $this->prefix.'ResetPasswordHash';
		$this->_primary = 'Hash';
	}
	
	public static function &getInstance()
	{
		if (self::$instance==null) {
			self::$instance = new self();
		}

		return self::$instance;
	}
	
	public function deleteForMember($CustGuid)
	{
		$where = $this->db->quoteInto('CustGuid=?', $this->wrapGuid($CustGuid));
		return $this->delete($where);
	}

	public function insert(array $params)
	{
		$params['CreateTime'] = $this->expr('GETDATE()');
		$params['CustGuid'] = $this->wrapGuid($params['CustGuid']);
		
		return parent::insert($params);
	}
}