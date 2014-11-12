<?php 

class Msd_Api_Output
{
	public $formats = array('xml', 'json');
	protected $params = array(
											'xml_root' => 'place',
											'format' => 'xml',
											'data' => array(),
											);
	protected $cdata = true;
	protected static $instance = null;
	
	function __destruct()
	{
		$this->clean();
	}
	
	public static function getInstance()
	{
		if (self::$instance==null) {
			self::$instance = new self();
		}
	
		return self::$instance;
	}
	
	public function clean()
	{
		
	}
	
	public function setCdata($cdata)
	{
		$this->cdata = (bool)$cdata;
	}
	
	public function output($format='xml')
	{
		$output = '';
		
		switch ($format) {
			case 'json':
				$output = json_encode((array)$this->params['data']);
				break;
			default:
				$output = '<'.'?xml version="1.0" encoding="UTF-8"?'.'>';
				$output .= $this->params['xml_root'] ? '<'.$this->params['xml_root'].'>' : '';
				$output .= $this->array2Xml($this->params['data'][$this->params['xml_root']]);
				$output .= $this->params['xml_root'] ? '</'.$this->params['xml_root'].'>' : '';
				break;
		}

		return $output;
	}
	
	public function setParam($key, $val=null)
	{
		if (is_array($key)) {
			foreach ($key as $k=>$v) {
				$this->params[$k] = $v;
			}
		} else if ($val==null && array_key_exists($key, $this->params)) {
			unset($this->params[$key]);
		} else {
			$this->params[$key] = $val;
		}		
	}

	/**
	 * 将数组转换成XML
	 *
	 * @param array $data
	 * @return string
	 */
	protected function array2XML(&$data)
	{
		$xml = '';

		if (is_array($data)) {
			foreach ($data as $key=>$value) {
				if (!is_numeric($key)) {
					if (!is_array($value) && trim($value)=='') {
						$xml .= '<'.$key.' />';	
					} else {
						$xml .= '<'.$key.'>';
						$xml .= is_array($value) ? $this->array2XML($value) : $this->wrapXMLData($value);
						$xml .= '</'.$key.'>';
					}
				} else {
					$xml .= $this->array2XML($value);
				}
			}
		} else {
			$xml .= $data;
		}

		return $xml;
	}
	
	/**
	 * 封装XML数据
	 *
	 * @param string $value
	 * @return string
	 */
	protected function wrapXMLData($value)
	{
		if ($value!='' && $this->cdata) {
			if (strpos($value, ']]>')===false) {
				$value = "<![CDATA[".$value."]]>";
			} else {
				$value = '<![CDATA['.str_replace(']]>', '] ] >', $value).']]>';
			}			
		}
		
		return $value;
	}
}
