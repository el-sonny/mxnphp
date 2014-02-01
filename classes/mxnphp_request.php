<?php
class mxnphp_request{
	public function __construct(){
	}
	public function get_controller(){
		$config = mxnphp_registry::get('__mxnphp_config__');
		$controler_name = isset($_GET['controler']) ? $_GET['controler'] : $config->default_controler;
		$controler_name = str_replace("-","_",$controler_name);
		return $controler_name;
	}
	public function get_action(){
		$config = mxnphp_registry::get('__mxnphp_config__');
		$action = isset($_GET['action']) ? $_GET['action'] : $config->default_action;
		$action = str_replace("-","_",$action);
		return $action;
	}
	protected function clean_input($input){
		return mysql_real_escape_string(trim($input));
	}
	static function get($variable){
		return isset($_GET[$variable]) ? mysql_real_escape_string(trim($_GET[$variable])) : false;
	}
	static function post($variable){
		return isset($_POST[$variable]) ? mysql_real_escape_string(trim($_POST[$variable])) : false;
	}
	static function get_request($variable){
		if(self::get($variable)){
			$val = self::get($variable);
		}else if(self::post($variable)){
			$val = self::post($variable);
		}else{
			$val = false;
		}
		return $val;
	}
}
?>