<?php


class Member_FeedbackController extends Msd_Controller_Member
{
	protected $handler = null;
	protected $all = array();
	
	public function init()
	{
		parent::init();
		
		$this->AuthRedirect();
	}
	
	public function delAction()
	{
		$AutoId = (int)$this->getRequest()->getParam('AutoId', 0);
		if ($AutoId) {
			$table = &Msd_Dao::table('feedback');
			
			$d = $table->get($AutoId);
			if ($d['CustGuid']!=$this->member->uid()) {
				throw new Msd_Exception('不能删除他人的留言');
			} else {
				$table->doDelete($AutoId);
			}
		}
		
		$this->redirect('feedback');		
	}
	
	public function indexAction()
	{
		$this->pager_init();
		$table = &Msd_Dao::table('feedback');
		
		$rows = $table->search($this->pager, array(
			'CustGuid' => $this->member->uid()
		), array(
			'OrderNo' => 'ASC'
		));
		
		$this->view->rows = $rows;
		$this->view->page_links = $this->page_links($this);		
	}
}