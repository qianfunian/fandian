<?php

class Msd_Dao_Table_Web_Orderissueapp extends Msd_Dao_Table_Web
{
	protected static $instance = null;
	
	public function __construct()
	{
		parent::__construct();
		
		$this->_name = $this->prefix.'OrderIssueApp';
		$this->_primary = 'Id';
		
	}
	
	public static function &getInstance()
	{
		if (self::$instance==null) {
			self::$instance = new self();
		}
		
		return self::$instance;
	}
	
	
	public function updateOrder($data, $where)
	{
		foreach($where as $k=>$w) {
			$string .= $this->db->quoteInto("$k = ?", $w).' AND ';
			
		}
		$string = substr($string, 0,-4);
		$flag = $this->update($data, $string);
		//如果接受订单 商家下单跳过辅调
		if($data ['IsEnabled'] == 1){
			$orderGuid = $where['OrderGuid'];
			
			$issuedTime = $data ['IssuedTime'];
			
			try {
				$ts = &$this->transaction();
				$ts->start();
					
				$this->db->query("update OrderItem  set  StatusId = 'Issued'  where OrderGuid = '$orderGuid'");
					
				$this->db->query("
						insert into OrderStatusLog values(NEWID(),(select CityId from [Order] where OrderGuid = '$orderGuid'),'$orderGuid','Issued',NULL,CONVERT(nvarchar,getdate(),120),'商家')
						");
				
				$row = $this->db->query("select
						[Order].CityId,
						OrderGuid,
						VersionId,
						[Order].VendorGuid,
						VendorName,
						ItemCount,
						ItemAmount,
						BoxAmount,
						SumAmount,
						ContactMethod
						from [Order] left join VendorContactMethod on [Order].VendorGuid =
						VendorContactMethod.VendorGuid
						where OrderGuid = '$orderGuid'")->fetch();
				extract($row);
				
				$sql = "insert into OrderIssue values(
						NEWID(),
						'$CityId', 
						'$OrderGuid', 
						$VersionId, 
						'Issued',
						'$VendorGuid', 
						'$VendorName', 
						$ItemCount, 
						$ItemAmount, 
						$BoxAmount, 
						$SumAmount, 
						$ContactMethod,
						'$issuedTime',
						NULL,
						CONVERT(nvarchar,getdate(),120),
						'$VendorName'
				)";
				
				$this->db->query($sql);
				
				$this->db->query("update [Order] set StatusId = 'Issued' where OrderGuid = '$orderGuid'");
				
				$ts->commit();
			}catch (Exception $e) {
				$ts->rollback();
				Msd_Log::getInstance()->orderissueapp($e->getMessage()."\n".$e->getTraceAsString());
			}
		}
		return $flag;
	}
	
	public function &search(array $where=array(), array $order=array())
	{
		$select = $this->s();
		$select->from($this->_name);
		
		if ($where['vendorguid']) {
			$select->where('VendorGuid = ?', $where['vendorguid']);
			$select->where('CreateTime > ?', date('Y-m-d'));
		}
		
		if (count($order)>0) {
			foreach ($order as $key=>$val) {
				$select->order($key.' '.$val);
			}
		} else {
			$select->order($this->primary().' DESC');
		}
	
		$result = $this->all($select);
	
		return $result;
	}
}