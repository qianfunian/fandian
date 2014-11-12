<?php

/**
 * 投票调查
 * 
 * @author pang
 *
 */
class Msd_Votes
{
	protected static $instance = array();
	protected $vid = 0;
	protected $vote = array();
	protected $choices = array();
	
	protected function __construct($vid)
	{
		$this->vid = (int)$vid;
		if ($this->vid>0){
			$this->getVote();
		}
	}	
	
	public function __get($key)
	{
		$result = false;
		
		if (isset($this->vote[$key])) {
			$result = &$this->vote[$key];
		}
		
		return $result;
	}
	
	public function __set($key, $val)
	{
		$this->vote[$key] = $val;
		
		return true;
	}
	
	public static function &getInstance($vid)
	{
		if (!isset(self::$instance[$vid])){
			self::$instance[$vid] = new self($vid);
		}
		
		return self::$instance[$vid];
	}
	
	public static function &cacheGet($vid)
	{
		$data = array();
		$cacher = &Msd_Cache_Remote::getInstance();
		$key = 'vote_'.$vid;
		if (!$data) {
			$obj = self::getInstance($vid);
			$vote = $obj->getVote();
			$choices = $obj->getChoices();
			
			$data = array(
				'vote' => $vote,
				'choices' => $choices	
				);
			$cacher->set($key, $data);
		}
		
		return $data;
	}
	
	public static function &getModuleVotes($Module)
	{
		$votes = array();
		$cacher = &Msd_Cache_Remote::getInstance();
		$key = 'mvotes_'.md5($Module);
		$votes = $cacher->get($key);
		if (!$votes) {
			$pager = array(
				'page' => 1,
				'limit' => 99,
				'skip' => 0	
				);
			$data = &Msd_Dao::table('votes')->search($pager, array(
				'Module' => $Module
				), array());
			
			$cacher->set($key, $data);
			$votes = &$data;
		}
		
		return $votes;
	}
	
	public function getVote()
	{
		$info = &Msd_Dao::table('votes')->get($this->vid);
		if ($info){
			$this->vote = &$info;
		}
		
		return $this->vote;
	}
	
	public function getChoices()
	{
		if (!$this->choices) {
			$this->choices = &Msd_Dao::table('vote/choices')->getChoices($this->vid);
		}
		
		return $this->choices;
	}
	
	public function addChoice($ChoiceId)
	{
		
	}
}