<?php

class Fadmin_VotesController extends Msd_Controller_Fadmin
{
	protected $Modules = array();
	
	public function init()
	{
		parent::init();
		
		$this->auth('votes');
		$modules = explode(',', Msd_Config::cityConfig()->votes->modules);
		foreach ($modules as $m) {
			$this->Modules[$m] = $m;
		}
		
		$this->view->Modules = $this->Modules;
	}
	
	public function delAction()
	{
		$AutoId = (int)$this->getRequest()->getParam('AutoId', 0);
		
		if ($AutoId) {
			Msd_Dao::table('votes')->doDelete($AutoId);
			Msd_Dao::table('vote/choices')->delChoices($AutoId);
			
			Msd_Cache_Clear::vote($AutoId);
		}
		
		$this->log(array(
				'type' => 'browse',
				'message' => '删除投票调查',
		));
		
		$this->redirect($this->scriptUrl.'votes');
	}
	
	public function doeditAction()
	{
		$error = array();
		$vc = array();
		$dvc = array();
		$uvc = array();
		
		$params = $this->getRequest()->getParams();
		$AutoId = (int)$params['AutoId'];
		$VoteTitle = trim($params['VoteTitle']);
		$PubFlag = (int)$params['PubFlag'];
		$IsMultiChoice = (int)$params['IsMultiChoice'];
		$OrderNo = (int)$params['OrderNo'];
		$Choices = 0;
		$Module = trim($params['Module']);
		$Intro = trim($params['Intro']);

		if ($AutoId) {
			$obj = Msd_Votes::getInstance($AutoId);
			$vote = $obj->getVote();
			$dvc = $obj->getChoices();
		}
		
		if ($VoteTitle=='') {
			$error['VoteTitle'] = '请填写投票标题';
		}

		$i = 0;
		$choices = array();
		foreach ($params as $k=>$v) {
			if (preg_match('/^choice/i', $k) && strlen(trim($v))>0) {
				if (isset($dvc[$i])) {
					$uvc[] = array(
						'AutoId' => $dvc[$i]['AutoId'],
						'ChoiceTitle' => $params['choice_'.$i],
						'Choosed' => (int)$params['choosed_'.$i]	
						);
				} else {
					$uvc[] = array(
						'ChoiceTitle' => $params['choice_'.$i],
						'Choosed' => (int)$params['choosed_'.$i]
						);
				}
				$Choices++;
			}
			
			if (preg_match('/^choice/i', $k) && strlen(trim($v))>0) {
				$choices[$i] = array(
					'ChoiceTitle' => $v,
					'Choosed' => $params['choosed_'.str_replace('choice_', '', $k)]	
					);	
			
				$i++;
			}
		}

		if ($Choices==0) {
			$error['choices'] = '请至少设置一个选项';
		}
		
		if (count($error)>0) {
			$this->view->headScript()->appendFile($this->baseUrl.'js/kindeditor/kindeditor-min.js');
			
			$this->view->choices = $choices;
			$this->view->data = $params;
			$this->view->error = $error;
			echo $this->view->render('votes/edit.phtml');
			exit(0);
		}
		
		$voteParams = array(
			'VoteTitle' => 	$VoteTitle,
			'PubFlag' => $PubFlag,
			'IsMultiChoice' => $IsMultiChoice,
			'OrderNo' => $OrderNo,
			'Choices' => $Choices,
			'Module' => $Module,
			'Intro' => $Intro
			);
		
		$vTable = &Msd_Dao::table('votes');
		$vcTable = &Msd_Dao::table('vote/choices');
		if ($AutoId) {
			$vTable->doUpdate($voteParams, $AutoId);
			
			$log = '修改投票调查';
			
			Msd_Cache_Clear::vote($AutoId);
		} else {
			$vTable->insert($voteParams);
			$AutoId = $vTable->lastInsertId();
			
			$log = '增加投票调查';
		}

		foreach ($uvc as $vc) {
			if ($vc['AutoId']) {
				if (strlen(trim($vc['ChoiceTitle']))>0) {
					$vcTable->doUpdate(array(
						'ChoiceTitle' => $vc['ChoiceTitle'],
						'Choosed' => $vc['Choosed']
						), $vc['AutoId']);
				} else {
					$vcTable->doDelete($vc['AutoId']);
				}
			} else {
				$vcTable->insert(array(
					'VoteId' => $AutoId,
					'ChoiceTitle' => $vc['ChoiceTitle'],
					'Choosed' => $vc['Choosed']	
					));
			}
		}    	
		
    	$this->log(array(
    			'type' => 'browse',
    			'message' => $log
    			));
		
		$this->redirect($this->scriptUrl.'votes');
	}
	
	public function editAction()
	{
		$this->view->headScript()->appendFile($this->baseUrl.'js/kindeditor/kindeditor-min.js');
		
		$AutoId = (int)$this->getRequest()->getParam('AutoId', 0);
		
		$vote = array(
			'OrderNo' => '9999'	
			);
		$choices = array();
		
		if($AutoId) {
			$obj = Msd_Votes::getInstance($AutoId);
			$vote = $obj->getVote();
			$choices = $obj->getChoices();
		}

		$this->view->choices = $choices;
		$this->view->data = $vote;
	}
	
	public function indexAction()
	{
		$this->pager_init();
		
		$table = &Msd_Dao::table('votes');
			
		$params = $sort = array();
		$orderKey = trim($this->getRequest()->getParam('order_key', ''));
		$searchKey = trim(urldecode($this->getRequest()->getParam('search_key', '')));
		$searchVal = trim(urldecode($this->getRequest()->getParam('search_val', '')));
			
		if (strlen($searchKey) && strlen($searchVal)) {
			$params[$searchKey] = $searchVal;
		}

		if ($orderKey!='') {
			$sort[$orderKey] = 'DESC';
		}
		
		$rows = $table->search($this->pager, $params, $sort);
		
		$this->view->rows = $rows;
		$this->view->page_links = $this->page_links();
		$this->view->data = array();
		$this->view->request = $_REQUEST;
			
		$this->log(array(
				'type' => 'browse',
				'message' => '浏览投票',
		));		
	}
}

