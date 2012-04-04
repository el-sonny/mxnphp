<?php
abstract class component extends controler{
	public function __construct($controler,$params){
		$this->controler = $controler;
		$this->config = $this->controler->config;
		$this->init($params);
	}
	abstract function init($params);
}
?>