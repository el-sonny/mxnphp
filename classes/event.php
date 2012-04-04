<?php
class event{
	function event($values = array()){
		foreach($values as $key => &$value){
			$this->$key = &$value;
		}
	}
}

?>