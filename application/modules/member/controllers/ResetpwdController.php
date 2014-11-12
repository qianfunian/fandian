<?php

class Member_ResetpwdController extends Msd_Controller_Member
{
	public function indexAction()
	{
		$error = $request = array();
		
		if ($this->getRequest()->isPost()) {
			$p = $this->getRequest()->getPost();
			$Email = $p['Email'];
			
			if (!Msd_Validator::isEmail($Email)) {
				$error['Email'] = '请输入正确的Email';	
			} else {
				$member = &Msd_Member::createInstance($Email, 'email');
				if ($member && $member->uid()) {
					$extend = $member->extend();
					
					//$handler = &Msd_Member_Resetpwd::getInstance($member->uid());
					//$this->view->hash = $hash = sha1(uniqid(mt_rand()));
					//$handler->saveHash($hash);
					
					$this->view->verifyemail  = urlencode($extend['Email']);
					$this->view->verifystring = urlencode($member->__get('VerifyString'));

                    $receiver = $extend['Email'];
                    $Subject = '重置您在饭店网的密码';
                    $Content = $this->view->render('resetpwd/content.phtml');

                    $emailer = &Msd_Email::factory();
                    $emailer->addTo($receiver, $receiver);
                    $emailer->setSubject($Subject);
                    $emailer->setBodyHtml($Content);

                    try {
                        $emailer->send();
                        Msd_Log::getInstance()->email('Receiver: '.$receiver.', Subject: '.$Subject);
                    } catch (Exception $e) {
                        Msd_Log::getInstance()->email($e->getMessage()."\n".$e->getTraceAsString());
                    }

					$this->redirect('resetpwd?success=1');
				} else {
					$error['Email'] = '对不起，这个Email没有注册过，无法为您找回密码';
				}
			}
		}
		
		$this->view->error = $error;
		$this->view->request = $request;
		$this->view->success = (bool)$this->getRequest()->getParam('success', '');
	}
	
	public function doAction()
	{
		if($this->_request->isPost())
		{
			$email    = $this->_request->getPost('email');
			$password = $this->_request->getPost('password');
			$member   = &Msd_Member::createInstance($email, 'email');
			
			$randomstring=NULL;
			for ($i = 0; $i < 16; $i++)
			{
				$randomstring .= chr(mt_rand(32, 126)); //Range of ASCII characters
			}
			
			$params = array(
					'PassWord' => $password,
					'VerifyString' => $randomstring
			);
			
			$member->update($params);
			
			//$this->_helper->viewRenderer->setNoRender(true);
		}
		$email  = $this->_request->getQuery("email");
		$verify = $this->_request->getQuery("verify");
		
		$member = &Msd_Member::createInstance($email, 'email');
		$member->__get('VerifyString');
		
		$flag = $member->__get('VerifyString') == $verify?1:0;
		$this->view->flag = $flag;
		
// 		$hash = trim($this->getRequest()->getParam('hash', ''));
// 		$table = &Msd_Dao::table('resetpasswordhash');
// 		$data = $table->get($hash);
// 		if ($data['Hash']) {
// 			$handler = &Msd_Member_Resetpwd::getInstance($data['CustGuid']);
			
// 			$newPassword = $handler->doReset();
// 			if ($newPassword) {
// 				$handler->clearMyHash();
				
// 				$this->view->newPassword = $newPassword;
// 			}
// 		} else {
// 			throw new Msd_Exception('参数不正确');
// 		}
	}
}

