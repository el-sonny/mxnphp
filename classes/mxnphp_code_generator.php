<?php
abstract class mxnphp_code_generator{
	public function mxnphp_code_generator($class_name,$config){
		$this->class_name = $class_name;
		$this->table = new $class_name();
		$this->config = $config;
		$this->load_texts($config->lang);
		$this->errors = array();
	}
	private function load_texts(){
		include $this->config->mxnphp_dir.'/langs/'.$this->config->lang."/crud_templates.php";
	}	
	protected function add_error($error){
		echo $error."<br/>";
	}
	abstract function create();
}
?>