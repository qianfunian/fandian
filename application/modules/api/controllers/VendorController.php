<?php
/**
 * @desc 商家手机端接口 包括商家登录， 获取订单信息，商家确认订单
 * @author Administrator
 *
 */
class Api_VendorController extends Msd_Controller_Api {
	/**
	 * 商家登录
	 */
	protected $vmember = array ();
	public function loginAction() {
		if ((! $_SERVER ['PHP_AUTH_USER'] || ! $_SERVER ['PHP_AUTH_PW'])) {
			header ( 'WWW-Authenticate: Basic realm="Api Auth"' );
			header ( 'HTTP/1.0 401 Unauthorized' );
			exit ();
		} else if ($_SERVER ['PHP_AUTH_USER'] && $_SERVER ['PHP_AUTH_USER']) {
			$pass = $this->authMember ( $_SERVER ['PHP_AUTH_USER'], $_SERVER ['PHP_AUTH_PW'] );
			if (! $pass) {
				$this->error ( 'error.member.auth_failed', 401 );
			}
		}
		
		$this->xmlRoot = 'vmember';
		
		if ($this->vmember) {
			$result ['vendorname'] = $this->vmember ['VendorName'];
			$result ['vendorguid'] = $this->vmember ['VendorGuid'];
			$result ['account'] = $_SERVER ['PHP_AUTH_USER'];
			$result ['pwd'] = $_SERVER ['PHP_AUTH_PW'];
			$this->output [$this->xmlRoot] = $result;
		}
		$this->output ();
		
		exit ();
	}
	protected function authMember($UserName, $PassWord) {
		$passed = false;
		
		$zveTable = Msd_Dao::table ( 'zvendorexpand' );
		$data = $zveTable->get ( $UserName, 'loginID' );
		if ($data ['Password'] == $PassWord) {
			$this->vmember = $data;
			$passed = true;
		}
		return $passed;
	}
	
	/**
	 * 饭店网公告，最近公告
	 */
	public function announceAction() {
		$this->xmlRoot = 'result';
		$announceTable = Msd_Dao::table ( 'announce' );
		$announces = $announceTable->all ();
		
		foreach ( $announces as $announce ) {
			$this->output [$this->xmlRoot] [] = array (
					'announce' => $announce 
			);
		}
		
		$this->output ();
		exit ();
	}
	
	/**
	 * 根据vendorguid 和 date 获取商家订单
	 */
	public function getOrderAction() {
		$vendorGuid = $this->_request->getParam ( 'vendorguid' );
		// 商家心跳
		$zveTable = Msd_Dao::table ( 'zvendorexpand' );
		$zveTable->doUpdate ( array (
				'APPLastTime' => date ( 'Y-m-d H:i:s' ) 
		), $vendorGuid );
		
		$this->xmlRoot = 'result';
		
		$oiaTable = Msd_Dao::table ( 'orderissueapp' );
		$orders = $oiaTable->search ( array (
				'vendorguid' => $vendorGuid 
		), array (
				'IsEnabled' => 'ASC',
				'CreateTime' => 'DESC'
		) );
		
		$i = 0;
		foreach ( $orders as $order ) {
			$d ['OrderId'] = $order ['OrderId'];
			$d ['OrderGuid'] = $order ['OrderGuid'];
			$d ['IsEnabled'] = $order ['IsEnabled'];
			$d ['SumAmount'] = sprintf("%.2f", $order ['SumAmount']);
			$d ['PubDate'] = $order ['CreateTime'];
			if ( $d ['IsEnabled'] === NULL ) {
				$i++;
			}
			$this->output [$this->xmlRoot] [] = array (
					'order' => $d 
			);
		}
		$this->output [$this->xmlRoot] []= array (
				'count' => $i 
		);
		$this->output ();
		exit ();
	}
	
	/**
	 * 更改订单阅读状态,获取订单餐品详情列表
	 */
	public function getOrderDetailAction() {
		$this->xmlRoot = 'result';
		
		$vendorGuid = $this->_request->getParam ( 'vendorguid' );
		$orderGuid = $this->_request->getParam ( 'orderguid' );
		$oiaTable = Msd_Dao::table ( 'orderissueapp' );
		// 更改订单阅读状态
		$data = array (
				'IsRead' => '1',
				'ReplyTime' => date ( 'Y-m-d H:i:s' ) 
		);
		$where = array (
				'VendorGuid' => $vendorGuid,
				'OrderGuid' => $orderGuid 
		);
		$result = $oiaTable->updateOrder ( $data, $where );
		
		$oiaData = $oiaTable->get ( $orderGuid, 'OrderGuid' );
		$msg = '';
		if ($oiaData ['IsEnabled'] !== NULL) {
			if ($oiaData ['IsEnabled'] == 1) {
				$msg = '接受订单  预约取菜时间：' . $oiaData ['IssuedTime'];
			} else {
				$msg = '取消订单  ' . $oiaData ['Remark'];
			}
		}
		$this->output [$this->xmlRoot] [] = array (
				'msg' => $msg 
		);
		// 获取订单详情列表
		if ($result) {
			$oiTable = &Msd_Dao::table ( 'order/item' );
			$items = $oiTable->getItems ( $orderGuid );
			foreach ( $items as $item ) {
				$itemData ['ItemName'] = $item ['ItemName'];
				$itemData ['ItemPrice'] = $item ['ItemPrice'];
				$itemData ['ItemQty'] = $item ['ItemQty'];
				$itemData ['ItemAmount'] = $item ['ItemAmount'];
				$itemData ['ItemRequest'] = $item ['ItemReq'];
				
				$this->output [$this->xmlRoot] [] = array (
						'item' => $itemData 
				);
			}
		}
		$this->output ();
		exit ();
	}
	
	/**
	 * 商家查看订单后的反馈 预约取菜时间 或者 退回订单说明退回原因，退回后由客服回电确认订单情况
	 */
	public function setOrderDetailAction() {
		$this->xmlRoot = 'result';
		$enabled = $this->_request->getParam ( 'enabled' );
		$mark = $this->_request->getParam ( 'mark' );
		$vendorGuid = $this->_request->getParam ( 'vendorguid' );
		$orderGuid = $this->_request->getParam ( 'orderguid' );
		
		$oiaTable = Msd_Dao::table ( 'orderissueapp' );
		
		if ($enabled == 1) {
			$time = ( int ) $mark;
			$data ['IssuedTime'] = date ( 'Y-m-d H:i:s', strtotime ( $time . ' minutes' ) );
		} else {
			$data ['Remark'] = $mark;
		}
		
		$data ['IsEnabled'] = $enabled;
		$data ['ReplyTime'] = date ( 'Y-m-d H:i:s' );
		
		$where = array (
				'VendorGuid' => $vendorGuid,
				'OrderGuid' => $orderGuid 
		);
		
		$result = $oiaTable->updateOrder ( $data, $where );
		
		$msg = $enabled == 1 ? '接受订单' : '退回订单';
		if ($result) {
			$this->output [$this->xmlRoot] = array (
					'msg' => $msg . ' ' . $mark 
			);
		}
		$this->output ();
		exit ();
	}
	
	public function appInfoAction(){
		$this->xmlRoot = 'result';
		$this->output [$this->xmlRoot] = array (
				'versionCode' => '1',
				'versionName' => '1.1',
				'downloadUrl' => "http://10.0.0.4/Vendor.apk",
				'updateLog' => '更新了商家获取订单信息的功能'
		);
		$this->output ();
		exit ();
	}
}