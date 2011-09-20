<?php
if(isset($_GET['m'])){
	$this->include_template('message','global');
}else if(isset($_GET['e'])){
	$this->include_template('error','global');
}
?>