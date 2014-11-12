<?php

class Files_UploadController extends Msd_Controller_Files
{
	public function init()
	{
		parent::init();
	}
	
	public function indexAction()
	{
		$post = $this->getRequest()->getPost();
		$hash = trim($this->getRequest()->getParam('hash'));
		
		if ($hash && $_FILES['myfile']) {
			$f = Msd_Uploader::Save(array(
					'file' => 'myfile',
					'hash' => $hash,
					'usage' => 'article'
					));
			unset($f['tmp_name']);
		} else {
			$f = array();
		}
		
		Msd_Output::prepareHtml();
		echo "['".implode("','", $f)."']";
		Msd_Output::doOutput();
	}
	
	public function itemAction()
	{
		$post = $this->getRequest()->getPost();
		$ItemGuid = $this->getRequest()->getParam('ItemGuid', '');
		$VendorGuid = $this->getRequest()->getParam('VendorGuid', '');
	
		if ($_FILES['myfile']) {
			$f = Msd_Uploader::SaveItemImage(array(
					'file' => 'myfile',
					'ItemGuid' => $ItemGuid,
					'VendorGuid' => $VendorGuid
			));
	
			if ($f) {
				$ieTable = &Msd_Dao::table('item/extend');
				$iTable = &Msd_Dao::table('item');
	
				$item = $iTable->get($ItemGuid);
				$itemE = $ieTable->get($ItemGuid);
	
				if ($itemE) {
					$ieTable->doUpdate(array(
							'HasLogo' => 1
					), $item['ItemGuid']);
				} else {
					$ieTable->insert(array(
							'ItemGuid' => $ItemGuid,
							'HasLogo' => 1,
							'CityId' => $item['CityId']
					));
				}
			}
		} else {
			$f = false;
		}
		
		$output = '0';
		if ($f) {
			$ImageUrl = Msd_Waimaibao_Item::imageUrl(array(
				'ItemGuid' => $ItemGuid,
				'VendorGuid' => $VendorGuid,
				'HasLogo' => 1,
				'force' => true
				));
			$output = "['".$ItemGuid."', '".$ImageUrl."']";
			
			Msd_Hook::run('NewFileSaved', array(
				'url' => $ImageUrl
				));
		}
	
		Msd_Output::prepareHtml();
		echo $output;
		Msd_Output::doOutput();
	}

	public function itembigAction()
	{
		$post = $this->getRequest()->getPost();
		$ItemGuid = $this->getRequest()->getParam('ItemGuid', '');
		$VendorGuid = $this->getRequest()->getParam('VendorGuid', '');
	
		if ($_FILES['myfile']) {
			$f = Msd_Uploader::SaveItemImage(array(
					'file' => 'myfile',
					'ItemGuid' => $ItemGuid,
					'VendorGuid' => $VendorGuid
			));
		} else {
			$f = false;
		}
		
		$output = '0';
		if ($f) {
			$ImageUrl = Msd_Waimaibao_Item::imageBigUrl(array(
				'ItemGuid' => $ItemGuid,
				'VendorGuid' => $VendorGuid,
				'HasLogo' => 1,
				'force' => true
				));
			$output = "['".$ItemGuid."', '".$ImageUrl."']";

			Msd_Hook::run('NewFileSaved', array(
			'url' => $ImageUrl
			));
		}
	
		Msd_Output::prepareHtml();
		echo $output;
		Msd_Output::doOutput();
	}

	//保存我要上封面头像到服务器
	public function activeAction()
	{
		if ($_FILES['myfile']) {
			$f = Msd_Uploader::saveHeadPhotoImage();
		}else
		{
			$f=0;
		}
		echo $f;
		exit;
	}
	
	public function itemspecialAction()
	{
		$post = $this->getRequest()->getPost();
		$ItemGuid = $this->getRequest()->getParam('ItemGuid', '');
		$VendorGuid = $this->getRequest()->getParam('VendorGuid', '');
	
		if ($_FILES['myfile']) {
			$f = Msd_Uploader::SaveItemSpecialImage(array(
					'file' => 'myfile',
					'ItemGuid' => $ItemGuid,
					'VendorGuid' => $VendorGuid
			));
		} else {
			$f = false;
		}
	
		$output = '0';
		if ($f) {
			$ImageUrl = Msd_Waimaibao_Item::imageSpecialUrl(array(
				'ItemGuid' => $ItemGuid,
				'VendorGuid' => $VendorGuid,
				'HasLogo' => 1,
				'force' => true
				));
			$output = "['".$ItemGuid."', '".$ImageUrl."']";
		
			Msd_Hook::run('NewFileSaved', array(
			'url' => $ImageUrl
			));
		}		
		
		Msd_Output::prepareHtml();
		echo $output;
		Msd_Output::doOutput();
	}
	
	public function vendorAction()
	{
		$post = $this->getRequest()->getPost();
		$VendorGuid = $this->getRequest()->getParam('VendorGuid', '');
		
		if ($_FILES['myfile']) {
			$f = Msd_Uploader::SaveVendorLogo(array(
					'file' => 'myfile',
					'VendorGuid' => $VendorGuid
					));
			
			if ($f) {
				Msd_Dao::table('vendor/extend')->doUpdate(array(
					'HasLogo' => 1	
					), $VendorGuid);
			}
		} else {
			$f = false;
		}
		
		$output = '0';
		if ($f) {
			$ImageUrl = Msd_Waimaibao_Vendor::imageUrl(array(
				'VendorGuid' => $VendorGuid
				));
			$output = "['".$VendorGuid."', '".$ImageUrl."']";
		
			Msd_Hook::run('NewFileSaved', array(
			'url' => $ImageUrl
			));
		}
		
		Msd_Output::prepareHtml();
		echo $output;
		Msd_Output::doOutput();
	}
	
	public function vendorbigAction()
	{
		$post = $this->getRequest()->getPost();
		$VendorGuid = $this->getRequest()->getParam('VendorGuid', '');
		
		if ($_FILES['myfile2']) {
			$f = Msd_Uploader::SaveVendorLogoBig(array(
					'file' => 'myfile2',
					'VendorGuid' => $VendorGuid
					));
		} else {
			$f = false;
		}

		$output = '0';
		if ($f) {
			$ImageUrl = Msd_Waimaibao_Vendor::imageBigUrl(array(
				'VendorGuid' => $VendorGuid
				));
			$output = "['".$ItemGuid."', '".$ImageUrl."']";
		
			Msd_Hook::run('NewFileSaved', array(
			'url' => $ImageUrl
			));
		}
		
		Msd_Output::prepareHtml();
		echo $output;
		Msd_Output::doOutput();
	}
	
}