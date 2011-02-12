<?php
//Load main Framework Files
require_once $_SERVER['DOCUMENT_ROOT']."system/scripts/mxnphp_core.php";
$security = $_SERVER['DOCUMENT_ROOT']."/system/templates/default/security/index.mxnt";
$template = new template($security);
$template->render();
$result = $template->save_render($_SERVER['DOCUMENT_ROOT']."/templates/$mxnphp_theme/security/index.mxnt");
if($result > 1){
	echo "Security Template Generated<br>";
}
?>