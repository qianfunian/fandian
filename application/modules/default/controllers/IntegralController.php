<?php

/**
 * 
 * 积分兑换
 * @author pang
 *
 */

class IntegralController extends Msd_Controller_Default
{
	
	public function indexAction()
    {
    	if (!Msd_Config::cityConfig()->credit->enabled) {
    		$this->building();
    	}
    	
    	$categories = &Msd_Waimaibao_Credit::Categories();
    	
    	$table = &Msd_Dao::table('credit');
    	
    	$params = $sort = array();
		$params['PubFlag'] = '1';
			
		$pager = array(
			'limit' => 999,
			'page' => 1,
			'offset' => 0
			);
			
    	$rows = $table->search($pager, $params, $sort);

    	$request = array(
    			'Contactor' => $_COOKIE['contactor'],
    			'Address' => $_COOKIE['address'],
    			'Phone' => $_COOKIE['phone']
    			);
    	
    	$this->view->rows = $rows;
    	$this->view->page_links = $this->page_links();
    	$this->view->data = array();
    	$this->view->request = $request;
    	$this->view->categories = $categories;
    }
    
	public function doAction()
	{
		$ArticleId = (int)$this->getRequest()->getParam('ArticleId', 0);
		$data = &Msd_Waimaibao_Credit::get($ArticleId);
		
		$output = array(
			'message' => '兑换失败，请检查你的可用积分及该物品是否还有剩余！',
			'success' => 0	
			);
		
		if ($data['extend']['ArticleId']) {
			$params = $this->getRequest()->getParams();
			$output = &Msd_Waimaibao_Credit::exchange($data['extend']['ArticleId'], $params);
		}
		
		$this->ajaxOutput($output);
    }
}