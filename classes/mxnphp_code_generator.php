<?php
abstract class mxnphp_code_generator{
	public function mxnphp_code_generator($table,$config){
		$this->table = new $table();
		$this->config = $config;
		$this->load_texts($config->lang);
	}
	private function load_texts(){
		include $this->config->mxnphp_dir.'/langs/'.$this->config->lang."/crud_templates.php";
	}	
	abstract function create();
}
?>