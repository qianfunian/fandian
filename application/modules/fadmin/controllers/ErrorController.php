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
        
		switch ($errors->type) {
			case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ROUTE:
			case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
			case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:
				$this->getResponse()->setHttpResponseCode(404);
				$priority = Zend_Log::NOTICE;
				$this->view->message = 'Page not found';
				break;
			default:
				$this->getResponse()->setHttpResponseCode(500);
				$priority = Zend_Log::CRIT;
				$this->view->message = 'Application error';
				break;
			}

			Msd_Log::getInstance()->exception("\nPrority: ".$priority."\nMessage:".$this->view->message."\nParameters: ".serialize($errors->request->getParams())."\nException:".$errors->exception);

			$this->view->exception = $errors->exception;
			$this->view->request   = $errors->request;
	}
}

