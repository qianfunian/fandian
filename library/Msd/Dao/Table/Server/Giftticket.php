<?php
class Msd_Dao_Table_Server_Giftticket extends Msd_Dao_Table_Server {
	protected static $instance = null;
	public function __construct() {
		parent::__construct ();
		
		$this->_name = $this->prefix . 'FT_GiftTicket';
		$this->_primary = 'Guid';
		$this->_orderKey = 'Guid';
		$this->_realPrimary = 'Guid';
		$this->_primaryIsGuid = false;
	}
	public static function &getInstance() {
		if (self::$instance == null) {
			self::$instance = new self ();
		}
		
		return self::$instance;
	}
	public function verify($giftId) {
		$sql = "select top 1 ft.UsedState,ft.UsedTime,gt.TName  from FT_GiftTicket ft left join FT_GiftTicketType gt
 on ft.TGuid = gt.TGuid  where MD5Number = " . $this->q ( $giftId );
		return $this->all ( $sql );
	}
}
