<?php
//Load main Framework Files
require_once $_SERVER['DOCUMENT_ROOT']."system/scripts/mxnphp_core.php";
//Generate Main Template
$main_template = $_SERVER['DOCUMENT_ROOT']."/system/templates/controlers/main-controler.mxnt";
$template = new template($main_template);
$template->render();
$result = $template->save_render($_SERVER['DOCUMENT_ROOT']."/controlers/".$mxnphp_root_controler.".php");
if($result > 1){
	echo "Main Controler Generated<br>";
}
if($mxnphp_security){
	//Generate Security Template
	$file = $_SERVER['DOCUMENT_ROOT']."/system/templates/controlers/security-controler.mxnt";
	$template = new template($file);
	$template->render();
	$result = $template->save_render($_SERVER['DOCUMENT_ROOT']."/controlers/security.php");
	
	if($result > 1){
		echo "Security Controler Generated<br>";
	}

}
?>