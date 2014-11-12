<?php

class Msd_Dao_Table_Web_Custdiscount extends Msd_Dao_Table_Web
{
	protected static $instance = null;
	
	public function __construct()
	{
		parent::__construct();
		
		$this->_name = $this->prefix.'CustDiscount';
		$this->_primary = 'CGuid';
	}
	
	public static function &getInstance()
	{
		if (self::$instance==null) {
			self::$instance = new self();
		}
		
		return self::$instance;
	}
	
	public function &search($phone,$id = '')
	{
		if($id=='cz'){
		$sql= "select cd.CGuid,cd.CustGuid,at.ActName,cd.DiscntValue,cd.DiscountState
 from W_CustDiscount cd left join Activities at  
 on cd.ActGuid  = at.ActGuid  left join [dbo].[CustomerPhone] cp  
 on cd.CustGuid = cp.CustGuid  
 where cd.DiscountState = 'NoUsed' 
 and at.CityId   = 'cz'
 and cp.PhoneNumber = '{$phone}'";

}else{
$sql = "select cd.CGuid,cd.CustGuid,at.ActName,cd.DiscntValue,cd.DiscountState
from W_CustDiscount cd left join Activities at 
on cd.ActGuid = at.ActGuid left join [dbo].[CustomerPhone] cp 
on cd.CustGuid = cp.CustGuid
where cd.DiscountState = 'NoUsed' 
and at.CityId = '{$id}'
and cp.PhoneNumber = '{$phone}'";}
		$result = $this->db->query($sql)->fetchAll();
		return $result;
	}
}
