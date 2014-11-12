<?php 
/*
 * 生日福利
 */
class WelfareController extends Msd_Controller_Default
{
	protected function _init()
	{
		$config = &Msd_Config::appConfig();
		$cityConfig = &Msd_Config::cityConfig();
	
		$pager = array(
				'page' => 1,
				'limit' => 999,
				'skip' => 0
		);
		$rows = &Msd_Dao::table('item')->search($pager, array(
				'ServiceName' => $config->db->n->service_name->giftcard,
				'Disabled' => 0,
				'passby_pager' => true
		), array());
	
		$ods = &Msd_Waimaibao_Order::parseCookieItems('gift_items','生日卡');
		
		$p_items = $itemids = $c_items = $items = $remarks = $_remarks = $_items = $order_items = array();
		$tmp = explode(',', $_COOKIE['gift_items']);
		if (count($tmp)>0) {
			foreach ($tmp as $row) {
				list($ItemGuid, $count) = explode('|', $row);
				$count = (int)$count;
				if ($count>0) {
					$order_items[$ItemGuid] = array(
							'ItemGuid' => $ItemGuid,
							'count' => $count
					);
					$_items[] = array(
							'ItemGuid' => $ItemGuid,
							'count' => $count
					);
				}
			}
		}
	
		foreach ($rows as $row) {
			$c_items[$row['VendorGuid']] || $c_items[$row['VendorGuid']] = array();
				
			if (!$items[$row['VendorGuid']]) {
				if ($_COOKIE['coord_guid']) {
					$r = Msd_Waimaibao_Freight::calculate($_COOKIE['coord_guid'], $row['VendorGuid']);
					$freight = $r['freight'];
				} else {
					$freight = 0;
				}
	
				$items[$row['VendorGuid']] = array(
						'items' => array(),
						'VendorGuid' => $row['VendorGuid'],
						'VendorName' => $row['VendorName'],
						'freight' => $freight
				);
			}
	
			if (isset($order_items[$row['ItemGuid']])) {
				$c_items[$row['VendorGuid']][] = array(
						$row['ItemGuid'],
						$order_items[$row['ItemGuid']]['count']
				);
				$itemids[] = $row['ItemGuid'];
	
				$row['_count_'] = $order_items[$row['ItemGuid']]['count'];
				$p_items[$row['VendorGuid']] || $p_items[$row['VendorGuid']] = array();
				$p_items[$row['VendorGuid']]['VendorGuid'] = $row['VendorGuid'];
				$p_items[$row['VendorGuid']]['VendorName'] = $row['VendorName'];
				$p_items[$row['VendorGuid']]['items'][] = $row;
			}
				
			$items[$row['VendorGuid']]['items'][] = array(
					'ItemGuid' => $row['ItemGuid'],
					'ItemName' => $row['ItemName'],
					'UnitPrice' => $row['UnitPrice'],
					'UnitName' => $row['UnitName'],
					'BoxQty' => $row['BoxQty'],
					'BoxUnitPrice' => $row['BoxUnitPrice'],
					'MinOrderQty' => $row['MinOrderQty'] ? $row['MinOrderQty'] : 1	,
					'ItemQty' => $row['ItemQty'],
					'VendorGuid' => $row['VendorGuid'],
					'Description' => $row['Description'],
					'HasLogo' => 1,
					'Sales' => $row['Sales'],
					'Persisted' => (int)$row['Persisted']
			);
		}
	
		$this->view->c_items = $c_items;
		$this->view->items = $items;
		$this->view->itemids = $itemids;
		$this->view->p_items = $p_items;
		$this->view->ods = $ods;
		$this->view->itypes = count($items);
	
		$services = array();
		$ds = &Msd_Cache_Loader::Services();
		foreach ($ds as $_service) {
			if (!$_service['Disabled']) {
				$services[] = array(
						'guid' => $_service['ServiceGuid'],
						'start' => substr($_service['StartTime'], 0, 8),
						'end' => substr($_service['EndTime'], 0, 8)
				);
			}
		}
		$this->view->services = $services;
	
		$tmp = explode('[]', $_COOKIE['remarks']);
		if (count($tmp)>0) {
			foreach ($tmp as $row) {
				list($remark, $_VendorGuid) = explode('{}', $row);
				$remark = trim($remark);
	
				$_remarks[] = array(
						'VendorGuid' => $_VendorGuid,
						'remark' => $remark
				);
				$remarks[$_VendorGuid] = $remark;
			}
		}
	
		$this->view->citems = $_items;
		$this->view->cremarks = $_remarks;
		$this->view->remark = $remarks;
	}
	
	public function indexAction()
	{
		$cacher = &Msd_Cache_Remote::getInstance();
		$cacheKey = 'gift_index';
		$items = $cacher->get($cacheKey);
		$newCacheLoaded = false;
		
		$config = &Msd_Config::appConfig();
		$pager = array(
				'page' => 1,
				'limit' => 999,
				'skip' => 0, 
				);
		
		if (!$items['set']) {
			$items['set'] = &Msd_Dao::table('item')->search($pager, array(
					'ServiceName' => $config->db->n->service_name->giftcard,
					'CtgName' => $config->db->n->ctg_name->giftcard->set
					), array());
			$newCacheLoaded = true;
		}
		if (!$items['cake']) {
			//生日蛋糕
			$items['cake'] = &Msd_Dao::table('item')->search($pager, array(
					'ServiceName' => $config->db->n->service_name->giftcard,
					'CtgName' => $config->db->n->ctg_name->giftcard->cake
			), array());
			$newCacheLoaded = true;
		}
		
		if (!$items['flower']) {
			//鲜花祝福
			$items['flower'] = &Msd_Dao::table('item')->search($pager, array(
					'ServiceName' => $config->db->n->service_name->giftcard,
					'CtgName' => $config->db->n->ctg_name->giftcard->flower
			), array());
			$newCacheLoaded = true;
		}
		
		if (!$items['food']) {
			//生日配餐
			$items['food'] = &Msd_Dao::table('item')->search($pager, array(
					'ServiceName' => $config->db->n->service_name->giftcard,
					'CtgName' => $config->db->n->ctg_name->giftcard->food
			), array());
			$newCacheLoaded = true;
		}
		
		if (!$items['gift']) {
			//生日好礼
			$items['gift'] = &Msd_Dao::table('item')->search($pager, array(
					'ServiceName' => $config->db->n->service_name->giftcard,
					'CtgName' => $config->db->n->ctg_name->giftcard->gift
			), array());
			$newCacheLoaded = true;
		}
		
		if (!$items['fulizixun']) {
			
			$items['fulizixun'] = &$this->getArticl(array(Msd_Config::cityConfig()->db->article->category->fulizixun));		
			$newCacheLoaded = true;
		}
		
		if(!$items['jierifuli'])
		{
			$items['jierifuli'] = &$this->getArticl(array(Msd_Config::cityConfig()->db->article->category->jierifuli));
			$newCacheLoaded = true;
		}
		
		if ($newCacheLoaded) {
			$cacher->set($cacheKey, $items);
		}
	
		//Msd_Debug::dump($items);exit;
		$this->view->items = $items;

	}
	
	public function getArticl($category)
	{
		$pager = array(
				'limit' => 5,
				'pg' => 1
		);
			
		$table = &Msd_Dao::table('article');
		
		$params = array();
		$params['PubFlag'] = '1';
		$params['CategoryId'] = $category;
		$params['Regions'] = Msd_Waimaibao_Region::RegionGuids();
			
		$sort = array(
				'OrderNo' => 'ASC',
				'PubTime' => 'DESC'
		);
			
		return $articls = $table->search($pager, $params, $sort);
	}
	
	public function showAction()
	{
		$this->_init();
		$ItemGuid = $this->getRequest()->getParam('Item', '');
		 
		if (!Msd_Validator::isGuid($ItemGuid)) {
			$this->redirect('welfare');
			exit(0);
		}
		 
		$data = $extend = array();
		 
		$data = Msd_Dao::table('item')->get($ItemGuid);
		$extend = Msd_Dao::table('item/extend')->get($ItemGuid);
		 
		$this->view->data = $data;
		$this->view->extend = $extend;
		
	}
	
	/*
	 *查询礼品卡号使用状况
	 */
	public function queryGiftcardAction()
	{
		if($this->_request->isPost())
		{
			$giftcode = &$_POST['giftcode'];
			$table = &Msd_Dao::table('giftcard');
			$row =$table->get($giftcode);
			
			echo json_encode($row);
		}
		exit(0);
	}
	
	/*
	 * 礼品卡号验证
	 */
	public function validateAction()
	{	
		if($this->_request->isPost())
		{
			$giftcode = &$_POST['giftcode'];
			
			$table = &Msd_Dao::table('giftcard');
			$row =$table->get($giftcode);
			if($row==null)
			{
				echo "1";
			}elseif($row['UsedTime'] != null)
			{
				echo "2";
			}else
			{
				echo "恭喜，验证成功！可抵用￥".$row["Value"]."元";
			}
		}
		exit;
	}
}