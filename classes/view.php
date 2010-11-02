<?php
class view{
	public $breaks = true;
	public $bold_labels = true;
	public $table;
	public $obj;
	public $header = 0;
	public $debug = false;
	public $header_size = "h2";
	public $clause = "1";
	function view($obj,$id){
		$this->obj = new $obj($id);
	}
	function print_std($fields,$labels){
		$this->obj->debug = $this->debug;
		$this->obj->read($fields);
		$labels = explode(",",$labels);
		if($this->header){
			preg_match_all('/%(\w+)/i', $this->header, $result, PREG_PATTERN_ORDER);
			echo "<".$this->header_size.">";
			foreach($result[1] as $result){
				echo $this->obj->$result." ";
			}
			echo "</".$this->header_size.">";
		}
		if(!$this->breaks)
			echo "<p>";
		$i = 0;
		$first = true;
		foreach($this->obj->fields as $field){
			if(!$first){
			if($this->breaks) 
				echo "<p>";
			if($this->bold_labels)
				echo "<b>";
		    echo "$labels[$i]";
			if($this->bold_labels)
				echo "</b>";
			echo "$field";
			if($this->breaks) 
				echo "</p>";
			$i++;}
			$first = false;
		}
		if(!$this->breaks)
			echo "</p>";
	}
	function print_table($object,$fields,$headers){
		$headers = explode(",",$headers);
		$i = 0;
		echo "<table><tr>";
		foreach($headers as $header){
			echo "<th>$header</th>";
		}
		echo "</tr>";
		$object = new $object(0);
		$object->search_clause = $this->clause;
		$fieldss= explode(",",$fields);
		$results = $object->read($fields);
		foreach($results as $result){
			echo "<tr>";
			foreach($fieldss as $field){
				echo "<td>{$result->fields[$field]}</td>";
			}
			echo "</tr>";
		}
		echo "</table>";
	}
	
}
?>