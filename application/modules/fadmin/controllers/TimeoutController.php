<?php

class Fadmin_TimeoutController extends Msd_Controller_Fadmin
{
	public function init()
	{
		parent::init();
		
		$this->auth('timeout');
	}
	
	public function logsAction()
	{
		$OrderGuid = trim(urldecode($this->getRequest()->getParam('OrderGuid', '')));
		$this->view->rows = Msd_Dao::table('order/status/log')->getOrderStatusLogs($OrderGuid);
		$this->view->order = Msd_Dao::table('order')->get($OrderGuid);
	}
	
	public function indexAction()
	{
		Msd_Timer::start('analysis');
		
		$cConfig = &Msd_Config::cityConfig();
		$sdate = trim($this->getRequest()->getParam('s_date', date('Y-m-d')));
		
		$params = array();
		$params['s_date_key'] = trim($this->getRequest()->getParam('s_date_key', 'posted'));
		$params['s_date'] = $this->getRequest()->getParam('s_date', date('Y-m-d'));
		$params['e_date'] = $this->getRequest()->getParam('e_date', date('Y-m-d'));
		$params['s_hour'] = $this->getRequest()->getParam('s_hour', 0);
		$params['s_minute'] = $this->getRequest()->getParam('s_minute', 0);
		$params['e_hour'] = $this->getRequest()->getParam('e_hour', 23);
		$params['e_minute'] = $this->getRequest()->getParam('e_minute', 59);
		$params['timeout'] = $this->getRequest()->getParam('timeout', '');
		$params['without_pre'] = $this->getRequest()->getParam('without_pre', '0');
		$params['without_chg'] = $this->getRequest()->getParam('without_chg', '0');
		$params['deliver'] = $this->getRequest()->getParam('deliver', '');
		$params['is_vip'] = trim($this->getRequest()->getParam('is_vip', ''));
		$params['city_id'] = $cConfig->city_id;

		$this->view->data = Msd_Dao::table('order/analysis')->summary($params);
		
		$hours = array();
		for($i=0;$i<24;$i++) {
			$hours[$i] = $i;
		}
		
		$minutes = array();
		for($i=0;$i<60;$i+=10) {
			$minutes[$i] = $i;
		}
		$minutes[59] = 59;
		
		$this->view->hours = $hours;
		$this->view->minutes = $minutes;
		
		$costs = array();
		$costs[''] = '*默认*';
		for($i=30;$i<121;$i+=10) {
			$costs[$i] = $i;
		}
		$this->view->costs = $costs;
		
		$this->view->ds = array(
			'' => '* 请选择 *'	
			);
		$rows = &Msd_Dao::table('deliveryman')->all();
		foreach ($rows as $row) {
			$this->view->ds[$row['DlvManName']] = $row['DlvManName'];
		}
		
		$this->view->search_costs = Msd_Timer::end('analysis');
		
		$this->log(array(
				'type' => 'browse',
				'message' => '浏览超时单',
			));
	}
	
	public function searchAction()
	{
		$this->_search();
			
		$this->log(array(
				'type' => 'browse',
				'message' => '浏览超时单',
			));		
	}
	
	public function exportAction()
	{
		$this->_search();
		
		$config = &Msd_Config::appConfig();
		$cConfig = &Msd_Config::cityConfig();
		
		require_once 'PHPExcel/PHPExcel.php';
		$excel = new PHPExcel();
		$excel->setActiveSheetIndex(0);
		$s = $excel->getActiveSheet();
		
		switch ($this->request['s_date_key']) {
			case 'issued':
				$dstr = '商家下单时间';
				break;
			case 'assigned':
				$dstr = '指派速递时间';
				break;
			default:
				$dstr = '客户下单时间';
				break;
		}
				
		$s->setCellValue('A1', '序号');
		$s->setCellValue('B1', '单号');
		$s->setCellValue('C1', '商家');
		$s->setCellValue('D1', '一级客户');
		$s->setCelLValue('E1', '客户下单时间');
		$s->setCellValue('F1', '商家下单');
		$s->setCellValue('G1', '分配速递');
		$s->setCellValue('H1', '送达时间');
		$s->setCellValue('I1', '耗时（分钟）');
		$s->setCellValue('J1', '要求时间');
		$s->setCellValue('K1', '最后改单时间');
		$s->setCellValue('L1', '配送费');
		$s->setCellValue('M1', '实际耗时');
		$s->setCellValue('N1', '配送员');
		
		$i = 2;
		foreach ($this->view->rows as $row) {
			if ($row['Distance']==0) {
				$freight = 0;
			} else if ($row['Distance']<=3000) {
				$freight = 8;
			} else if ($row['Distance']>3000 && $row['Distance']<=5000) {
				$freight = 15;
			} else if ($row['Distance']>5000 && $row['Distance']<=6000) {
				$freight = 18;
			} else {
				$freight = '>18';
			}
						
			$s->setCellValue('A'.$i, $row['_seq']);
			$s->setCellValue('B'.$i, (string)$row['OrderId']);
			$s->setCellValue('C'.$i, $row['VendorName']);
			$s->setCellValue('D'.$i, $row['IsVip'] ? '是' : '否');
			$s->setCellValue('E'.$i, $this->view->Dt($row['AddTime']));
			$s->setCellValue('F'.$i, $this->view->Dt($row['InformTime']));
			$s->setCellValue('G'.$i, $this->view->Dt($row['AssignedTime']));
			$s->setCellValue('H'.$i, $this->view->Dt($row['DeliveryedTime']));
			$s->setCellValue('I'.$i, $row['Costs']);
			$s->setCellValue('J'.$i, strlen($row['ReqDateTime']) ? $this->view->Dt($row['ReqDateTime'], 'time') : '-');
			$s->setCellValue('K'.$i, $row['LastChangeTime'] ? $this->view->Dt($row['LastChangeTime']) : '--');
			$s->setCellValue('L'.$i, $freight);
			$s->setCellValue('M'.$i, $row['MinutesCost']);
			$s->setCellValue('N'.$i, $row['Deliver']);
			
			$i++;
		}
			
		$this->log(array(
				'type' => 'browse',
				'message' => '导出超时单',
			));	
		
		$objWriter = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
		$tmp_file = $cConfig->system->tmp_dir.DIRECTORY_SEPARATOR.'export_'.date('YmdHis').'_'.rand(10000,99999).'.xlsx';
		$objWriter->save($tmp_file);
		
		$bin = file_get_contents($tmp_file);
		$filename = iconv('utf-8', 'gb2312', '超时单导出');
		
		header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
		header ("Last-Modified: " . gmdate("D,d M YH:i:s") . " GMT");
		header ("Cache-Control: no-cache, must-revalidate");
		header ("Pragma: no-cache");
		header ("Content-type: application/msexcel");
		header('Content-Transfer-Encoding: binary');
		
		header ("Content-Disposition: attachment; filename=".$filename.".xlsx" );
		
		if (strlen($bin)) {
			header('Content-Length: '.strlen($bin));
		}
		echo $bin;
		ob_end_flush();
		exit(0);
	}
	
	protected function _search()
	{
		$this->pager_init();
		$this->pager['page'] = 1;
		$this->pager['limit'] = 999;
			
		$cConfig = &Msd_Config::cityConfig();
		$table = &Msd_Dao::table('order/analysis');
			
		$params = $sort = array();
		$orderKey = trim($this->getRequest()->getParam('order_key', ''));
		$searchKey = trim(urldecode($this->getRequest()->getParam('search_key', '')));
		$searchVal = trim(urldecode($this->getRequest()->getParam('search_val', '')));
		
		$params['freight'] = (int)$this->getRequest()->getParam('price', 8);
		$params['date'] = trim($this->getRequest()->getParam('s_date', date('Y-m-d')));
		$params['s_date_key'] = trim($this->getRequest()->getParam('s_date_key', 'posted'));
		$params['s_date'] = $this->getRequest()->getParam('s_date', date('Y-m-d'));
		$params['e_date'] = $this->getRequest()->getParam('e_date', date('Y-m-d'));
		$params['s_hour'] = $this->getRequest()->getParam('s_hour', 0);
		$params['s_minute'] = $this->getRequest()->getParam('s_minute', 0);
		$params['e_hour'] = $this->getRequest()->getParam('e_hour', 23);
		$params['e_minute'] = $this->getRequest()->getParam('e_minute', 59);
		$params['timeout'] = $this->getRequest()->getParam('timeout', 60);
		$params['without_pre'] = $this->getRequest()->getParam('without_pre', '0');
		$params['without_chg'] = $this->getRequest()->getParam('without_chg', '0');
		$params['deliver'] = $this->getRequest()->getParam('deliver', '');
		$params['IsTimeout'] = 1;
		$params['is_vip'] = trim($this->getRequest()->getParam('is_vip', ''));
		$params['city_id'] = $cConfig->city_id;
			
		if (strlen($searchKey) && strlen($searchVal)) {
			$params[$searchKey] = $searchVal;
		}
			
		if ($orderKey!='') {
			$sort[$orderKey] = 'DESC';
		}
			
		$rows = $table->search($this->pager, $params, $sort);
		$tVendors = array();
		$tDelivers = array();
		$tTimers = array();
		foreach ($rows as $row) {
			if (isset($tVendors[$row['VendorName']])) {
				$tVendors[$row['VendorName']] += 1;
			} else {
				$tVendors[$row['VendorName']] = 1;
			}
			
			if (isset($tDelivers[$row['Deliver']])) {
				$tDelivers[$row['Deliver']] += 1;
			} else {
				$tDelivers[$row['Deliver']] = 1;
			}
			
			$ra = new DateTime($row['RealAddTime']);
			$h = date('H', $ra->getTimestamp());
			if (isset($tTimers[$h])) {
				$tTimers[$h] += 1;
			} else {
				$tTimers[$h] = 1;
			}
		}
		
		arsort($tVendors);
		arsort($tDelivers);
		arsort($tTimers);
		
		$tVendors = array_chunk($tVendors, 5, true);
		$tDelivers = array_chunk($tDelivers, 5, true);
		$tTimers = array_chunk($tTimers, 5, true);
		
		$this->view->rows = $rows;
		$this->view->page_links = $this->page_links();
		$this->view->data = array();
		$this->view->request = $_REQUEST;
		$this->view->tVendors = $tVendors[0];
		$this->view->tDelivers = $tDelivers[0];
		$this->view->tTimers = $tTimers[0];
	}
	
}