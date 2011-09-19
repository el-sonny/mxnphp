<?php
class mxnphp_main_code_generator extends mxnphp_code_generator{
	public function create(){
		$template_generator = new mxnphp_template_generator($this->class_name,$this->config);
		$controler_generator = new mxnphp_controler_generator($this->class_name,$this->config);
		$template_generator->create();
		$controler_generator->create();
	}
}
?>