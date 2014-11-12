<?php

/**
 * 
 * @author pang
 * @email pang@fandian.com
 *
 */
class Api_CoversController extends Msd_Controller_Api
{
	public function init()
	{
		parent::init();
		
		Zend_Controller_Front::getInstance()->setParam('noViewRenderer', 0);
	}
	
	/**
	 * 读取启动封面
	 * 
	 */
	public function indexAction()
	{
		$this->xmlRoot = 'covers';
		$config = &Msd_Config::appConfig();
		
		$covers = $config->api->covers->toArray();
		$dms = explode('|', $config->api->cover_dimensions);
		
// 		foreach ($covers as $cover) {
// 			$data = explode('|', $cover);
// 			$ims = array();
// 			$lu = new DateTime($data[2]);
// 			foreach ($dms as $dm) {
// 				$ims[] = array(
// 					'cover_image' => array(
// 						'image_url' => $this->staticUrl.$config->api->cover_url.$dm.'/'.date('Ymd', $lu->getTimestamp()).'_'.$data[4],
// 						'dimension' => $dm
// 						)	
// 					);
// 			}
			
			
// 			$this->output[$this->xmlRoot][] = array(
// 				'cover' => 	array(
// 					'start_time' => $data[0],
// 					'end_time' => $data[1],
// 					'last_update' => $data[2],
// 					'delay' => $data[5],
// 					'link_url' => $data[3],
// 					'cover_images' => $ims
// 					)
// 				);
// 		}

		$this->output();
	}
}