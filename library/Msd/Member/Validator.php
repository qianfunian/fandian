<?php

class Msd_Member_Validator extends Msd_Member_Base
{
	public static $USERNAME_EXISTS = -1;
	public static $USERNAME_NOT_VALID = -2;
	public static $USERNAME_TOO_SHORT = -3;
	
	public static function username($username, $uid=0)
	{
		$codes = array(
				'USERNAME_EXISTS' => -1,
				'USERNAME_NOT_VALID' => -2,
				'SUCCESS' => 1
				);
		$result = $codes['USERNAME_NOT_VALID'];
		$minLength = (int)Msd_Config::appConfig()->member->username->min_length;
		$maxLength = (int)Msd_Config::appConfig()->member->username->max_length;
		
		if (
				!Msd_Validator::isEmptyString($username) &&
				!Msd_Validator::isCell($username) && 
				!Msd_Validator::isEmail($username) &&
				Msd_Validator::inValidLength($username, $minLength, $maxLength)
			) {
			$member = &Msd_Member::createInstance($username, 'username');
			$muid = $member->uid();
			if (Msd_Validator::isGuid($muid) && $muid!=(string)$uid) {
				$result = $codes['USERNAME_EXISTS'];
			} else {
				$result = $codes['SUCCESS'];
			}
		}

		return array(
				'codes' => &$codes,
				'result' => $result
				);
	}
	
	public static function email($email, $uid=0)
	{
		$codes = array(
				'EMAIL_EXISTS' => -1,
				'EMAIL_NOT_VALID' => -2,
				'SUCCESS' => 1
				);
		
		$result = $codes['EMAIL_NOT_VALID'];
		
		if (
			!Msd_Validator::isEmptyString($email) &&
			Msd_Validator::isEmail($email)	
			) {
			$member = &Msd_Member::createInstance($email, 'email');
			
			if ($member->uid() && $member->uid()!=(string)$uid) {
				$result = $codes['EMAIL_EXISTS'];
			} else {
				$result = $codes['SUCCESS'];
			}
		}
		
		return array(
				'codes' => &$codes,
				'result' => $result
				);
	}
	
	public static function cell($cell, $uid=0)
	{
		$codes = array(
				'CELL_EXISTS' => -1,
				'CELL_NOT_VALID' => -2,
				'CELL_NOT_EXISTS_BUT_ORDERED' => -3,
				'SUCCESS' => 1,
			);
		
		$CustGuid = '';
		$result = $codes['CELL_NOT_VALID'];
		
		if (
				!Msd_Validator::isEmptyString($cell) &&
				Msd_Validator::isCell($cell)
		) {
			$member = &Msd_Member::createInstance($cell, 'cell');
			$info = $member->info();
				
			if ($info['CustGuid'] && $info['CustGuid']!=(string)$uid) {
				$result = $codes['CELL_EXISTS'];
			} else {
				$row = &Msd_Dao::table('customer/phone')->OrderCellCheck($cell);
				if (Msd_Validator::isGuid($row['CustGuid'])) {
					$result = $codes['CELL_NOT_EXISTS_BUT_ORDERED'];
					$CustGuid = $row['CustGuid'];
				} else {
					$result = $codes['SUCCESS'];
				}
			}
		}
		
		return array(
				'codes' => &$codes,
				'result' => $result,
				'CustGuid' => $CustGuid
		);		
	}
	
	public static function realname($realname)
	{
		$codes = array(
				'REALNAME_NOT_VALID' => -1,
				'SUCCESS' => 1
				);
		$result = $codes['REALNAME_NOT_VALID'];

		if (Msd_Validator::inValidLength($realname, 2, 24)) {
			$result = $codes['SUCCESS'];
		}

		return array(
				'codes' => &$codes,
				'result' => $result
				);
	}
	
	public static function password($password, $password2)
	{
		$codes = array(
				'PASSWORD_NOT_MATCH' => -2,
				'PASSWORD_NOT_VALID' => -1,
				'SUCCESS' => 1
				);
		$result = $codes['PASSWORD_NOT_VALID'];
		
		$minLength = (int)Msd_Config::appConfig()->member->password->min_length;
		if (strlen($password)<$minLength) {
			$result = $codes['PASSWORD_NOT_VALID'];
		} else if ($password!=$password2) {
			$result = $codes['PASSWORD_NOT_MATCH'];
		} else {
			$result = $codes['SUCCESS'];
		}
		
		return array(
				'codes' => &$codes,
				'result' => $result
				);
	}
}