<?php

/**
 * 团膳
 * 
 * @author pang
 *
 */
class TuanController extends Msd_Controller_Default
{
	public function init()
	{
		parent::init();
		
		if (!Msd_Config::cityConfig()->navi->tuan_enable) {
			$this->redirect('');
		}
	}	

	public function indexAction()
	{
		$config = &Msd_Config::appConfig();
		$cityConfig = &Msd_Config::cityConfig();
		
		$votes = array();
		$data = (array)Msd_Votes::getModuleVotes('团膳');
		foreach ($data as $row) {
			$votes[] = Msd_Cache_Loader::Vote($row['AutoId']);
		}

		$this->view->votes = $votes;
		
		$pager = array(
			'page' => 1,
			'limit' => 999,
			'skip' => 0	
			);
		$rows = &Msd_Dao::table('item')->search($pager, array(
			'CtgName' => $config->db->n->ctg_name->tuan,
			'Disabled' => 0,
			'passby_pager' => true
			), array());

		$ods = &Msd_Waimaibao_Order::parseCookieItems('tuan_items');
		$p_items = $itemids = $c_items = $items = $remarks = $_remarks = $_items = $order_items = array();
		$tmp = explode(',', $_COOKIE['tuan_items']);
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
				);
		}

		$this->view->c_items = $c_items;
		$this->view->titems = $items;
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
	
	public function galleryAction()
	{
		$cacher = &Msd_Cache_Remote::getInstance();
		$output = array();
		$ItemGuid = $this->getRequest()->getParam('ItemGuid', '');
		$output['success'] = 1;
		
		$Item = &Msd_Dao::table('item')->cget($ItemGuid);
		$VendorGuid = $Item['VendorGuid'];
		
		$key = 'turl_'.$ItemGuid;
		$urls = $cacher->get($key);

		if (!$urls) {
			$urls = array();
			$config = &Msd_Config::cityConfig();
			$staticUrl = Msd_Controller::staticUrl();
	
			$path = $config->attachment->save_path->items_tuan;
			$path .= $VendorGuid.'/'.$ItemGuid.'/';
			
			$storage = &Msd_File_Storage::factory($config->attachment->save->protocol);
			$files = $storage->dirFiles($path);

			foreach ($files as $file) {
				if (preg_match('/jpg/i', $file) && !preg_match('/thumb/i', $file)) {
					$urls[] = $staticUrl.$config->attachment->web_url->items_tuan.$VendorGuid.'/'.$ItemGuid.'/'.$file;
				}
			}

			if (count($urls)==0) {
				$urls[] = Msd_Waimaibao_Item::imageBigUrl(array(
					'ItemGuid' => $ItemGuid,
					'VendorGuid' => $VendorGuid
					));
			} else {
				sort($urls);
			}
			
			$cacher->set($key, $urls, MSD_ONE_DAY);
		}
		
		$output['urls'] = &$urls;
		
		$this->ajaxOutput($output);
	}
}

