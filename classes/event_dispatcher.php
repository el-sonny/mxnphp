<?php
abstract class event_dispatcher{
	private $listeners = array();
	public function add_event_listener($event,$listener){
		if(!isset($this->listeners[$event])) $this->listeners[$event] = array();
		$this->listeners[$event][count($this->listeners[$event])] = $listener;
	}
	public function dispatch_event($event,$event_object){
		if(isset($this->listeners[$event])){
			foreach($this->listeners[$event] as $listener){
				$listener->$event($event_object);
			}
		}
	}
}
?>