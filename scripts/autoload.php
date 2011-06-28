<?php
function __autoload($class_name){
	global $config_name;
	$config = new $config_name();
	$folders = array(
		$config->mxnphp_dir."/classes/",
		$config->document_root."/models/model.",
		$config->document_root."/controlers/controler."
	);
	$i = 0;
	$size = count($folders);
	do{
		$file = $folders[$i++].$class_name.".php";
	}while(!file_exists($file) && $i<$size);
	if(file_exists($file)){
		include_once $file;
	}//else
		//echo "class $class_name not found";
}
?>