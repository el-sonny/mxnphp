<?php
function __autoload($class_name){
	global $config_name;
	global $__mxnphp_classes_loaded__;
	$config = new $config_name();
	$folders = array(
		$config->document_root."/controlers/controler.",
		$config->document_root."/components/",
		$config->mxnphp_dir."/classes/components/",
		$config->mxnphp_dir."/classes/",
		$config->document_root."/models/model.",
		$config->document_root."/library/"
	);
	$i = 0;
	$size = count($folders);
	do{
		$file = $folders[$i].$class_name.".php";
		switch($i){
			case 0:
				$__mxnphp_classes_loaded__[$class_name] = "controller";
				break;
			case 4:
				$__mxnphp_classes_loaded__[$class_name] = "model";
				break;
			case 5:
				$__mxnphp_classes_loaded__[$class_name] = "library";
				break;
			
		}
		$i++;
	}while(!file_exists($file) && $i<$size);
	if(file_exists($file)){
		include_once $file;
	}else{
		//var_dump($file);
	}
	
}
?>