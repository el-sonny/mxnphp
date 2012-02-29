<?php
abstract class component extends controler{
	public function __construct($controler){
		$this->controler = $controler;
		$this->config = $this->controler->config;
		$this->init();
	}
	abstract function init();
}
?>