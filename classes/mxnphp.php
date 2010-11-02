<?php 
class mxnphp{
	public $config;
	public $security;
	public function mxnphp($config = false){
		if(!$config)
			$this->config = new default_config();
		else
			$this->config = $config;
	}
	public function load_model($model_name = "general"){
		$file = $this->config->document_root."/models/model.$model_name.php";
		if(file_exists($file)){
			include_once $file;
		}
	}
	public function load_controler(){
		$controler_name = isset($_GET['controler']) ? $_GET['controler'] : $this->config->default_controler;
		$action = isset($_GET['action']) ? $_GET['action'] : $this->config->default_action;
		if(class_exists($controler_name)){						
			$security = ($this->config->secured) ? new $this->config->security_controler($this->config):false;
			$controler = new $controler_name($this->config,$security);
		//	$controler->config = $this->config;
			if(method_exists($controler,$action)){
				$controler->$action();
			}else{
				echo $controler->default_action($action);
			};
		}else{
			echo "<p>default controler $controler_name does not exist</p>";
			echo "<p><a href='/system/generate/new.php'>generate new site</a></p>";
		}
	}
}
?>