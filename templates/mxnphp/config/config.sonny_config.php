<?php
class sonny_config{
	public function sonny_config(){
		//Site Configuration
		$this->site_name =  'MxnphpTest';
		$this->http_address = 'http://localhost/';
		$this->document_root = $_SERVER['DOCUMENT_ROOT'];
		$this->mxnphp_dir = "c:/wamp/www/mxnphp/";
		$this->lang = "en";
		//Security configuration
		$this->secured = false;
		//$this->security_controler = "";
		//$this->session_name = "";
		//Database Configuration
		$this->db_host = 'localhost';
		$this->db_name = 'vid_manager';
		$this->db_user = 'root';
		$this->db_pass = '';
		//Software Configuration
		//$this->user_class = 'user';
		$this->theme = 'mxnphp';
		$this->default_controler = 'main';
		$this->default_action = 'index';		
	}
}
?>