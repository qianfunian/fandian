<?php

class Fadmin_ActiveController extends Msd_Controller_Fadmin
{
	
	public function init()
	{
		parent::init();
	}

	public function indexAction()
	{
		$table = Msd_Dao::table('active');
		$this->view->rows = $table->getAll();
	}
	
}