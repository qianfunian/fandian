<?php
 
$url = 'http://open.fandian.com/partner/v12580/order.xml';
//$url = 'http://127.0.3.40/partner/v12580/refund.xml';

$ch = curl_init();

$items = array(
	'AAEA9D7A-2032-462A-B9B0-80943F687F1D|1',
	'9914E062-F604-4489-B2F3-26FB067D23EF|1',
	'EFD77A06-7688-4B2D-A59A-F2629C3BF129|2',
	'D67A503F-ED62-43A0-8144-1D0798728260|3',
	'3D031A45-C4B9-4642-82D7-7A73FF8B0AFB|1',
	);

$post = array(
	'order_id' => date('mdHi'),//date('mdHis').rand(100,999),
	'create_time' => date('Y-m-d H:i:s'),
	'total_money' => 100,
	'payment_status' => 1,
	'contactor' => base64_encode('雷雱'),
	'address' => base64_encode('testestest'),
	'phone' => '18605126700',
	'remark' => base64_encode('test for remark'),
	'items' => $items[rand(0,count($items)-1)],
	'city' => '0510'
	);
$post = array(
	'address' => '无锡ss测试地址',
	'city' => '0519',
	'order_id' => '10417509',
	'create_time' => date('YmdHis').'333',
	'total_money' => '0.1',
	'payment_status' => '0',
	'express_time' => '',
	'contactor' => '夏',
	'phone' => '13770317679',
	'remark' => '网络部测试，请删除',
	'items' => 'CDAA3E60-C9B0-427D-8F86-1E89F6905C90|1',
);
/*$post = array(
	'city' => '0510',
	'order_id' => '0808081027770',
	'money' => '52.00',
	);*/
ksort($post);
$baseString = '';
$tmp = array();
foreach ($post as $k=>$v) {
	$tmp[] = $k.'='.$v;
}	
$baseString = implode('&', $tmp);
$ps = array(
	'string' => $baseString,
	'secret' => 'uylpfomvrektxzwnqbihcgda'
	);

include 'Zend/Http/Client.php';
$http = new Zend_Http_Client('http://192.168.1.88:8080/v12580_encrypt.jsp', array());
$http->setParameterGet($ps);
$sign = trim(strip_tags(trim($http->request('GET')->getBody())));

$post['key'] = 'baaaaaab';
$post['signature'] = $sign;
$params = array(
	CURLOPT_URL => $url,
	CURLOPT_RETURNTRANSFER => 1,
	CURLOPT_POST => 1,
	CURLOPT_POSTFIELDS => $post
	);

curl_setopt_array($ch, $params);

$result = curl_exec($ch);

header('Content-Type: text/xml; charset=utf-8');
header('Content-Length: '.strlen($result));
die($result);