<?php

class SitemapController extends Msd_Controller_Default
{
	public function indexAction()
    {
		$format = strtolower($this->getRequest()->getParam('format', ''));
		
    	$sitemap = &Msd_Cache_Loader::Sitemap();
		$this->view->sitemap = $sitemap;

		switch ($format) {
			case 'xml':
			case 'xml.gz':
				$this->_xmlSitemap();
				break;
		}
    }
    
    protected function _xmlSitemap()
    {
    	echo $this->view->render('sitemap/xml.phtml');
    	exit(0);
    }
}