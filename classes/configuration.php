<?php 
class configuration{
	protected function configuration(){
		//Site Configuration
		$this->site_name =>  'mxnphp';
		$this->http_address => 'http://localhost/';
		$this->document_root => $_SERVER['DOCUMENT_ROOT'];
		//Database Configuration
		$this->db_host => 'localhost';
		$this->db_name => 'mxnphp';
		$this->db_user => '';
		$this->db_pass => '';
		//Software Configuration
		$this->user_class => 'user';
		$this->theme => 'default';
		$this->default_controler => 'main';
		$this->default_action => 'index';
	}
}
?>