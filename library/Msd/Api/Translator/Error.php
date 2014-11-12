<?php

class Msd_Api_Translator_Error extends Msd_Api_Translator_Base
{
	protected static $instance = null;
	
	public static function &getInstance()
	{
		if (self::$instance==null) {
			self::$instance = new self();
		}
		
		return self::$instance;
	}
	
	public function translate(array $params)
	{
		switch ($params['code']) {
			case 'error.order.items_required':
				$result = '下单失败，请添加有效的菜品';
				break;
			case 'error.order.address_required':
				$result = '下单失败，请填写您的送餐地址';
				break;
			case 'error.order.contactor_required':
				$result = '下单失败，请填写您的订单联系人';
				break;
			case 'error.order.phone_required':
				$result = '下单失败，请填写您的联系电话';
				break;
			case 'error.order.items_not_valid':
				$result = '下单失败，您提交的菜品数据有误';
				break;
			case 'error.order.items_count_not_valid':
				$result = '下单失败，菜品数量有误';
				break;
			case 'error.order.only_one_vendor_permitted':
				$result = '下单失败，一次只能提交一个商家的订单';
				break;
			case 'error.order.fatal_error':
				$result = '下单失败，服务器严重错误，请联系管理员';
				break;
			case 'error.member.addressbook_parameters_not_valid':
				$result = '修改地址簿时参数非法';
				break;
			case 'error.member.avatar.invalid_file':
				$result = '没有上传有效的头像文件';
				break;
			case 'error.member.avatar_save_failed':
				$result = '用户头像上传失败';
				break;
			case 'error.member.auth_failed':
				$result = '登录失败，用户名或者密码错误';
				break;
			case 'error.request.api_key_needed':
			case 'error.request.invalid_api_key':
				$result = 'API key无效';
				break;
			case 'error.general.forbidden':
				$result = '没有权限访问该功能';
				break;
			case 'error.general.parameter_invalid':
				$result = '参数错误';
				break;
			case 'error.member.addressbook_rows_limit':
				$result = '最多只能保存5条地址簿信息';
				break;
			case 'error.member.addressbook_address_required':
				$result = '请填写地址簿的地址';
				break;
			case 'error.member.addressbook_contactor_required':
				$result = '请填写地址簿的联系人';
				break;
			case 'error.member.addressbook_phone_required':
				$result = '请填写地址簿的联系电话';
				break;
			case 'error.member.addressbook_name_required':
				$result = '请填写地址簿的标题';
				break;
			case 'error.member.email_not_found':
				$result = '这个Email还没有注册过';
				break;
			case 'error.member.username_not_valid':
				$result = '请填写有效的用户名';
				break;
			case 'error.member.username_exists':
				$result = '用户名已经被注册了';
				break;
			case 'error.member.password_not_valid':
				$result = '请填写有效的密码';
				break;
			case 'error.member.passwords_not_match':
				$result = '两次输入的密码不一致';
				break;
			case 'error.member.email_not_valid':
				$result = '请填写有效的Email地址';
				break;
			case 'error.member.email_exists':
				$result = '这个Email已经被注册了';
				break;
			case 'error.member.cell_not_valid':
			case 'error.member.cellphone_not_valid':
				$result = '请填写有效的手机号码';
				break;
			case 'error.member.cell_exists':
			case 'error.member.cellphone_exists':
				$result = '这个手机号码已经被注册了';
				break;
			case 'error.member.realname_not_valid':
				$result = '请填写有效的真实姓名';
				break;
			case 'error.fatal_error':
				$result = '接口异常，请联系饭店网';
				break;
			default:
				$result = '未知错误';
				break;
		}
		
		return $result;
	}
}