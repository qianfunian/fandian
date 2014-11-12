<?php
class ActiveController extends Msd_Controller_Default
{
	public function init()
	{
		parent::init();
	}
	
	public function indexAction()
	{
		$table = Msd_Dao::table('active');
		$params = $this->_request->getParam('order','Poll');
		
		if($params != 'CreateTime'&& $params != 'Poll')
		{
			$this->_redirect();
		}
		$rows = $table->getAll($params,1);
		$this->view->rows = $rows;
		
		$this->view->day = (strtotime(date('2013-4-30'))-strtotime(date('Y-m-d')))/86400;
		
	}
	
	public  function attendAction()
	{
		if($this->_request->isPost())
		{
			if(trim($this->_request->getPost("Realname"))=="" || trim($this->_request->getPost("Photolink"))==""||trim($this->_request->getPost("Mobilephone"))==""|| trim($this->_request->getPost("Email"))==""|| trim($this->_request->getPost("Enounce"))=="")
			{
				header("Location:http://wx.fandian.com/active/error");
			}else{
				$table = Msd_Dao::table('active');
				$post = $this->_request->getPost();
				
				if($table->insert($post))
				{
					header("Location:http://wx.fandian.com/active/success");
				}
			}
		}
	}
	public function successAction()
	{
		
	}
	public function errorAction()
	{
		
	}
	public function pollAction()
	{
		$id =  $this->_request->getPost('id');
		
		$cacher = &Msd_Cache_Remote::getInstance();

		$vkey = 'poll_'.$id.'_'.ip2long(Msd_Request::clientIp());
		$lastVote = (int)$cacher->get($vkey);
		$lastCookie = $_COOKIE["poll_".$id];
		$ua = ' '.strtolower($_SERVER['HTTP_USER_AGENT']);

		if(time(date('Y-m-d')) >= time('2013-05-01'))
		{
			echo "2";
		}else{
			if (substr($ua, 0, 12)!='mozilla/4.0' && !preg_match('/msie 6/', $ua)) {
				if ($lastVote || $lastCookie) {
					echo '0';
				}else
				{
					Msd_Log::getInstance()->poll($id.'-'.Msd_Request::clientIp().'-'.substr($ua, 0, 11));
					$table = Msd_Dao::table('active');
					$table->updatePoll($id,1);
					setcookie("poll_".$id,'1',time() + MSD_ONE_DAY);
					$cacher->set($vkey, time(), MSD_ONE_DAY);
					echo '1';
				}
			} else {
				echo '0';
			}
		}

		exit;
	}
	
	public function deleteAction()
	{
		if($this->_request->isPost())
		{
			$uid = $this->_request->getPost('uid');
			$table = Msd_Dao::table('active');
			if($table->doDelete($uid))
			{
				echo "1";
			}else
			{
				echo "0";
			}
			
		}
		exit;
	}
	
	public function confimAction()
	{
		if($this->_request->isPost())
		{
			$tag = $this->_request->getPost('tag');
			$uid = $this->_request->getPost('uid');
			$table = Msd_Dao::table('active');
			if($table->updateActive($tag,$uid))
			{
				echo $tag;		
			}else
			{
				echo '3';
			}
		}
		exit;
	}
}
