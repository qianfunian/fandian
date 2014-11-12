<?php

/**
 * 
 * 网站文章
 * @author pang
 *
 */

class ArticleController extends Msd_Controller_Default
{
	public function payAction()
	{
		$id = (int)$this->getRequest()->getParam('id', 0);
		
		$this->pager_init();
		$table = &Msd_Dao::table('article');
		$config = &Msd_Config::cityConfig();
		
		$rows = $table->search($this->pager,  array(
				'CategoryId' => array(
						$config->db->article->category->help
				),
				'PubFlag' => '1',
				'passby_pager' => true,
				'Regions' => $config->db->guids->area->toArray()
			), array(
				'OrderNo' => 'ASC',
				'PubTime' => 'DESC'
			));
		
		$this->view->op_config = $config->onlinepay->toArray();
		$this->view->rows = $rows;
		$this->view->page_links = $this->page_links();
		$this->view->request = $_REQUEST;		
	}
	
	public function serviceAction()
	{
		$id = (int)$this->getRequest()->getParam('id', 0);
		$title = trim(urldecode($this->getRequest()->getParam('title', '')));
	
		$this->pager_init();
		$table = &Msd_Dao::table('article');
		$config = &Msd_Config::cityConfig();
	
		$rows = $table->search($this->pager,  array(
				'CategoryId' => array(
						$config->db->article->category->service
				),
				'PubFlag' => '1',
				'passby_pager' => true,
				'Regions' => $config->db->guids->area->toArray()
			), array(
				'OrderNo' => 'ASC',
				'PubTime' => 'DESC'
			));
	
		if (is_array($rows) && count($rows)>0) {
			if ($id || strlen($title)) {
				foreach ($rows as $row) {
					if ($row['ArticleId']==$id || $title ==$row['Title']) {
						$this->view->data = $row;
						break;
					}
				}
				
				if (!$this->view->data) {
					$this->_helper->getHelper('Redirector')->gotoUrl($this->scriptUrl.'article/service');
				}
			} else {
				$this->view->data = $rows[0];
			}
		}
	
		$this->view->rows = $rows;
		$this->view->page_links = $this->page_links();
		$this->view->request = $_REQUEST;
		$this->view->views = Msd_Article::updateViews($this->view->data['ArticleId']);
	}
	
	public function aboutusAction()
    {
		$id = (int)$this->getRequest()->getParam('id', 0);
		$title = trim(urldecode($this->getRequest()->getParam('title', '')));
		
		$this->pager_init();
		$table = &Msd_Dao::table('article');
		$config = &Msd_Config::cityConfig();
		
		$rows = $table->search($this->pager,  array(
				'CategoryId' => array(
					$config->db->article->category->aboutus
					),
				'PubFlag' => '1',
				'passby_pager' => true,
				'Regions' => $config->db->guids->area->toArray()
			), array(
				'OrderNo' => 'ASC',
				'PubTime' => 'DESC'
			));

		if (is_array($rows) && count($rows)>0) {
			if ($id || strlen($title)) {
				foreach ($rows as $row) {
					if ($row['ArticleId']==$id || $title==$row['Title']) {
						$this->view->data = $row;
						break;
					}
				}
				
				if (!$this->view->data) {
					$this->_helper->getHelper('Redirector')->gotoUrl($this->scriptUrl.'article/aboutus');
				}
			} else {
				$this->view->data = $rows[0];
			}
		}

		$this->view->rows = $rows;
		$this->view->page_links = $this->page_links();
		$this->view->request = $_REQUEST;
		$this->view->views = Msd_Article::updateViews($this->view->data['ArticleId']);
    }
    /*
     * 福利资讯
     */
    public function fulizixunAction()
    {
    	$id = (int)$this->getRequest()->getParam('id', 0);
    	$title = trim(urldecode($this->getRequest()->getParam('title', '')));
    	 
    	$this->pager_init();
    	$table = &Msd_Dao::table('article');
    	$config = &Msd_Config::cityConfig();
    	
    	$rows = $table->search($this->pager,  array(
    			'CategoryId' => array(
    					$config->db->article->category->fulizixun
    			),
    			'PubFlag' => '1',
    			'passby_pager' => true,
    			'Regions' => Msd_Waimaibao_Region::RegionGuids()
    	), array(
    			'OrderNo' => 'ASC',
    			'PubTime' => 'DESC'
    	));
    	 
    	if (is_array($rows) && count($rows)>0) {
    		if ($id || strlen($title)) {
    			foreach ($rows as $row) {
    				if ($row['ArticleId']==$id || $row['Title']==$title) {
    					$this->view->data = $row;
    					break;
    				}
    			}
    			 
    			if (!$this->view->data) {
    				$this->_helper->getHelper('Redirector')->gotoUrl($this->scriptUrl.'article/fulizixun');
    			}
    		} else {
    			$this->view->data = $rows[0];
    		}
    	}
    	 
    	$this->view->rows = $rows;
    	$this->view->page_links = $this->page_links();
    	$this->view->request = $_REQUEST;
    	$this->view->views = Msd_Article::updateViews($this->view->data['ArticleId']);
    }
    /*
     * 节日福利
     */
    public function jierifuliAction()
    {
    	$id = (int)$this->getRequest()->getParam('id', 0);
    	$title = trim(urldecode($this->getRequest()->getParam('title', '')));
    	
    	$this->pager_init();
    	$table = &Msd_Dao::table('article');
    	$config = &Msd_Config::cityConfig();

    	$rows = $table->search($this->pager,  array(
    			'CategoryId' => array(
    					$config->db->article->category->jierifuli
    			),
    			'PubFlag' => '1',
    			'passby_pager' => true,
    			'Regions' => Msd_Waimaibao_Region::RegionGuids()
    	), array(
    			'OrderNo' => 'ASC',
    			'PubTime' => 'DESC'
    	));
    	
    	if (is_array($rows) && count($rows)>0) {
    		if ($id || strlen($title)) {
    			foreach ($rows as $row) {
    				if ($row['ArticleId']==$id || $row['Title']==$title) {
    					$this->view->data = $row;
    					break;
    				}
    			}
    	
    			if (!$this->view->data) {
    				$this->_helper->getHelper('Redirector')->gotoUrl($this->scriptUrl.'article/jierifuli');
    			}
    		} else {
    			$this->view->data = $rows[0];
    		}
    	}
    	
    	$this->view->rows = $rows;
    	$this->view->page_links = $this->page_links();
    	$this->view->request = $_REQUEST;
    	$this->view->views = Msd_Article::updateViews($this->view->data['ArticleId']);
    } 
    public function othersAction()
    {
    	$id = (int)$this->getRequest()->getParam('id', 0);
		$title = trim(urldecode($this->getRequest()->getParam('title', '')));

    	$this->pager_init();
    	$table = &Msd_Dao::table('article');
    	$config = &Msd_Config::cityConfig();
    
    	$rows = $table->search($this->pager,  array(
    			'CategoryId' => array(
    					$config->db->article->category->others
    			),
    			'PubFlag' => '1',
				'passby_pager' => true,
				'Regions' => $config->db->guids->area->toArray()
    		), array(
    			'OrderNo' => 'ASC',
    			'PubTime' => 'DESC'
    		));
    
    	if (is_array($rows) && count($rows)>0) {
    		if ($id || strlen($title)) {
    			foreach ($rows as $row) {
    				if ($row['ArticleId']==$id || $row['Title']==$title) {
    					$this->view->data = $row;
    					break;
    				}
    			}
				
				if (!$this->view->data) {
					$this->_helper->getHelper('Redirector')->gotoUrl($this->scriptUrl.'article/others');
				}
    		} else {
    			$this->view->data = $rows[0];
    		}
    	}
    
    	$this->view->rows = $rows;
    	$this->view->page_links = $this->page_links();
    	$this->view->request = $_REQUEST;
    	$this->view->views = Msd_Article::updateViews($this->view->data['ArticleId']);
    }
    
	public function helpAction()
    {
		$id = (int)$this->getRequest()->getParam('id', 0);
		$title = trim(urldecode($this->getRequest()->getParam('title', '')));

		$this->pager_init();
		$table = &Msd_Dao::table('article');
		$config = &Msd_Config::cityConfig();
		
		$rows = $table->search($this->pager,  array(
				'CategoryId' => array(
					$config->db->article->category->help
					),
				'PubFlag' => '1',
				'passby_pager' => true,
				'Regions' => $config->db->guids->area->toArray()
			), array(
				'OrderNo' => 'ASC',
				'PubTime' => 'DESC'
			));

		if (is_array($rows) && count($rows)>0) {
			if ($id || strlen($title)) {
				foreach ($rows as $row) {
					if ($row['ArticleId']==$id || $title==$row['Title']) {
						$this->view->data = $row;
						break;
					}
				}
				
				if (!$this->view->data) {
					$this->_helper->getHelper('Redirector')->gotoUrl($this->scriptUrl.'article/help');
				}
			} else {
				$this->view->data = $rows[0];
			}
		}

		$this->view->rows = $rows;
		$this->view->page_links = $this->page_links();
		$this->view->request = $_REQUEST;
		$this->view->views = Msd_Article::updateViews($this->view->data['ArticleId']);
    }
}
