<?php

class ErrorController extends Msd_Controller_Default
{

	public function errorAction()
    {
		$errors = $this->_getParam('error_handler');

		if (!$errors || !$errors instanceof ArrayObject) {
			$this->view->message = 'You have reached the error page';
			return;
		}
		
		$ec = '';

		switch ($errors->type) {
			case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ROUTE:
			case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
			case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:
				$this->getResponse()->setHttpResponseCode(404);
				$priority = Zend_Log::NOTICE;
				$this->view->message ||$this->view->message = '没有找到这个页面';
				$ec = '404';
				
				if (!Zend_Controller_Front::getInstance()->getParam('noViewRenderer')) {
					$this->view->Map301($_SERVER['REQUEST_URI']);
				} else {
					$this->_helper->getHelper('Redirector')->gotoUrl($this->baseUrl);
				}
				break;
			default:
				$this->getResponse()->setHttpResponseCode(500);
				$priority = Zend_Log::CRIT;
				$this->view->message || $this->view->message = '系统错误';
				break;
			}

			switch ($ec) {
				case '404':
					Msd_Log::getInstance()->error404("\nPrority: ".$priority."\nMessage:".$this->view->message."\nParameters: ".serialize($errors->request->getParams())."\nException:".$errors->exception);
					break;
				default:
					Msd_Log::getInstance()->exception("\nPrority: ".$priority."\nMessage:".$this->view->message."\nParameters: ".serialize($errors->request->getParams())."\nException:".$errors->exception);
					break;
			}

			$this->view->exception = $errors->exception;
			$this->view->request   = $errors->request;

			if ($this->isAjax) {
				$output = array(
						'exception' => $this->view->message,
						'success' => 0
						);
				$this->ajaxOutput($output);
			}
	}
}

