<?php

class VoteController extends Msd_Controller_Default
{
	public function submitAction()
    {
    	$p = $this->getRequest()->getPost();
		$vid = (int)$p['vid'];
		$choices = $p['vote_'.$vid];
		
		$sess = &Msd_Session::getInstance();
		$vkey = 'vk_'.$vid;
		$lastVote = (int)$sess->get($vkey);
		
		if ($lastVote) {
			$this->view->vMessage = '对不起，你已经投票过了，不能重复投票';
		} else {
			$vote = Msd_Cache_Loader::Vote($vid);
	
			if ($vote['vote']['AutoId']) {
				$vcTable = &Msd_Dao::table('vote/choices');
				if ($vote['vote']['IsMultiChoice']) {
					$choices = (array)$choices;
					foreach ($choices as $choice) {
						$choice = (int)$choice;
						if ($choice) {
							$vcTable->addChoice($choice);
						}
					}
				} else {
					$choice = (int)$choices;
					$vcTable->addChoice($choice);
				}
			}
			
			$sess->set($vkey, time());
			$this->view->vMessage = '投票成功！\n\n感谢您的参与！';
		}
		
		$this->view->vid = $vid;
    }
}