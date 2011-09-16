<?php
class mxnphp_orm{
	public $table;
	private $query;
	private $objects;
	private $search_tables;
	private $search_clauses;
	private $search_fields ;
	public function mxnphp_orm($query,$table){
		$this->query = $query;
		$this->table = $table;
		$this->scan();
		$this->translate();
		$this->execute();
		$this->clean();
	}
	private function scan(){
		$this->objects = explode(",",$this->query);
		$i = 0;
		foreach($this->objects as $object){
			$this->objects[$i++] = explode("=>",$object);
		}
	}
	private function translate(){
		$object_counter = $search_fields_counter = $search_clause_counter = $search_table_counter = 0;
		$this->search_tables[$search_table_counter++] = $this->table->table_name;
		if(isset($this->table->search_clause))
			$this->search_clauses[$search_clause_counter++] = $this->table->search_clause;
		else
			$this->search_clauses[$search_clause_counter++] = $this->table->table_name.'.'.$this->table->key.' = '.$this->table->id;
		//Translate To arrays
		$j = 0;
		foreach($this->objects as $object){
			$levels = count($object); 
			if($levels > 1){
				$top_level = $this->table;
				$i = 0;
				foreach($object as $level){
					if($top_level->objects[$level]){
						$object_name = $top_level->objects[$level];
						if(isset($this->object_pool[$object_name])){
							$new_table = $this->object_pool[$object_name];							
						}else{
							$new_table = new $object_name();
							$this->object_pool[$object_name] = $new_table;							
							$this->insert_into_objects($new_table,$object,$levels,$j,$search_fields_counter,$i);
						}
						$new_clause = $top_level->table_name.".$level = ".$new_table->table_name.".".$new_table->key;
					}else if($top_level->has_many[$level]){					
						$object_name = $top_level->has_many[$level];
						if(isset($this->object_pool[$object_name])){
							$new_table = $this->object_pool[$object_name];
						}else{
							$new_table = new $object_name();
							$this->object_pool[$object_name] = $new_table;
							$this->insert_into_objects($new_table,$object,$levels,$j,$search_fields_counter,$i);
						}
						$sub_key = isset($top_level->has_many_keys[$level]) ? $top_level->has_many_keys[$level] : get_class($top_level);
						$new_clause = $top_level->table_name.".".$top_level->key." = ".$new_table->table_name.'.'.$sub_key;
					}else if($i == ($levels - 1)){
						$this->search_fields[$search_fields_counter++] = $top_level->table_name.'.'.$level;
						break;
					}else{
						echo "youve got an error: $level not recognized";
						break;
					}
					$i++;
					$top_level = $new_table;
					if(!in_array($top_level->table_name,$this->search_tables))
						$this->search_tables[$search_table_counter++] = $top_level->table_name;
					if(!in_array($new_clause,$this->search_clauses))
						$this->search_clauses[$search_clause_counter++] = $new_clause;
				}
			}else{
				$this->search_fields[$search_fields_counter++] = $this->table->table_name.'.'.$object[0];
			}
			$j++;
			
		}
		// var_dump($this->search_tables);
		// var_dump($this->search_clauses);
		//var_dump($this->search_fields);
		//var_dump($this->objects);
		
		
		//Translate From arrays To SQL
		$fields_clause = implode($this->search_fields,', ');
		$tables_clause = implode($this->search_tables,', ');
		$caluses_clause = implode($this->search_clauses,' AND ');		
		$this->translation = "SELECT $fields_clause FROM $tables_clause WHERE $caluses_clause";
	}
	private function execute(){
		//var_dump($this->query);
		//var_dump($this->translation);
		$result_sql = mysql_query($this->translation);
		$j = 0;
		while($row = mysql_fetch_row($result_sql)){
			foreach($row as $key => $field){
				$current_object = $this->objects[$key];
				$levels = count($current_object); 
				if($levels > 1){
					$top_level =& $this->table;
					foreach($current_object as $k => $level){
						if($top_level->objects[$level]){
							if(!isset($top_level->$level))
								$top_level->$level = new $top_level->objects[$level]();
							$new_level =& $top_level->$level;
						}else if($top_level->has_many[$level]){	
							if(!isset($top_level->{$level}[$j]))
								$top_level->{$level}[$j] = new $top_level->has_many[$level]();
							$new_level =& $top_level->{$level}[$j];
						}else if($k == ($levels - 1)){
							$top_level->$level = $field;
							break;
						}
						
						$top_level =& $new_level;
					}
				}else{
					$this->table->$current_object[0] = $field;
				}
				
			}
			$j++;
		}	
	}
	private function clean(){
		foreach($this->objects as $current_object){
			$levels = count($current_object); 
			if($levels > 1){
				$top_level =& $this->table;
				foreach($current_object as $k => $level){
					if($top_level->objects[$level]){
						$new_level =& $top_level->$level;
					}else if($top_level->has_many[$level]){	
						$top_level->{$level} = array_unique($top_level->{$level});
						$new_level =& $top_level->{$level}[0];
					}else if($k == ($levels - 1)){
						break;
					}
					$top_level =& $new_level;
				}
			}
		}
	}
	private function insert_into_objects($new_table,$object,$levels,&$counter,&$search_fields_counter,$current_level){
		$new_clause = $new_table->table_name.".".$new_table->key;
		if(!isset($this->search_fields) || !in_array($new_clause,$this->search_fields)){
			$this->search_fields[$search_fields_counter++] = $new_clause;
			$new_object = $object;
			array_splice($new_object,$current_level+1);
			$new_object[$current_level+1] = $new_table->key;
			array_splice($this->objects,$counter,0,array($new_object));
			$counter++;			
		}
	}
	
}
?>