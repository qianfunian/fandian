<?php

require_once 'Zend/Captcha/Image.php';
require_once 'Zend/Session/Namespace.php';

class Msd_Image_Captcha
{
	public static function check($code, $module='')
	{
		$sess = &Msd_Session::getInstance($module);
		
		return strtolower($sess->get('captcha_code'))==strtolower($code) ? true : false;
	}

	public static function output($module='')
	{
		$sess = &Msd_Session::getInstance($module);
		
		$code = '';
		$list = 'BCEFGHJKMPQRTVWXY2346789';
		$len  = strlen($list) - 1;
		for ($i = 0; $i < 4; $i++) {
			$code .= $list[mt_rand(0, $len)];
		}
		$sess->set('captcha_code', $code);
		
		$img	=	imagecreate(50,22)	 ;
		$black	=	imagecolorallocate($img , 254 , 104 , 51)	;	//	背景颜色
		$white	=	imagecolorallocate($img , 255 , 255 ,255)	;//前景颜色
		$gray = imagecolorallocate($img , 200 , 200 , 200)	;
		
		$c = rand(0, 3);
		switch ($c) {
			case 0:
				$c = $black;
				break;
			case 1:
				$c = $white;
				break;
			case 2:
			default:
				$c = $gray;
				break;
		}
		imagefill($img , 68 , 30 , $c);
		
		//将验证码写入图片
		imagestring($img , 5 , 8 , 3 , $code , $white)	;
		
		//加入干扰元素
		for($i=0;$i<400;$i++) {
			$randcolor	=	imagecolorallocate($img, rand(0,255) , rand(0,255) , rand(0,255));
			imagesetpixel($img , rand()%70 , rand()%30 , $randcolor);
		}
		
		//输出图形
		header($_SERVER['SERVER_PROTOCOL'].' 200 OK');
		header('Status: 200');
		header('Cache-Control: no-store, no-cache, must-revalidate');
		header('Cache-Control: pre-check=0, post-check=0, max-age=0', false);
		header('Pragma: no-cache');
		header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
		header('Conection: keep-alive');
				
		header("Content-type: image/png");
		imagepng($img);
		imagedestroy($img)	;
		
		exit(0);		
	}
}
