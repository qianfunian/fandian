<?php

class Files_AvatarController extends Msd_Controller_Files
{
	protected $config = null;
	
	public function init()
	{
		parent::init();
		$this->config = &Msd_Config::appConfig()->attachment;
	}
	
	public function indexAction()
	{
		$this->normalAction();
	}
	
	public function originAction()
	{
		$data = explode('/', $_SERVER['REQUEST_URI']);
		$hash = $data[4];
		
		$pat = '/^([a-z0-9]{40})\.([a-z]{3,})$/';
		$pat2 = '/^([a-z0-9]{40})$/';
		$fid = '';
		
		if (preg_match($pat, $hash)) {
			$hash = preg_replace($pat.'s', '\\1', $hash);
		} else if (preg_match($pat2, $hash)) {
			$hash = preg_replace($pat2.'s', '\\1', $hash);
		} else {
			$this->defaultAvatar();
		}
		
		$files = Msd_Dao::table('attachment')->avatarByHash($hash);
		
		if ($files[$this->config->usage->avatar]) {
			Msd_Files::Output($files[$this->config->usage->avatar]['FileId']);
		} else {
			$this->defaultAvatar();
		}
		
		exit(0);
	}	
	
	public function __call($method, $params)
	{
		$this->normalAction();
	}
	
	public function normalAction()
	{
		$data = explode('/', $_SERVER['REQUEST_URI']);
		$hash = $data[4];
		
		$pat = '/^([a-z0-9]{40})\.([a-z]{3,})$/';
		$pat2 = '/^([a-z0-9]{40})$/';
		
		if (preg_match($pat, $hash)) {
			$hash = preg_replace($pat.'s', '\\1', $hash);
		} else if (preg_match($pat2, $hash)) {
			$hash = preg_replace($pat2.'s', '\\1', $hash);
		} else {
			$hash = $data[3];
			if (preg_match($pat, $hash)) {
				$hash = preg_replace($pat.'s', '\\1', $hash);
			} else if (preg_match($pat2, $hash)) {
				$hash = preg_replace($pat2.'s', '\\1', $hash);
			} else {
				$this->defaultAvatar('normal');
			}
		}

		$files = Msd_Dao::table('attachment')->avatarByHash($hash);

		if ($files[$this->config->usage->avatar_normal]) {
			Msd_Files::Output($files[$this->config->usage->avatar_normal]['FileId']);
		} else {
			$this->defaultAvatar();
		}
		
		exit(0);		
	}
	
	public function defaultAvatar($size='origin')
	{
		$cacher = Msd_Cache_Remote::getInstance();
		$key = 'default_avatar_'.$size;;
		$data = $cacher->get($key);
		$data = false;
		if (!$data) {
			$file = APPLICATION_PATH . '/../public/images/noavatar_'.$size.'.jpg';
			$data = array(
					'size' => filesize($file),
					'content' => file_get_contents($file)
					);
			$cacher->set($key, $data);
		}

		ob_end_clean();

		header('Content-Type: image/jpeg');
		header('Content-Length: '.(int)$data['size']);
		header('Accpet-Ranges: bytes');
		header('Content-Disposition: attachment; filename='.$key.'.jpg');
		header('Etag: '.sha1($key));
		header('Cache-Control: no-store, no-cache, must-revalidate');
		header('Cache-Control: pre-check=0, post-check=0, max-age=0', false);
		header('Pragma: no-cache');
		header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
		header('Conection: keep-alive');
			
		if (!empty($_SERVER['HTTP_IF_MODIFIED_SINCE']) || !empty($_SERVER['HTTP_IF_NONE_MATCH'])) {
			header('HTTP/'.$_SERVER['HTTP_PROTOCOL'].' 304 Not Modified');
		}
			
		echo $data['content'];
		ob_end_flush();
		exit(0);
	}
	
	public function smallAction()
	{
		$data = explode('/', $_SERVER['REQUEST_URI']);
		$hash = $data[4];
		
		$pat = '/^([a-z0-9]{40})\.([a-z]{3,})$/';
		$pat2 = '/^([a-z0-9]{40})$/';
		$fid = '';
		
		if (preg_match($pat, $hash)) {
			$hash = preg_replace($pat.'s', '\\1', $hash);
		} else if (preg_match($pat2, $hash)) {
			$hash = preg_replace($pat2.'s', '\\1', $hash);
		} else {
			$this->defaultAvatar('small');
		}
		
		$files = Msd_Dao::table('attachment')->avatarByHash($hash);
		
		if ($files[$this->config->usage->avatar_small]) {
			Msd_Files::Output($files[$this->config->usage->avatar_small]['FileId']);
		} else {
			$this->defaultAvatar();
		}
		
		exit(0);		
	}
}