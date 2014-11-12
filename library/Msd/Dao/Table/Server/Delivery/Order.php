<?php

class Msd_Dao_Table_Server_Delivery_Order extends Msd_Dao_Table_Server
{
	protected static $instance = null;
	
	public function __construct()
	{
		parent::__construct();
		
		$this->_name = $this->prefix.'D_DeliveryOrder';
		$this->_primary = 'Sequence';
		$this->_orderKey = 'Sequence';
		$this->_realPrimary = 'Sequence';
		$this->_primaryIsGuid = false;
	}
	
	public static function &getInstance()
	{
		if (self::$instance==null) {
			self::$instance = new self();
		}

		return self::$instance;
	}
	
	public function getOrderDeliver($OrderGuid)
	{
		$select = &$this->s();
		$select->from($this->sn('od'));
		$select->where('OrderGuid=?', $OrderGuid);
		$select->order('Sequence DESC');
		$select->limit(1);
		
		return $this->one($select);
	}
	
	public function &checkout(array $params)
	{
		$rows = array();
		$DlvManGuid = $params['DlvManGuid'];
		$start = $params['start'];
		$end = $params['end'];
		
		if (!$start || !$end) {
			$now = time();
			if ($hour<4) {
				$start = date('Y-m-d 05:00:00', $now-MSD_ONE_DAY);
				$end = date('Y-m-d 03:00:00', $now);
			} else {
				$start = date('Y-m-d 05:00:00', $now);
				$end = date('Y-m-d 03:00:00', $now+MSD_ONE_DAY);
			}	
		}	
		
		$sql = "SELECT o.[OrderId],
					o.[StatusId],
					s.[CallPhone],
					o.[VendorName],
					SUBSTRING(CONVERT(VARCHAR, ISNULL(ois.[FinishTime], ''), 8), 1, 5) AS CompletionTime,
					o.[ItemAmount],
					o.[Freight],
					dm.[DlvManId],
					co.[BaoXiao],
					co.[FaPiao],
					co.[Comment]
				FROM [dbo].[D_DeliveryOrder] AS do
					INNER JOIN [dbo].[Order] AS o
						ON o.[OrderGuid]=do.[OrderGuid]
					INNER JOIN [dbo].[Sales] AS s
						ON s.[SalesGuid]=o.[SalesGuid]
					INNER JOIN [dbo].[Deliveryman] AS dm
						ON dm.[DlvManGuid]=do.[DlvManGuid]
					LEFT JOIN [dbo].[D_Checkout] AS co
						ON co.[OrderId]=o.[OrderId] AND o.[CityId]=dm.[CityId]
					LEFT JOIN [dbo].[IssueLastVersion] AS ilv
						ON ilv.[OrderGuid]=o.[OrderGuid]
					LEFT JOIN [dbo].[OrderIssue] AS ois
						ON ois.[IssueGuid]=ilv.[IssueGuid]
				WHERE dm.[DlvManGuid]='".$DlvManGuid."'
						AND do.[AddTime]>='".$start."'
						AND do.[AddTime]<'".$end."'
				";
		$rows = &$this->all($sql);
		
		return $rows;
	}
	
	/**
	 * 
	 * @param array $params
	 */
	public function &toDispatch(array $params)
	{
		$rows = array();
		
		$start = $params['start'];
		$end = $params['end'];
		
		$sql = "SELECT o.[OrderId],
					o.[CityId],
					o.[VersionId],
					s.[Category],
					o.[StatusId],
					oi.[StatusId] AS ItemStatus,
					do.[AcceptTime],
					s.[CallPhone],
					v.[VendorName],
					SUBSTRING(CONVERT(VARCHAR, ISNULL(ois.[FinishTime], ''), 8), 1, 5) AS CompletionTime,
					s.[CustName],
					s.[CustAddress],
					s.[CoordName],
					o.[TotalAmount],
					[dbo].Fn_GetReqTimeString(o.[OrderGuid]) AS ReqTime,
					o.[Distance],
					o.[Freight],
					o.[BoxQty],
					o.[BoxAmount],
					CONVERT(VARCHAR, o.[AddTime], 120) AS AddTime,
					v.[ProvideInvoice],
					s.[Invoice],
					ois.[Remark],
					s.[CommonComment],
					s.[RequestRemark],
					s.[SalesAttribute],
					o.[PaymentMethod],
					e.[ElementName],
					oop.[PayedMoney],
					oop.[PayedVia],
					o.[OrderGuid],
					dm.[DlvManId],
					dm.[DlvManName],
					dm.[DlvManGuid],
					s.[CustGuid],
					o.[ItemCount],
					o.[ItemChanged]
				FROM [dbo].[D_DeliveryOrder] AS do
					INNER JOIN [dbo].[Order] AS o
						ON o.[OrderGuid]=do.[OrderGuid]
					INNER JOIN [dbo].[Sales] AS s
						ON s.[SalesGuid]=o.[SalesGuid]
					INNER JOIN [dbo].[Vendor] AS v
						ON v.[VendorGuid]=o.[VendorGuid]
					INNER JOIN [dbo].[Deliveryman] AS dm
						ON dm.[DlvManGuid]=do.[DlvManGuid]
					INNER JOIN [dbo].[IssueLastVersion] AS ilv
						ON ilv.[OrderGuid]=o.[OrderGuid]
					LEFT JOIN (
						SELECT [StatusId], [OrderGuid]
							FROM [dbo].[OrderItem]
						WHERE [StatusId]='Confirmed'
						) AS oi
						ON oi.[OrderGuid]=o.[OrderGuid]
					LEFT JOIN [dbo].[OrderIssue] AS ois
						ON ois.[IssueGuid]=ilv.[IssueGuid]
					LEFT JOIN [dbo].[OrderOnlinePay] AS oop
					INNER JOIN [dbo].[Enum] AS e
						ON e.[Language]='zh-CN' AND e.[EnumName]=N'支付银行' AND e.[ElementValue]=oop.[PayedVia]
						ON oop.[OrderGuid]=o.[OrderGuid]
				WHERE do.[Disabled]=0 
					AND do.[IsInvalid] IS NULL
					AND o.[StatusId] NOT LIKE 'Cancel%'
					AND do.[AddTime]>='".$start."'
					AND do.[AddTime]<'".$end."'
				";
		$rows = &$this->all($sql);
		
		return $rows;
	}
	
	public function &search(&$pager, array $where=array(), array $order=array())
	{
		$rows = array();
		$count = $pager['limit'] ? (int)$pager['limit'] : 20;
		$page = $pager['page'] ? (int)$pager['page'] : 1;
		$offset = $pager['offset'] ? (int)$pager['offset'] : 0;
	
		$stdTable = $this->t('category/standard');
	
		$select = $this->s();
		$cSelect = $this->s();
	
		$select->from($this->sn('c'), '*');
		$select->join($stdTable->sn('std'), 'c.CtgStdGuid=std.CtgStdGuid', 'std.CtgStdName');
	
		$cSelect->from($this->sn('c'), 'COUNT(*) AS total');
		$cSelect->join($stdTable->sn('std'), 'c.CtgStdGuid=std.CtgStdGuid', '');
	
		if ($where['CtgStdGuid']) {
			$_csg = array();
			foreach ($where['CtgStdGuid'] as $_g) {
				Msd_Validator::isGuid($_g) && $_csg[] = $this->wrapGuid($_g);
			}
			
			if (count($_csg)>0) {
				$select->where('c.CtgStdGuid IN(?) ', (array)$_csg);
				$select->where('c.CtgStdGuid IN(?) ', (array)$_csg);
			}
		}
	
		if (count($order)>0) {
			foreach ($order as $key=>$val) {
				$select->order($key.' '.$val);
			}
		} else {
			$select->order('c.'.(is_array($this->_orderKey) ? $this->_realPrimary : $this->_orderKey).' DESC');
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
	
	public function confirm($OrderGuid, $DateTime)
	{
		$where = $this->db->quoteInto('OrderGuid=?', $OrderGuid);
		$where .= ' AND IsInvalid IS NULL';
		
		$this->update(array(
			'AcceptTime' => $DateTime
			), $where);
	}
	
	public function &Categories($CtgGroupGuid)
	{
		$rows = array();
		$select = &$this->s();
		
		$csTable = &$this->t('category/standard');
		$cgmTable = &$this->t('category/groupmember');
		
		$select->from($this->sn('c'), array(
				'c.CtgStdGuid', 'c.CtgName'
				));
		$select->join($cgmTable->sn('cgm'), 'cgm.CtgGuid=c.CtgGuid', array());
		
		$select->where('cgm.CtgGroupGuid=?', $CtgGroupGuid);

		$tmp = $this->all($select);
		if ($tmp) {
			foreach ($tmp as $row) {
				$rows[$row['CtgStdGuid']] = $row['CtgName'];
			}
		}
		
		return $rows;
	}
}