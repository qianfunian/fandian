<?php

/**
 * 文章系统（公告、新闻等）
 * 
 * @author pang
 *
 */
class Msd_Article
{
	
	public static function delete($AutoId)
	{
		
	}
	
	/**
	 * 解析文章详情
	 * 
	 * @param Integer $ArticleId
	 */
	public static function &parse($ArticleId)
	{
		$data = array();
		$key = 'article_'.$ArticleId;
		$cacher = Msd_Cache_Remote::getInstance();
		$data = $cacher->get($key);
		$Categories = $cacher->get('Categories');
		
		if (!$data) {
			$data = Msd_Dao::table('article')->get($ArticleId);
			$cacher->set($key, $data);
		}
		
		$data['CategoryName'] = $Categories[$data['CategoryId']];
		
		return $data;
	}
	
	/**
	 * 更新文章阅读数
	 * 
	 * @param Integer $ArticleId
	 */
	public static function updateViews($ArticleId)
	{
		$views = 0;
		$d = self::parse($ArticleId);
		$cacheKey = 'article_views_'.$ArticleId;
		$cacher = &Msd_Cache_Remote::getInstance();
		
		$data = $cacher->get($cacheKey);
		$views = (int)$data['views'];
		
		if ($views<=0) {
			$views = (int)$d['Views']+1;
		} else {
			$views ++;
		}
		
		$cacher->set($cacheKey, array(
				'views' => $views
				));
		Msd_Dao::table('article')->increase('Views', $ArticleId);

		return $views;
	}
}