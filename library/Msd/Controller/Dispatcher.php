<?php

/**
 * 调度手机端的接口
 * 
 * @author pang
 *
 */
class Msd_Controller_Dispatcher extends Msd_Controller_Api
{
	protected $uid = '0';
	protected $user = array();
	protected $cKey = '';
	protected $sKey = '';
	protected $lastSync = 0;
	
	public function init()
	{
		$this->needKeyAuth = false;
		$this->outputWithoutCdata = true;
		
		parent::init();
		
		$this->sess = &Msd_Session::getInstance();
		$this->uid = $this->sess->get('dispatcher_uid');
		$this->user = $this->sess->get('dispatcher_user');
		$this->lastSync = (int)$this->sess->get('last_sync');

		$this->cKey = Msd_Waimaibao_Order_Dispatcher::dKey().'dmi_'.$this->uid;
		$this->sKey = Msd_Waimaibao_Order_Dispatcher::dKey().'dmc_'.$this->uid;
	}
	
	protected function sessCheck()
	{
		if (!$this->uid) {
			$this->error(1001);
		}
	}
	
	protected function error($error_code='', $level='user', $e=null)
	{
		$this->output = array();
		$this->xmlRoot = 'error';
		
		switch ((int)$error_code) {
			case 1000:
				$msg = '没有填写有效的用户名、密码';
				break;
			case 1002:
				$msg = '用户名或密码错误';
				break;
			case 1001:
				$msg = '会话超时，请重新登录';
				break;
			case 2100:
				$msg = '参数非法';
				break;
			case 2101:
				$msg = '无效的订单号';
				break;
			case 2102:
				$msg = '权限非法';
				break;
			case 2103:
				$msg = '订单已完成或取消';
				break;
			case 2104:
				$msg = '订单状态不可逆转';
				break;
			case 2105:
				$msg = '订单状态不可跨级更改';
				break;
			case 2201:
				$msg = '该API尚不支持';
				break;
			default:
				$msg = '未知错误';
				break;
		}
	
		$this->output[$this->xmlRoot] = array(
			'code' => $error_code,
			'msg' => $msg,
			'level' => $level
			);
	
		Msd_Log::getInstance()->apierror($error_code.':'.$msg);
	
		$this->output();
	}
}