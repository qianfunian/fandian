<?php

class Member_OrderController extends Msd_Controller_Member
{
	protected $handler = null;
	
	public function init()
	{
		parent::init();
	
		$this->AuthRedirect();
		$this->handler = &Msd_Member_Order::getInstance($this->member->uid());
	}
	
	/**
	 * 曾用过的地址
	 * 
	 */
	public function usedaddressAction()
	{
		$this->view->rows = $this->handler->last5Address();
	}
	
	public function savecommentAction()
	{
		$OrderGuid = trim(urldecode($this->getRequest()->getParam('OrderGuid', '')));
		if (Msd_Validator::isGuid($OrderGuid)) {
			$data = Msd_Waimaibao_Order::detail($OrderGuid);

			
			if ($data['customer']['CustGuid']==$this->member->uid()) {
				
				$comment = Msd_Waimaibao_Order_Comment::load($OrderGuid, $this->member->uid());
				if (!$comment) {
					Msd_Dao::table('order/comment')->insert(array(
						'OrderGuid' => $OrderGuid,
						'VendorGuid' => trim(urldecode($this->getRequest()->getParam('VendorGuid', ''))),
						'CustGuid' => $this->member->uid(),
						'UserName' => $this->member->Username,
						'Content' => trim(urldecode($this->getRequest()->getParam('comment_content', ''))),
						'OrderNo' => '9999',
						'IsShow' => '1',
						'Core'   => trim(urldecode($this->getRequest()->getParam('core', '1')))
						));
					
					$this->_helper->getHelper('Redirector')->gotoUrl($this->scriptUrl.'order/show?OrderId='.$OrderGuid);
				}
			} else {
				throw new Msd_Exception('对不起，您不能浏览其他人的订单。');
			}
		} else {
			throw new Msd_Exception('参数错误');
		}
	}
	
	public function showAction()
	{
		$OrderGuid = trim(urldecode($this->getRequest()->getParam('OrderId', '')));
		if (Msd_Validator::isGuid($OrderGuid)) {
			$data = Msd_Waimaibao_Order::detail($OrderGuid);
			$this->view->data = $data;

			if ($data['customer']['CustGuid']==$this->member->uid()) {
				$this->view->data = $data;
				$this->view->comment = Msd_Waimaibao_Order_Comment::load($OrderGuid, $this->member->uid());
			} else {
				throw new Msd_Exception('对不起，您不能浏览其他人的订单。');
			}
		} else {
			throw new Msd_Exception('参数错误');
		}
	}
	
	public function indexAction()
	{
		$params = array('limit' => 15);
		$this->pager_init($params);
		
		$table = &Msd_Dao::table('order');
			
		$params = $sort = array();
		$params['CustGuid'] = $this->member->uid();

		$rows = $table->search($this->pager, $params, $sort);
		
		$this->view->rows = $rows;
		$this->view->page_links = $this->page_links($this);
		$this->view->data = array();
		$this->view->request = $_REQUEST;		
	}
}

