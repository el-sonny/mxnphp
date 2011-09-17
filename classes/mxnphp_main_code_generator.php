<?php
class mxnphp_main_code_generator extends mxnphp_code_generator{
	public function create(){
		$template_creator = new mxnphp_template_generator($this->table,$this->config);
		$template_creator->create();
	}
}
?>