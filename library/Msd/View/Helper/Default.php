<?php

class Msd_View_Helper_Default extends Msd_View_Helper_Base
{	
	/**
	 * 生成投票选项html
	 * 
	 * @param unknown_type $choice
	 * @param unknown_type $vid
	 */
	public function VoteChoice($choice, $vote)
	{
		$isMulti = $vote['IsMultiChoice'] ? true : false;
		$name = 'vote_'.$vote['AutoId'];
		
		$params = array(
			'name' => $name,
			'value' => $choice['AutoId'],
			'label' => $choice['ChoiceTitle']	
			);
		$isMulti && $params['type'] = 'array';
		
		return $isMulti ? $this->Checkbox($params) : $this->Radiobox($params);
	}
	
	/**
	 * 价格过滤
	 * 
	 */
	public function Pricefilter($f)
	{
		$arr = array(
			'0.0' => '* 不限 *',
			'1.20' => '1元 ~ 20元',
			'20.50' => '20元 ~ 50元',
			'50.100' => '50元 ~ 100元',
			'1.50' => '1元 ~ 50元',
			'1.100' => '1元 ~ 100元'
			);
		
		return $this->view->Select($arr, 'price_filter', $f);
	}
	
	/**
	 * 点餐过程中的顶部提示
	 */
	public function OrderStepTip($step=1)
	{
		switch (intval($step)) {
			case 4:
				$str = '请确认您的订单';
				break;
			case 3:
				$h = (int)date('H');
				$m = (int)date('m');
				if (($h>9 && $h<21) || ($h==9 && $m>30) || ($h==21 && $m<30)) {
					$str = '选择美味';
				} else if (($h>1 && $h<9) || ($h==9 && $m<30)) {
					//	停止营业
					$str = "现在非饭店网线下服务时间，您现在的订单将会在".(date('H')<10 ? '今天' : '明天')."9点30分后由客服人员核实处理，敬请谅解。";
				} else {
					$str = "现在是饭店网夜宵配送时间，非夜宵商家的订单将会在".(date('H')<10 ? '今天' : '明天')."9点30分后由客服人员核实处理，敬请谅解。";
				}
				break;
			case 2:
				$h = (int)date('H');
				$m = (int)date('m');
				if (($h>9 && $h<21) || ($h==9 && $m>30) || ($h==21 && $m<30)) {
					$str = '选择商家开始点餐';
				} else if (($h>1 && $h<9) || ($h==9 && $m<30)) {
					//	停止营业
					$str = "现在非饭店网线下服务时间，您现在的订单将会在".(date('H')<10 ? '今天' : '明天')."9点30分后由客服人员核实处理，敬请谅解。";
				} else {
					$str = "现在是饭店网夜宵配送时间，非夜宵商家的订单将会在".(date('H')<10 ? '今天' : '明天')."9点30分后由客服人员核实处理，敬请谅解。";
				}
				break;
			default:
				$str = '为了我们更好的为您筛选商家、并且更精确的为您计算运费，请先设置您的送餐地址';
				break;
		}
		
		return $str;
	}
	
	/**
	 * 商家列表链接
	 * 
	 * @param array $params
	 */
	public function Vendorurl(array $params)
	{
		$np = array(
			'distance' => null,
			'sort' => null,
			'keyword' => null,
			'category' => null,
			'pg' => null
			);
		foreach ($params as $k=>$v) {
			if (strlen(trim($v))) {
				$np[$k] = $v;
			}
		}
		
		if ($np['keyword']) {
			$np['keyword'] = str_replace('/', '', $np['keyword']);
		}
		
		return $this->view->url($np);
	}
	
	/**
	 * 转换老网站的地址到新网站
	 * 
	 * @param string $url
	 */
	public function Map301($url)
	{
		$new = '';

		$url = strtolower($url);
		$url = ltrim($url, '/');
		list($url, $url_params) = explode('?', $url);
		$url_params = explode('=', $url_params);
		$params = explode('/', $url);
		
		$config = &Msd_Config::cityConfig()->urlmap;
		
		switch ($params[0]) {
			case 'dpoint':
				$id = (int)$params[1];
				$maps = $config->dpoint->toArray();
				if ($id && $maps['id_'.$id]) {
					$new = 'vendor/'.$maps['id_'.$id];
				}
				break;
			case 'dpoints':
				if (!isset($params[1])) {
					$keyword = $_REQUEST['keyword'];
					
					if ($keyword) {
						$new = 'vendor/search/keyword/'.$keyword;
					} else {
						$new = 'vendor';
					}
				} else {
					if ($url_params['keyword']==Msd_Iconv::u2g('龙虾')) {
						$new = 'vendor/search/龙虾';
					} else {
						$_s = substr($params[1], -32);
						if (strlen($_s)==32) {
							switch ($_s) {
								//	本帮菜
								case '80b334b3d494f15f1276e15110ca8145':
									$new = 'vendor/index/category/本帮菜';
									break;
								//	湘菜
								case '9d17b4cabe6111fa796756aa2a79a7c3':
									$new = 'vendor/index/category/湘菜';
									break;
								//	川菜
								case 'a8ed0b6868e24dc2d4b466cf0a4cb3a6':
									$new = 'vendor/index/category/川菜';
									break;
								//	零食点心
								case 'f4205989e189a57d93338644033bcabe':
									$new = 'vendor/index/category/零食点心';
									break;
								//	干锅类
								case 'a0cba89d64a823086514fa3666a46b1d':
									$new = 'vendor/index/category/干锅';
									break;
								//	粤菜
								case '9f09e18bbc4e88ce1388d30d460f5235':
									$new = 'vendor/index/category/粤菜';
									break;
								//	日韩料理
								case 'dd9aa95df8ee2184d70969c214dbed4d':
									$new = 'vendor/index/category/日韩料理';
									break;
								//	中西简餐
								case '85a7b5146338ab8f194212c10be6e3ab':
									$new = 'vendor/index/category/中西简餐';
									break;
								//	中西快餐
								case '04d8c4a77f35e32617143503a040478c':
									$new = 'vendor/index/category/中西快餐';
									break;
								//	其他
								case 'f49737d2727bfaa9ea1022678b039d26':
									$new = 'vendor/index/category/其他';
									break;
								//	下午茶
								case '2f7f5ad3880d6927d65b4f34666839ac':
									$new = 'vendor/index/category/下午茶';
									break;
								//	东北菜
								case '9cb45b61654760b79aa644d888c434f8';
									$new = 'vendor/index/category/东北菜';
									break;
								//	夜宵
								case 'e5972d74d33727442e2c4e7b3d9f43c7':
									$new = 'vendor/index/service/夜宵';
									break;
								default:
									$new = 'vendor';
									break;
							}
						}
					}
				}
				break;
			case 'words':
				$new = 'feedback';
				break;
			case 'static':
			case 'help':
				$new = 'article/help';
				break;
		}
		
		if ($new) {
			@ob_end_clean();
			header('Location: /'.$new, 301);
			exit(0);
		}
	}
	
	/**
	 * 是否在饭店网一般服务时间范围内
	 * 
	 * @return bool
	 */
	public function InNormalServiceTime()
	{
		$flag = false;
		$config = &Msd_Config::appConfig()->service_time->normal;
		$start = new DateTime(date('Y-m-d').' '.$config->start.':00');
		$end = new DateTime(date('Y-m-d').' '.$config->end.':00');
		$time = time();
		
		if ($time>=$start && $time<=$end) {
			$flag = true;
		}
		
		return $flag;
	}
	
	/**
	 * 是否在饭店网夜宵服务时间范围内
	 *
	 * @return bool
	 */
	public function InNightServiceTime()
	{
		$flag = false;
		$config = &Msd_Config::appConfig()->service_time->night;
		$time = time();
		$start = new DateTime(date('Y-m-d').' '.$config->start.':00');
		$end = new DateTime(date('Y-m-d', $time+MSD_ONE_DAY).' '.$config->end.':00');
		
		if ($time>=$start && $time<=$end) {
			$flag = true;
		}
		
		return $flag;
	}
	
	/**
	 * 是否在饭店网下午茶服务时间范围内
	 *
	 * @return bool
	 */
	public function InNoonServiceTime()
	{
		$flag = false;
		$config = &Msd_Config::appConfig()->service_time->noon;
		$start = new DateTime(date('Y-m-d').' '.$config->start.':00');
		$end = new DateTime(date('Y-m-d').' '.$config->end.':00');
		$time = time();
		
		if ($time>=$start && $time<=$end) {
			$flag = true;
		}
		
		return $flag;
	}
	
	public function Vendorinservice($VendorGuid)
	{
		return Msd_Waimaibao_Vendor::InService(Msd_Waimaibao_Vendor::C_ServiceTime($VendorGuid));	
	}
	
	/**
	 * 天气详情格式化
	 * 
	 * @param unknown_type $string
	 */
	public function Wd($string) 
	{
		$arr = array(
			'感冒指数', '运动指数', '洗车指数', '穿衣指数', '路况指数', '空气污染指数'	
			);
		
		foreach ($arr as $key) {
			$string = str_replace($key, '<br /><br />'.$key, $string);
		}

		return $string;
	}
	
	/**
	 * 商家是否不在服务时间
	 * 
	 * @param unknown_type $VendorGuid
	 */
	public function Outofservice($VendorGuid)
	{
		$str = '';
		if ($VendorGuid) {
			$serviceTimes = Msd_Waimaibao_Vendor::C_ServiceTime($VendorGuid);
			$now = time();
			$inService = false;
			foreach ($serviceTimes as $st) {
				$fd = new DateTime(date('Y-m-d '.$this->view->Sdt($st['StartTime'], 'hm').':00'));
				$ed = new DateTime(date('Y-m-d '.$this->view->Sdt($st['EndTime'], 'hm').':00'));
				if (!$inService && $now>=$fd->getTimestamp() && $now<=$ed->getTimestamp()) {
					$inService = true;
					break;
				}
			}
				
			if (!$inService) {
				$str = 'outofservice';
			}
		}
	
		return $str;
	}
	
	public function VendorServiceTimeIcon($data, $ServiceName='普通', $service=array())
	{
        $str = ((int)$data['InService'] && time()>$service[$ServiceName]['start'] && time()<$service[$ServiceName]['end']) ? '1' : '0';
		return $str;
	}

	/**
	 * 预订单的时间选择
	 * 
	 * @param unknown_type $time
	 */
	public function Orderpreselect($time)
	{
		$html = '';
		
		if (is_array($time)) {
			$o_year = $time['year'] ? (int)$time['year'] : date('Y');
			$o_month = $time['month'] ? (int)$time['month'] : (int)date('m', time()+MSD_ONE_DAY);
			$o_day = $time['day'] ? (int)$time['day'] : (int)date('d', time()+MSD_ONE_DAY);
			$o_hour = $time['hour'] ? (int)$time['hour'] : (int)date('H', time()+MSD_ONE_DAY);
			$o_minute = $time['minute'] ? (int)$time['minute'] : (int)date('i', time()+MSD_ONE_DAY);
		} else {
			$now = time();
			$time || $time = $now+3600*3;
	
			if (($time-$now)<3600) {
				$time = $now + 3600*3;	
			}
			
			$o_year = date('Y', $time);
			$o_month = date('m', $time);
			$o_day = date('d', $time);
			$o_hour = date('H', $time);
			$o_minute = date('i', $time);
		}

		$year = array(
			date('Y') => date('y'), 
			date('Y', time()+3600*24*365) => date('y', time()+3600*24*365)	
			);
		$month = array();
		for ($i=1;$i<=12;$i++) {
			$m = $i<10 ? '0'.$i : $i;
			$month[$i] = $m;
		}
		
		$day = array();
		for($i=1;$i<=31;$i++) {
			$d = $i<10 ? '0'.$i : $i;
			$day[$i] = $d;
		}
		
		$hour = array();
		for($i=0;$i<24;$i++) {
			if ($i<=2 || $i>=10) {
				$h = $i<10 ? '0'.$i : $i;
				$hour[$i] = $h;
			}
		}
		
		$minute = array();
		for ($i=0;$i<60;$i+=10) {
			$m = $i<10 ? '0'.$i : $i;
			$minute[$i] = $m;
		}
		
		$html .= $this->view->Select($year, 'order_pre_year', $o_year, " onchange=\"return CPreTime('pre_year', this.value);\"").'年';
		$html .= $this->view->Select($month, 'order_pre_month', $o_month, " onchange=\"return CPreTime('pre_month', this.value);\"").'月';
		$html .= $this->view->Select($day, 'order_pre_day', $o_day, " onchange=\"return CPreTime('pre_day', this.value);\"").'日';
		$html .= '<br />';
		$html .= $this->view->Select($hour, 'order_pre_hour', $o_hour, " onchange=\"return CPreTime('pre_hour', this.value);\"").'时';
		$html .= $this->view->Select($minute, 'order_pre_minute', $o_minute, " onchange=\"return CPreTime('pre_minute', this.value);\"").'分';
		$html .= "
		<script type='text/javascript'>
			var LAST_PRE_YEAR = '".$o_year."';
			var LAST_PRE_MONTH = '".$o_month."';
			var LAST_PRE_DAR = '".$o_day."';
			var LAST_PRE_HOUR = '".$o_hour."';
			var LAST_PRE_MINUTE = '".$o_minute."';
		</script>
		";
		
		return $html;
	}
	
	/**
	 * 数字到英文的转换（用于跟css做匹配）
	 * 
	 * @param unknown_type $i
	 */
	public function Numbertoword($i)
	{
		switch ($i) {
			case 3:
			case '3':
				$result = 'thr';
				break;
			case 4:
			case '4':
				$result = 'for';
				break;
			case 2:
			case '2':
				$result = 'tow';
				break;
			default:
				$result = 'one';
				break;
		}
		
		return $result;
	}
	
	/**
	 * 根据OrderGuid获得hash
	 * 
	 * @param unknown_type $OrderGuid
	 */
	public function Ohash($OrderGuid)
	{
		return Msd_Waimaibao_Order::OHash($OrderGuid);
	}
	
	/**
	 * 米到公里的转换
	 * 
	 * @param unknown_type $metres
	 */
	public function Km($metres)
	{
		$kms = '';
		$kms = sprintf('%01.2f', ((int)$metres/1000)).'km';
		if ($kms<=0) {
			$kms = '--';
		}
	
		return $kms;
	}
	
	/**
	 * 显示文章阅读数
	 * 
	 * @param unknown_type $data
	 */
	public function Articleviews($data)
	{
		$cacher = &Msd_Cache_Remote::getInstance();
		$cacheKey = 'article_views_'.$data['ArticleId'];
		$tmp = $cacher->get($cacheKey);
		$views = (int)$tmp['views'];
	
		if (!$views) {
			$views = $data['Views'];
		}
	
		return $views;
	}
}
