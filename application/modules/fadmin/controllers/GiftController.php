<?php 
class Fadmin_GiftController extends Msd_Controller_Fadmin
{
	public function init()
	{
		parent::init();
	}
	public function indexAction()
	{
		$this->pager_init();
		$table = &Msd_Dao::table('giftcard');
		$params = $sort = array();
		$rows = $table->search($this->pager, $params, $sort);
		
		$this->view->rows = $rows;
		$this->view->page_links = $this->page_links();
	}
	
	public function addAction()
	{
		if($this->_request->isPost())
		{
			if($_POST['m'])
			{
				$filename = $_FILES['gift']['name'];
				$tmp_name = $_FILES['gift']['tmp_name'];
				$msg = $this->uploadFile($filename,$tmp_name);
				
			}else
			{
				$table = &Msd_Dao::table('giftcard');
				$params['AddTime']='';
				$params['GiftId'] =$_POST['giftcode'];
				$params['Value'] =$_POST['value'];
				$params['AddUser'] = $this->member['Username'];

				$table->insert($params);
			}
			$this->redirect($this->scriptUrl.'gift');
		}
	}
	//属于生日卡业务的相关商品
	public function goodsAction()
	{
		$this->pager_init();
		
		$config = &Msd_Config::appConfig();
		
		//$this->view->headScript()->appendFile($this->baseUrl.'js/jquery/jquery.ajaxupload.js');
		//$this->view->headScript()->appendFile($this->baseUrl.'js/jquery/jquery.tooltip.js');
		
		//$VendorGuid = trim(urldecode($this->getRequest()->getParam('VendorGuid', '')));
		$table = &Msd_Dao::table('item');
			
		//$params = $sort = array();
		//$orderKey = trim($this->getRequest()->getParam('order_key', ''));
		//$searchKey = trim(urldecode($this->getRequest()->getParam('search_key', '')));
		//$searchVal = trim(urldecode($this->getRequest()->getParam('search_val', '')));
		
		$params['ServiceName'] = $config->db->n->service_name->giftcard;
		$params['Regions'] = Msd_Waimaibao_Region::RegionGuids();
		//$params['Vendor'] = $VendorGuid;
		
		//(int)$this->getRequest()->getParam('IsRec', '')==1 && $params['IsRec'] = 1;
			
		//if (strlen($searchKey) && strlen($searchVal)) {
		//	$params[$searchKey] = $searchVal;
		//}
			
		//if ($orderKey!='') {
		//	$sort[$orderKey] = 'DESC';
		//}
		
		$tmp = $table->search($this->pager, $params, array());
		$rows = array();
		foreach ($tmp as $row) {
			$row['img_url'] = Msd_Waimaibao_Item::imageUrl($row);
			$row['bimg_url'] = Msd_Waimaibao_Item::imageBigUrl($row);
			$rows[] = $row;
		}
		
		$this->view->rows = $rows;
		$this->view->page_links = $this->page_links();
			
		$this->log(array(
				'type' => 'browse',
				'message' => '浏览生日卡相关商品',
		));
		
	}

	public function editAction()
	{
		$this->view->headScript()->appendFile($this->baseUrl.'js/kindeditor/kindeditor-min.js');
		$this->view->headScript()->appendFile($this->baseUrl.'js/jquery/jquery.ajaxupload.js');
		
		$ItemGuid = trim(urldecode($this->getRequest()->getParam('ItemGuid')));
		
		$iTable = &Msd_Dao::table('item');
		$ieTable = &Msd_Dao::table('item/extend');
		
		$data = $extend = array();
		
		if ($ItemGuid) {
			$data = &$iTable->get($ItemGuid);
			if ($data['ItemGuid']) {
				$extend = $ieTable->get($ItemGuid);
				if (!$extend) {
					$ieTable->insert(array(
							'ItemGuid' => $ItemGuid
					));
				}
			}
		}
		
		$this->view->data = $data;
		$this->view->extend = $extend;
		$this->view->item_logo_url = Msd_Waimaibao_Item::imageSpecialUrl(array(
				'ItemGuid' => $ItemGuid,
				'VendorGuid' => $data['VendorGuid']
		));
	}
	
	public function doeditAction()
	{
		$ItemGuid = $this->getRequest()->getParam('ItemGuid', '');
		
		if (Msd_Validator::isGuid($ItemGuid)) {
			$p = &$this->getRequest()->getPost();
			$dao = &Msd_Dao::table('item/extend');
			$dao->doUpdate(array(
					'Detail' => $p['Detail'],
					'Sales' => (int)$p['Sales'],
					'LongTitle' => trim($p['LongTitle'])
			), $ItemGuid);
		}
		
		$this->redirect($this->scriptUrl.'gift/goods');
	}
	
	//导入Excel文件
	function uploadFile($file,$filetempname)
	{
		
		$filePath = './';
		$str = "";
		
		require_once 'PHPExcel/PHPExcel.php';
		require_once 'PHPExcel/PHPExcel/IOFactory.php';
		require_once 'PHPExcel/PHPExcel/Reader/Excel5.php';
	
		$time=date("y-m-d-H-i-s");
		$extend=strrchr ($file,'.');
		$name=$time.$extend;
		$uploadfile=$filePath.$name;
		$result = move_uploaded_file($filetempname,$uploadfile);
		
		if($result) 
		{
			
			$objReader = PHPExcel_IOFactory::createReader('Excel5');
			$objPHPExcel = $objReader->load($uploadfile);
			$sheet = $objPHPExcel->getSheet(0);
			$highestRow = $sheet->getHighestRow();         
			$highestColumn = $sheet->getHighestColumn();
	
			for($j=2;$j<=$highestRow;$j++)                      
			{
				for($k='A';$k<=$highestColumn;$k++)            
				{
					$str .=$objPHPExcel->getActiveSheet()->getCell("$k$j")->getValue().'\\';
				}
				$strs = explode("\\",$str);
				$table = &Msd_Dao::table('giftcard');
				$params['GiftId'] = $strs[0];
				$params['Value'] = $strs[1];
				$params['AddUser'] = $this->member['Username'];
				
				$table->insert($params);
				$str = "";
			}
			unlink($uploadfile); 
			$msg = 1;
		}
		else
		{
			$msg = 0;
		}
		return $msg;
	}

}
?>