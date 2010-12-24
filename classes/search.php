<?php
abstract class search{
	public $query;
	public $words;
	public $results;
	public $table;
	public $key;
	public $sort_by;
	public $debug;
	public $fields;
	public $obj;
	public $dateFormats;
	public $clause;
	public $search = true;
	public $sort;
	function search($query){
		
	}
	function parse(){
		$i = 0;
		preg_match_all('/(\b[\w]+:|\b[\w-]+\b|&|;)/i', $this->query, $result, PREG_PATTERN_ORDER);
		foreach($result[1] as $res)
			$this->words[$i++] = trim($res);
	}
	function simple($property,$search_types){
		if($this->query != ""){
			$property = explode(",",$property);
			$search_types = explode(",",$search_types);
			$query = $this->query;
			$table = $this->table;
			$field = $this->get_fields();
			$sql = "SELECT $field FROM $table WHERE ";
			$i = 0;	
			$clause = "";
			for($i=0;$i<count($property);$i++){
				$prop = $property[$i];
				$search_types[$i] = trim($search_types[$i]);
				if($search_types[$i] == "LIKE" || $search_types[$i] == "like" )
					$clause = $clause."$prop LIKE '%$query%' AND ";
				else if($search_types[$i] == "=")
					$clause = $clause."$prop = '$query' AND ";
				$i++;
			}
			$this->clause = substr($clause,0,-4);
			$sql = $sql.$this->clause;
			if(isset($this->sort_by)){
				$sort = "ORDER BY ".$this->sort_by;
			}else
				$sort = "";
			$this->sort = $sort;
			$sql = $sql."$sort;";
			if($this->debug)
				echo $sql;
			if($this->search)
				$this->fetch_results($sql);
			
		}
	}	
	function token($property,$search_types){
		$this->parse();
		if($this->query != ""){
			$words = $this->words;
			$table = $this->table;
			$fields = $this->get_fields();
			$sql = "SELECT $fields FROM $table WHERE ";
			for($j=0;$j<count($property);$j++){
					$prop = $property[$j];
				for($i=0;$i<count($this->words);$i++){
					$word = trim($this->words[$i]);
					if($i > 0)
						$sql = $sql." && ";
					if($search_types[$j] == "LIKE" || $search_types[$j] == "like" )
						$sql = $sql."$prop LIKE '%$query%' AND ";
					else if($search_types[$j] == "=")
						$sql = $sql."$prop = '$query' AND ";		
					$sql = $sql."$property LIKE '%$word%'";
				}
			}
			if(isset($this->sort_by)){
				$sort = "ORDER BY ".$this->sort_by;
			}else
				$sort = "";
			$sql = $sql."$sort;";
			if($this->debug){
				echo $sql;
			}
			$this->fetch_results($sql);
		}
	}
	function search_all($start,$end){
		if(($end - $start) >0){
			$fields = $this->get_fields();
			$table = $this->table;
			$sort = isset($this->sort_by) ? "ORDER BY ".$this->sort_by : "";
			$sql = "SELECT $fields FROM $table $sort LIMIT $start, $end";
			if($this->debug)
				echo $sql;
			$this->fetch_results($sql);
		}
	}
	function clean($fields){
		for($i=0;$i<count($fields);$i++){
			$fields[$i] = addslashes(trim($fields[$i]));
		}
		return $fields;
	}
	function clean_query($query){
		$query = addslashes(trim($query));
		$this->query = $query;
	}
	
	function get_fields(){
		if(isset($this->obj)){
			$field = "";
			foreach($this->fields as $f){
				if(isset($this->date_formats[$f])){
					$field = $field." DATE_FORMAT($f, '".$this->date_formats[$f]."')";
					$field = $field.",";
				}else
					$field = $field.$f.",";
			}
			$field = substr($field,0,-1);
		}else
			$field = $this->key;
		return $field;
	}

	function fetch_results($sql){
		$i = 0;
		$this->results = array();
		$result = mysql_query($sql);
		while($row = mysql_fetch_array($result)){
			if(isset($this->obj)){
				$this->results[$i] = new $this->obj($row[0]);
				foreach($this->fields as $f){
					if(isset($this->date_formats[$f])){
						$this->results[$i]->fields[$f] = $row["DATE_FORMAT($f, '".$this->date_formats[$f]."')"];
						$this->results[$i]->{$f} = $row["DATE_FORMAT($f, '".$this->date_formats[$f]."')"];
					}
					else{
						$this->results[$i]->fields[$f] = $row[$f];
						$this->results[$i]->{$f} = $row[$f];
					}
				}
			}else
				$this->results[$i] = ($row[0]);
			$i++;
		}
	}
}
?>