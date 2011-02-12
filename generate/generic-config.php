<?php 
class {:site_name:}_configurations implements configurations{
	private $site_variables;
	protected function set_default_variables(){
		$site_variables = array(
			"site_name" =>  'mxnphp',
			"default_controler" => 'security',
			"root_controler" => 'mxnphp',
			"db_host" => 'localhost',
			"db_name" => 'mxnphp',
			"db_user" => '',
			"db_pass" => '',
			"http_address" => 'http://localhost/',
			"document_root" => $_SERVER['DOCUMENT_ROOT'],
			"user_table" => 'user',
			"theme" => 'default'
		);
	}
}
?>