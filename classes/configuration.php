<?php 
abstract class mxnphp_configuration{
	protected function merge_with($name){
		require "config.$name.php";
		$config = new $name;
		$this = (mxnphp_configuration)array_merge((array)$this, (array) $config);
	}
}
?>