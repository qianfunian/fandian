<?php

/**
 * SMS打印机
 * 
 * @author pang
 *
 */
class Api_GprsprinterController extends Msd_Controller_Default
{
	public function init()
	{
	}
	
	/**
	 * 获取打印机报表
	 * 
	 * 功能描述：获取一些统计信息。该页面要根据以下信息参数计算出对应的起始时间和结束时间（如参入的参数超过系统时间可以只查询到当天的时间）。例如：参数参入的时间为month=2010-1，那么计算出的起始时间：2010-1-1，结束时间：2010-1-31。返回这两个时间段的统计信息即可。
访问方式：
1获取统计信息：GetReportTable.aspx?id=<商家编号>&sn=<加密后的序列号2>&[ month=<年月> | year=<年份>  | quart=<季度>|  time1=<日期1>&time2=<日期2>]&state=[0|1]
2获取详细信息：GetReportTable.aspx?id=<商家编号>&sn=<加密后的序列号2>&[ month=<年月> | year=<年份>  | quart=<季度>|  time1=<日期1>&time2=<日期2>]&state=[0|1]&p=[1|2|…]
参数说明：
id：商家编号
作用：获取密钥，对应商家的报表
sn：加密后的打印机序列号2
作用：解密后与数据库中的序列号2核对，正确才给出对应的报表
month：年份-月份。格式举例：month=2010-1
作用：要查询的月份报表
year：年份。格式举例：year=2010
作用：要查询的年度报表
quart：年份-季度。格式举例：quart=2010-1
作用：要查询的季度报表
time1: 年份-月份-日期。格式举例：time1=2010-1-1
time2：年份-月份-日期。格式举例：time2=2010-12-1
作用：查询time1到time2的时间报表
state：获取报表的类型
作用：0标识获取统计信息，1表示获取详细信息（包括统计）
p：页码；
作用：该参数获取详细信息时使用的。当一次详细信息超过打印机所能接受字符4KB大小时就要分页获取。
返回格式：
1：统计信息：
<stime> 起始时间</stime>//查询统计的起始时间
<time>结束时间</time>//查询统计的结束时间
<allprice>总金额</allprice>//该时间段内
<num>订单总数</num>//订单总数
2：详细信息：
<stime> 起始时间</stime>//查询统计的起始时间
<time>结束时间</time>//查询统计的结束时间
<allprice>总金额</allprice>//该时间段内
<num>订单总数</num>//订单总数
<OdCont>订单信息</OdCont>//订单的信息，每个订单之间用换行符分开
...
<page></page>//当一页发送不玩所有的详细信息时，处最后一页外都要发送；
…
<end></end>//详细信息的最后一页发送该元素
如果改时间段没有报表等信息则返回：<none></none>
其他错误信息返回：<error></error>
	 */
	public function getreporttableAction()
	{
		$this->xmlRoot = '';
		$this->output[$this->xmlRoot] = array(
			'none' => ''	
			);
		
		$this->output();
	}
	
	/**
	 * 打印机将通过短信获取的商家信息更新到服务器上。该信息包括：店铺名称（打印抬头）、打印联数、打印结尾信息
	 * 
	 * 访问方式：UpdateCustInfo.aspx?id=<商家编号>&sn=<加密后的SN2>&phead=<商家名称>&ptimes=<打印联数>&pend=<打印结尾信息>
参数说明：
id：商家编号
作用：根据该编号获取密钥及跟新其对应的信息。该信息是无法修改的
sn：加密后的打印机序列号2。
作用：解密后，与数据库中的序列号2比较，核对信息。信息正确才可以修改对应的信息。
phead：商家名称，也是打印抬头
作用：要更新的商家名称、打印抬头
ptimes：打印的联数
作用：要更新的打印联数
pend：打印结尾信息
作用：要更新的打印结尾信息
返回格式：
调用成功返回：<success></success>
错误返回：<error></error>
	 */
	public function updatecustinfoAction()
	{
		$this->success();
	}
	
	/**
	 * 根据订单编号修改订单的处理状态
	 * 
	 * 访问方式：UpdateOrder.aspx?id=<商家编号>&orderid=<加密后的订单编号>&state=1
参数说明：
id：商家编号
作用：根据商家编号获取对应打印机存在数据库中的密钥
orderid：使用打印机密钥加密后订单编号
作用：把该参数解密后，修改该订单的状态为对应的状态
state：修改成那种状态
作用：指定修改成那种状态。目前只有修改成功的状态。
返回格式：
更新成功：<success></success>
     更新失败：<error></error>
	 */
	public function updateorderAction()
	{
		$config = &Msd_Config::appConfig();
		$state = (int)$this->getRequest()->getParam('state');
		$orderid = trim(urldecode($this->getRequest()->getParam('orderid')));
		$id = trim(urldecode($this->getRequest()->getParam('id')));
		
		if ($state && $orderid && $id) {
			$vTable = &Msd_Dao::table('vendor');
			$oTable = &Msd_Dao::table('order');
			$opTable = &Msd_Dao::table('order/printed');
			$vgTable = &Msd_Dao::table('vendor/gprsprinter');
			$oivTable = &Msd_Dao::table('order/itemversion');
			$ovTable = &Msd_Dao::table('order/version');
			$oiTable = &Msd_Dao::table('order/item');
			$oslTable = &Msd_Dao::table('order/status/log');
			$vcTable = &Msd_Dao::table('vendor/confirm');

			$Vendor = $vTable->getById($id);
			if ($Vendor['VendorGuid']) {
				$Printer = $vgTable->get($Vendor['VendorGuid']);
				if ($Printer['VendorGuid']) {
					$oOrderId = Msd_Service_Hjenp::Decryption($orderid, $Printer['Key']);
					list($oOrderId, $foo) = explode('-', $oOrderId);
					$oVersionId = intval(trim($foo, 'v'))-1;
					
					if ($oOrderId) {
						$Order = $oTable->getByOrderId($oOrderId);
						if ($Order['OrderGuid']) {
							$row = $oivTable->someVersion($Order['OrderGuid'], $oVersionId);

							if ($row['OIVGuid']) {
								$ts = $opTable->transaction();
								$ts->start();
								try {
									$opTable->setPrintedByOIV($row['OIVGuid']);
									
									//	更新订单状态
									$lv = $oivTable->lastVersion($Order['OrderGuid']);
									if ((int)$lv['VersionId']==(int)$row['VersionId']) {
										//	订单日志 OrderStatusLog
										$StatusLogGuid = $oslTable->genGuid();
										$oslTable->insert(array(
												'StatusLogGuid' => $StatusLogGuid,
												'OrderGuid' => $Order['OrderGuid'],
												'StatusId' => $config->order->status->issued,
											));
										
										//	菜品状态 OrderItem
										$items = $oiTable->getOIVItems($row['OIVGuid']);
										foreach ($items as $item) {
											if ($item['StatusId']==$config->order->status->issuing) {
												$oiTable->doUpdate(array(
													'StatusId' => $config->order->status->issued
													), $item['OrdItemGuid']);
											}
										}
										
										//	订单状态 OrderVersion
										$ovTable->doUpdate(array(
											'StatusId' => $config->order->status->issued
											), $lv['OrdVerGuid']);
										
										//	下单记录
										//	期望取菜时间
										$lvc = $vcTable->last($Order['OrderGuid']);
										$OrderMinutes = (int)$Vendor['OrderMinutes'];
										$toConfirmTime = date('Y-m-d H:i:s', time()+60*$OrderMinutes).'.000';
										$vcTable->insert(array(
											'OrderGuid' => $Order['OrderGuid'],
											'OrdVerGuid' => $lv['OrdVerGuid'],
											'ContactMethod' => Msd_Config::appConfig()->db->enum->contact_method->pos,
											'CompletionTime' => $toConfirmTime,
											'Remark' => ''
											));
									}
									$ts->commit();
	
									$this->success();
								} catch (Exception $e) {
									Msd_Log::getInstance()->printer($e->getTraceAsString()."\n".$e->getMessage());
									$ts->rollback();
									
									$this->error();
								}
							} else {
								$this->error();
							}
						} else {
							$this->error();
						}
					} else {
						$this->error();
					}
				} else {
					$this->error();
				}
			} else {
				$this->error();
			}
		} else {
			$this->error();
		}
	}
	
	/**
	 * 根据商家编号返回对应商家最早的并且没有打印的订单
	 * 访问方式：GetPrintOrder.aspx?id=<商家编号>&p=<页码>
参数说明：
id：商家编号。
作用：在打印机初始化时返回的商家编号。
p：页码。
作用：打印机一次只能读取4KB的字节数量。如果订单的长度超过4KB。订单就要被分页传送。
返回格式：
<time>订单下发的时间</time> //该时间与网络校时的时间格式一致
<orderid>订单编号</orderid>//订单编号是已经加密过的编号，
<OdCont>订单内容</OdCont>//订单内容。包括用户名、联系电话、送餐地址、餐品（餐品名称、价格、份数、备注）。打印机打印一行的字节数为32个字节。如果长度超过该字节，打印机自动换行。如果内容中需要用到换行（不满32个字节的），请在要换行的字符后面插入“\r”即可。“\r\n”将换行两次。
<page></page>//分页标志
….
<end></end>//订单结束
元素说明:
Ordered:加密后的订单编号
Time：下订单时间
Page:分页标志
OdCont：订单内容
End：一个订单结束标志。如果一个订单超过打印机一次所能接受的字符串，那么订单将被分页获取（传入的参数page=后面就跟着页面）。订单的每页（最后页除外）的最后都用<page></page>标识出。订单的最后一个页面的最后需要加上该标志。

如果无订单则返回<none></none>
其他错误返回<error></error>
	 */
	public function getprintorderAction()
	{
		$VendorId = trim(urldecode($this->getRequest()->getParam('id')));
		$p = (int)$this->getRequest()->getParam('p', 1);
		$Vendor = &Msd_Dao::table('vendor')->getById($VendorId);
		$VendorGuid = isset($Vendor['VendorGuid']) ? $Vendor['VendorGuid'] : '';

		if ($VendorGuid) {
			$gTable = &Msd_Dao::table('vendor/gprsprinter');
			$row = $gTable->getOrder($VendorGuid);
			if ($row['OrderGuid']) {
				$opTable = &Msd_Dao::table('order/printed');
				$vcTable = &Msd_Dao::table('vendor/confirm');
				$ovTable = &Msd_Dao::table('order/version');
				$oiTable = &Msd_Dao::table('order/item');
				
				$items = &Msd_Dao::table('order/item')->getOIVItems($row['OIVGuid']);
				$this->view->data = array(
					'time' => date('Y-m-d H:i:s'),
					'orderid' => $this->enc($row['OrderId']),
					'items' => &$items,
					'OdCont' => Msd_Iconv::u2g($OrderContent),
					'page' => 1,
					'end' => '',
					'versionid' => $row['VersionId']
					);
				
				$ov = &$ovTable->getOIV($row['OrderGuid'], $row['OIVGuid']);
				$this->view->item_changed = ($ov['ItemChanged'] && $ov['VersionId']) ? (bool)$ov['ItemChanged'] : false;
				
				$lvc = $vcTable->last($row['OrderGuid']);
				$op = $opTable->getOIV($row['OrderGuid'], $row['OIVGuid']);
				$te = new DateTime($op['AddTime']);
				
				$preItemGuids = array();
				$vid = (int)$row['VersionId'];
				if ($vid>0) {
					$ist = $vid-1;
					$ied = $opTable->lastPrintedVersion($row['OrderGuid']);
					$_items = $oiTable->getVersionItems($row['OrderGuid'], $ied);
					foreach ($_items as $item) {
						!in_array($item['ItemGuid'], $preItemGuids) && $preItemGuids[] = $item['ItemGuid'];
					}
					
					$preItemGuids = array_unique($preItemGuids);
				}

				$this->view->time_expected = ($lvc) ? substr($lvc['CompletionTime'], 0, 5) : date('H:i', $te->getTimestamp()+$Vendor['OrderMinutes']*60);
				$this->view->AddTime = $ov['AddTime'];
				$this->view->item_amount = $row['ItemAmount'];
				$this->view->request_remark = $row['RequestRemark'];
				$this->view->is_first_version = $opTable->isFirstVersion($row['OrderGuid']);
				$this->view->pre_item_guids = $preItemGuids;
				
				$opTable->updateSendTime($row['OrderGuid'], $row['OIVGuid']);
				$opTable->updateUnuse($row['OrderGuid'], $row['opAddTime']);
			
				self::updateHeartbeat($VendorGuid);
			} else {
				self::updateHeartbeat($VendorGuid);
				$this->none();
			}
		} else {
			$this->error();
		}
	}
	
	/**
	 * 返回服务器当前的时间
	 * 返回格式：
<sysTime>服务器当前时间</sysTime>//服务器当前时间：24小时制、占用19个字节，不足位的前面补“0”。日期之间使用“-”隔开，日期与时间之间使用一个英文空格隔开，时间之间使用英文冒号隔开。例如：2010-01-01 01:01:01。
	 */
	public function timingAction()
	{
	}
	
	/**
	 * 打印机第一次运行，内存中并没有相应商家的信息。所以需要进行初始化相关的操作。该页面根据打印机提供的sn1和sn2获取商家的信息
	 * InitionPrinter.aspx?sn1=<SN1码>&sn2=<加密后的SN2代码>
	 * 参数说明：
sn1：打印机背面的序列号；
作用：更具sn1获取存储在数据库中的密钥。该密钥与打印机中保留的密钥是一样的。
sn2：经过加密后的打印机内存中保留的序列号；
作用：该参数经过解密后和数据库中的序列号进行核对。正确了才返回商家信息，以防非法获取商家信息；
返回格式：（各元素之间没有换行，为描述方便特换行。下面也一样）
 <custid>商家编号</ custid >//商家编号，打印机根据这个编号获取商家信息；
<phead>店铺名称</phead>//店铺名称，也是打印机打印的抬头名称；如果没没有，打印机将不打印；
<phone>13512345678</phone>//负责人电话，也是发送指令的电话，只有该号码才能修改打印机的一些重要信息；
<ptimes>2</ptimes>//打印的联数
<pend>打印结尾</pend>//打印结尾信息，如果没有打印机将不打印结尾信息
如果没有对应的编号返回：<none></none>
错误返回：<error></error>
注意：<phone></phone>的号码要么为空、要么为你可以使用的号码。否则初始化后，打印机将不接受其他号码短信指令的设置！当为空时，接受所有手机号码的设置指令。当为可使用的号码时，只有这个号码的短信指令有效
	 */
	public function initionprinterAction()
	{
		$Sn1 = trim(urldecode($this->getRequest()->getParam('sn1')));
		$Sn2 = trim(urldecode($this->getRequest()->getParam('sn2')));
		
		if ($Sn1) {
			$gTable = &Msd_Dao::table('vendor/gprsprinter');
			$vTable = &Msd_Dao::table('vendor');
			$row = $gTable->validateSn($Sn1);
			
			if ($row) {
				$Vendor = $vTable->cget($row['VendorGuid']);
				if ($Vendor['VendorGuid']) {
					$this->view->data = array(
						'custid' => $Vendor['VendorId'],
						'phead' => Msd_Iconv::u2g($Vendor['VendorName']),
						'phone' => $row['Cell'],
						'ptimes' => 1,
						'pend' => ''
						);
				} else {
					$this->none();
				}
			} else {
				$this->none();
			}
		} else {
			$this->error();
		}
	}

	protected function enc($str)
	{
		return $str;
	}
	
	protected function none()
	{
		echo $this->view->render('gprsprinter/none.phtml');
		exit(0);
	}
	
	protected function error()
	{
		echo $this->view->render('gprsprinter/error.phtml');
		exit(0);
	}
	
	protected function success()
	{
		echo $this->view->render('gprsprinter/success.phtml');
		exit(0);
	}
	
	protected static function updateHeartbeat($VendorGuid)
	{
		Msd_Dao::table('vendor/gprsprinter')->updateHeartbeat($VendorGuid);
	}
}