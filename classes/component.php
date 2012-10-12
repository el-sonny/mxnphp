<?php
abstract class component extends controler{
	public function __construct(&$controler,$params){
		$this->controler = &$controler;
		$this->config = $this->controler->config;
		$this->init($params);
	}
	
	protected function init($params){
		if(isset($params) && $params){
			foreach($params as $key => $value){
				$this->params->$key = $value;
			}
		}
	}
}
?>