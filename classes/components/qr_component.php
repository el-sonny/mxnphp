<?php

class qr_component extends component{
	public function init(){
		//include($this->config->mxnphp_dir."/plugins/qrcodeservice/qrcode.class.php");
	}
	public function generate($url){
		$this->generateQRwithGoogle($url);
	}
	function generateQRwithGoogle($url,$widhtHeight ='150',$EC_level='L',$margin='0') {
		 $url = urlencode($url); 
		 echo '<img src="http://chart.apis.google.com/chart?chs='.$widhtHeight.
	'x'.$widhtHeight.'&cht=qr&chld='.$EC_level.'|'.$margin.
	'&chl='.$url.'" alt="QR code" />';
	 }
}
?>	