<?php
class main extends controler{
	public function main($config,$security){
		$this->config = $config;
		$this->security = $security;
		$this->dbConnect();
	}
	public function index(){
		//$this->include_theme("index","index");
		$creator = new mxnphp_main_code_generator('user',$this->config);
		$creator->create();
	}
	
}
?>