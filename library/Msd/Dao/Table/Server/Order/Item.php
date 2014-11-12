<?php

class Msd_Dao_Table_Server_Order_Item extends Msd_Dao_Table_Server_Order_Base
{
	protected static $instance = null;
	
	public function __construct()
	{
		parent::__construct();
		
		$this->_name = $this->prefix.'OrderItem';
		$this->_primary = 'OrdItemGuid';
		
		$this->nullKeys = array(
				'ItemId', 'ItemReq', 'Remark', 'StatusId', 'CancelGuid'
				);
	}
	
	public static function &getInstance()
	{
		if (self::$instance==null) {
			self::$instance = new self();
		}
		
		return self::$instance;
	}
	
	public function &getOIVItems($OIVGuid)
	{
		$rows = array();
		
		$oiviTable = &$this->t('order/iiversion');
		$select = &$this->s();
		$select->from($this->sn('oi'));
		$select->join($oiviTable->sn('oivi'), 'oivi.OrdItemGuid=oi.OrdItemGuid', array());
		$select->where('oivi.OIVGuid=?', $OIVGuid);
		$select->where('oi.IsCanceled=?', '0');
		$select->order('oi.LineIndex ASC');

		return $this->all($select);
	}
	
	public function &getOrderItems($OrderGuid, array $status=array())
	{
		$vTable = &Msd_Dao::table('vendor');
		$iTable = &Msd_Dao::table('item');
		$ieTable = &Msd_Dao::table('item/extend');
		$select = &$this->s();
		$select->from($this->sn('oi'));
		$select->join($iTable->sn('i'), 'i.ItemGuid=oi.ItemGuid', array(
			'i.VendorGuid', 'i.BoxUnitPrice'
			));
		$select->joinleft($ieTable->sn('ie'), 'ie.ItemGuid=oi.ItemGuid', array(
			'ie.HasLogo'	
			));
		$select->where('oi.OrderGuid=?', $OrderGuid);
		count($status)>0 && $select->where('oi.StatusId IN (?)', $status);
		$select->order('oi.LineIndex ASC');

		return $this->all($select);
	}
    
	public function &getItems($OrderGuid) {
		$select = &$this->s();
		$select->from($this->sn('oi'));
		$select->where('oi.OrderGuid=?', $OrderGuid);
		$select->order('oi.AddTime ASC');
		return $this->all($select);
	}
	
	public function &getVersionItems($OrderGuid, $VersionId)
	{
		$rows = array();
		
		$oiviTable = &$this->t('order/iiversion');
		$ovTable = &$this->t('order/version');
		$olvTable = &$this->t('order/lastversion');
		$oTable = &$this->t('order');
		$oivTable = &$this->t('order/itemversion');
		
		$select = &$this->s();
		$select->from($this->sn('oi'));
		$select->join($oiviTable->sn('oivi'), 'oivi.OrdItemGuid=oi.OrdItemGuid', '');
		$select->join($ovTable->sn('ov'), 'ov.OIVGuid=oivi.OIVGuid', '');
		$select->join($oivTable->sn('oiv'), 'oiv.OIVGuid=ov.OIVGuid', array());
		
		$select->where('oiv.VersionId=?', $VersionId);
		$select->where('oi.IsCanceled=?', '0');
		$select->where('ov.OrderGuid=?', $OrderGuid);
		$select->order('oi.LineIndex ASC');

		return $this->all($select);
	}

	public function insert(array $params)
	{
		(isset($params['OrderGuid']) && !($params['OrderGuid'] instanceof Zend_Db_Expr)) && $params['OrderGuid'] = $this->wrapGuid($params['OrderGuid']);
		(isset($params['ItemGuid']) && !($params['ItemGuid'] instanceof Zend_Db_Expr)) && $params['ItemGuid'] = $this->wrapGuid($params['ItemGuid']);
		$params['AddTime'] = $this->expr('GETDATE()');
		$params['AddUser'] || $params['AddUser'] = Msd_Config::appConfig()->db->status->name->default;
		$params['IsClosed'] = 0;
		$params['StatusId'] = Msd_Config::appConfig()->db->status->item->default;
		$params['IsCanceled'] = 0;
		
		return parent::insert($params);
	}
}
