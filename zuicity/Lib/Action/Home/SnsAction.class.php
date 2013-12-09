<?php
class SnsAction extends BaseAction{
	public function _initialize(){
		parent::_initialize();
		Vendor('Oauth.Oauth2.php');
	}
	public function login(){
		define('BASEPATH','1');
		$name=$this->_get('name');
		$app=C($name);
		include realpath('zuicity/_Core/Extend/Vendor/Oauth/OAuth2.class.php');
		$status=Oauth2::provider($name,$app);
		dump($status);
	}
}