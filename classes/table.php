<?php
/**
* Class abstract table
*
* Esta clase es el modelado de la base de datos 
* que va a cargar el framework.
* 
*/
abstract class table{
	public $objects;
	public $fields;
	public $table_name;
	public $date_formats;
	public $has_many = array();
	public $key = "id";
	public $debug = false;
	public $tables;
	public $clause = "";
	public $search_clause;
	public $get_id = false;
	public $insert_id = true;
	public $md5;
	public $limit;
	public $distinct = false;
	
	/**
	* Funcion table
	* 
	* Esta funcion es un constructor de  
	* la variable fields en la posicion $key
	* que es el parametro de esta funcion
	* 
	* @param string $key esta variable sera  
	* un parametro para la posicion de fields
	*
	*/
	
	
	/*
	 
	 Table Class
	
	public function insert();
	public function update();
		
	//Other functions
	
	public function fetchAll();
	public function query();
	public function exec_query();
	public function fetchObjects();
	public function getchArrays();
	
	
	public function query();
	*/
	
	
	public function table($key = 0){
		$this->info();
		$this->fields[$this->key] = $key;
		$this->{$this->key} = $key;
	}
	/**
	* Funcion create
	* 
	* Inserta los datos en la BD
	* 
	* Esta funcion se encarga de llenar los
	* campos de las tablas de la base de datos
	*
	*@param string $fields parametro de campos de la BD.
	*
	*@param boolean $values si values es falso crea un vector 
	* con los valores.
	*/
	
	public function create($fields,$values = false){
		$fields = explode(",",$fields);
		//$values = explode(",",$values);
		if(!$values){
			$i = 0;
			foreach($fields as $field){
				$values[$i++] = $_POST[$field."_input"];
			}
		}
		$values = $this->format_input($fields,$values);
		$values = $this->clean($values);
		
		$sql ="INSERT INTO ".$this->table_name." (";
		for($i=0;$i<count($fields);$i++){
			$sql = $sql."`".$fields[$i]."`, ";
		}
		$sql = substr($sql,0,-2).") VALUES (";
		for($i=0;$i<count($values);$i++){
			if(isset($this->md5[$fields[$i]])){
				$sql = $sql."MD5(".$values[$i]."), ";
			}else{
				$sql = $sql."".$values[$i].", ";
			}
		}
		$sql = substr($sql,0,-2).");";
		if($this->debug)
			echo $sql."</br>";
		$result = $this->execute_sql($sql);
		if($result){
			$i = 0;
			foreach($fields as $field){
				$this->{$field} = substr($values[$i++],1,-1);
			}
		}
		if($this->insert_id)
			$this->id = mysql_insert_id();
		return $result;
	}
	/**
	* Funcion read
	* 
	* Lee los datos en la BD
	* 
	* Esta funcion se encarga de leer los
	* campos de las tablas de la base de datos
	*
	*@param string $fields parametro de campos de la BD.
	*
	*/

	public function get_tables($fields){
		$fields = explode(",",$fields);
		$this->object_format($fields);
		$tables = $this->tables;
		$n = count($tables);
		$tables[$n] = $this->table_name;
		return $tables;
	}

	public function read($fields){
		$fields = explode(",",$fields);
		$fields2 = $this->object_format($fields);
		$distinct = $this->distinct ? "DISTINCT " : "";
		$sql = "SELECT $distinct";
		for($i=0;$i<count($fields2);$i++){
			$sql = $sql.$fields2[$i].", ";
		}
		$sql = substr($sql,0,-2);
		if(count($this->tables))
			$tables = ",".implode(",",$this->tables);
		else
			$tables = "";
		if(isset($this->search_clause)){
			$clause = $this->search_clause;
			$limit = isset($this->limit) && $this->limit ? " LIMIT ".$this->limit : "";
			$limit = isset($this->order_by) ? " ORDER BY ".$this->order_by.$limit : $limit;
		}else{
			$clause = $this->table_name.".".$this->key." = '".$this->fields[$this->key]."' ";
			$limit = " LIMIT 1";
		}
		$sql = $sql." FROM ".$this->table_name." ".$tables." WHERE ".$clause.$this->clause.$limit;
		if(count($fields2)){
			if($this->debug)
				echo $sql."<br/>";	
			$result_sql = mysql_query($sql);
			$multiple_results = $result_sql && mysql_num_rows($result_sql) >= 1 && isset($this->search_clause);
			if($multiple_results){
				$j =0;
				while($row = mysql_fetch_row($result_sql)){
					$result_array[$j] = new $this(0);
					for($i=0;$i<count($row);$i++){
						$fields[$i] = trim($fields[$i]);
						if(preg_match_all('/(\w+)\s*=>\s*(\w+)/i', $fields[$i], $result, PREG_PATTERN_ORDER)){
							if(isset($this->objects[$result[1][0]])){
								$obj = $this->objects[$result[1][0]];
								if(!isset($result_array[$j]->$obj)){
									$result_array[$j]->$obj = new $obj(0);
								}
								$result_array[$j]->$obj->$result[2][0] = $row[$i];
							}
						}else if(isset($this->objects[$fields[$i]])){
							$result_array[$j]->{$fields[$i]} = new $this->objects[$fields[$i]]($row[$i]);
						}else{
							$result_array[$j]->{$fields[$i]} = $row[$i];
						}
						$result_array[$j]->fields[$fields[$i]] = $row[$i];
						
					};
					$j++;
				}
				return $result_array;
			}else{
				if($result_sql){
					$row = mysql_fetch_row($result_sql);
					for($i=0;$i<count($row);$i++){
						$fields[$i] = trim($fields[$i]);
						if(preg_match_all('/(\w+)\s*=>\s*(\w+)/i', $fields[$i], $result, PREG_PATTERN_ORDER)){
							
							if(isset($this->objects[$result[1][0]])){
								
								$obj = $this->objects[$result[1][0]];
								if(!isset($this->{$obj})){
									$this->{$this->objects[$result[1][0]]} = new $obj(0);
									
								}
								
								$this->{$result[1][0]}->{$result[2][0]} = $row[$i];
							}
						}else if(isset($this->objects[$fields[$i]])){
							$this->{$fields[$i]} = new $this->objects[$fields[$i]]($row[$i]);
						}else{
							$this->{$fields[$i]} = $row[$i];
						}
						$this->fields[$fields[$i]] = $row[$i];
					};
					//return $row;
				}else{
					if($this->debug)
						echo "Mysql Error :".mysql_error();
					return false;
				}
				
			}
		}
		$i = 0;
		$has_many = false;
		foreach ($fields as $field){
			if(preg_match_all('/(\w+)\s*=>\s*(.+)/i', $field, $result, PREG_PATTERN_ORDER)){
				if(isset($this->has_many[$result[1][0]])){
					if(isset($has_many_fields[$result[1][0]])){
						///echo $result[2][0];
						$has_many_fields[$result[1][0]] = $has_many_fields[$result[1][0]].$result[2][0].",";
					}else{
						$has_many_fields[$result[1][0]] = $result[2][0].",";
					}
					$has_many = true;
				};				
			}
			$i++;
		};
		if($has_many){
			foreach($this->has_many as $key => $value){
				if(isset($has_many_fields[$key])){
					//var_dump( $fields);
					//echo $value;
					$fields = substr($has_many_fields[$key],0,-1);
					$object = new $value();
					if($this->debug)
						$object->debug = true;
					$llave = isset($this->has_many_keys[$key]) ? $this->has_many_keys[$key]:get_class($this);
					$object->search_clause = $object->table_name.".".$llave." = '".$this->{$this->key}."'";
					if(isset($this->has_many_limits[$key])){$object->limit = $this->has_many_limits[$key];}
					if(isset($this->has_many_clause[$key])){$object->search_clause = $object->search_clause." AND ".$this->has_many_clause[$key];}
					if(isset($this->has_many_order_by[$key])){$object->order_by = $this->has_many_order_by[$key];}
					
					$this->{$key} = $object->read($fields);
				}
			}
		}
	}	
	/**
	* Funcion update
	* 
	* Actualiza los datos en la BD
	* 
	* Esta funcion se encarga de actualizar los
	* campos de las tablas de la base de datos
	*
	*@param string $fields parametro de campos de la BD.
	*
	*@param boolean $values si values es falso crea un vector 
	* con los valores si es true limpia la variable.
	*/
	
	public function update($fields,$values=false){
		$fields = explode(",",$fields);
		if(!$values){
			$i = 0;
			foreach($fields as $field){
				$values[$i++] = $_POST[$field."_input"];
			}
		}
		$values = $this->clean($values);
		$sql = "UPDATE ".$this->table_name." SET ";
		for($i=0;$i<count($fields);$i++){
			if(isset($this->md5[$fields[$i]])){
				$sql .= "`".$fields[$i]."` = MD5( ".$values[$i]." ), ";
			}else{
				$sql .= "`".$fields[$i]."` = ".$values[$i].", ";
			}
		}
		$sql = substr($sql,0,-2);
		$sql = $sql." WHERE `".$this->key."` = '".$this->fields[$this->key]."' LIMIT 1;";
		if($this->debug)
			echo $sql."<br/>";
		return $this->execute_sql($sql);
	}
	
	/**
	* Funcion destroy
	* 
	* Elimina datos de las tablas de la base de datos
	* 
	* Esta funcion se encarga de eliminar registros de 
	* tablas en la base de datos
	*
	*
	*/
	
	public function destroy(){
		$sql = "DELETE FROM ".$this->table_name." WHERE ".$this->key." = '".$this->fields[$this->key]."' LIMIT 1;";
		if($this->debug)
			echo $sql."<br/>";
		return $this->execute_sql($sql);
	}

	/**
	* Funcion search_clause
	* 
	* Sets the search_clause variable
	* 
	*/
	public function search_clause($field,$value,$comparator = '=',$wildcards = false){
		$value = mysql_real_escape_string($value);
		$w = $wildcards ? "%" : "";
		return $this->search_clause = "$field $comparator '$w$value$w'";		
	}
	
	protected function execute_sql($sql){
		$result = mysql_query($sql);
		if($this->get_id)
			$this->{$this->key} = mysql_insert_id();
		return $result;
	}
        public function ExecuteReturnObject($query){
		$result = $this->execute_sql($query);
                $i = 0;
                $records = array();
		if($result && mysql_num_rows($result) >= 1){
			while ($row = mysql_fetch_assoc($result)) {
				foreach($row as $key=>$value){
					$records[$i]->$key = $value;
				}
				$i++;
			}
		}
		return $records;
	}
	protected function clean($fields){
		for($i=0;$i<count($fields);$i++){
			$fields[$i] = str_replace("(;)",",",mysql_real_escape_string(trim($fields[$i])));
			if($fields[$i] == ""){
				$fields[$i] = "NULL";
			}else{
				$fields[$i] = "'$fields[$i]'";
			}
		}
		return $fields;
	}
	
	protected function object_format($fields){
		$i = 0;
		$j = 0;
		$fieldsr = array();
		foreach($fields as $field){
			$field = trim($field);
			if(preg_match_all('/(\w+)\s*=>\s*(\w+)/i', $field, $result, PREG_PATTERN_ORDER)){	
				if(isset($this->objects[$result[1][0]])){
					$mother_field = $result[1][0];
					$child_field = $result[2][0];
					//echo $this->objects[$mother_field];
					$child_table = new $this->objects[$mother_field](0);
					$child_table_name = $child_table->table_name;
					$child_table_key = $child_table->key;
					$fieldsr[$i] = $child_table_name.".".$child_field;
					$this->clause = $this->clause." AND $child_table_name.$child_table_key = ".$this->table_name.".".$mother_field;
					$this->tables[$result[1][0]] = $child_table_name;
				}else
					$i--;	
			}else{
				$fieldsr[$i] = $this->table_name.".".$field;
				if(isset($this->date_formats[$field])){
					$fieldsr[$i] = "DATE_FORMAT($fieldsr[$i], '".$this->date_formats[$field]."')";
				}
			}
			$i++;
		}
		return $fieldsr;
	}
	protected function format_input($fields,$values){
		return $values;
	}
	public function __toString(){
		return $this->id;
	}
	public function max_id(){
		$sql = "SELECT MAX({$this->key}) FROM ".$this->table_name.";";		
		$result = mysql_query($sql);
		if($this->debug){
			echo $sql."<br/>";
		}
		if($result){
			$row = mysql_fetch_row($result);
			$max_id = $row[0];
		}else{
			$max_id = 0;
			if($this->debug)
				echo "Mysql Error :".mysql_error()."<br/>";
		}		
		return $this->{$this->key} = $max_id;
	}
	public function select(){
		$select = new mxnphp_Db_select($this);
		return $select->select();
	}
	public function next_id(){
		$next_id = false;
		$sql = "SHOW TABLE STATUS LIKE '{$this->table_name}'";
		$result = mysql_query($sql);		
		if($this->debug){
			echo $sql."<br/>";
		}
		if($result){
			$row = mysql_fetch_array($result);
			$next_id = $row['Auto_increment'];
		}else{
			if($this->debug) echo "Mysql Error :".mysql_error()."<br/>";
		}		
		return $next_id;
	}
	public function quote($attr = false){
		if($attr){
			if(is_string($attr)){
				$attr = str_replace("\\","\\\\",$attr);
				$attr = str_replace("'","\\'",$attr);
				return $attr;
			}elseif(is_int($attr)){
				return $attr;
			}elseif(is_array($attr)){
				$result = array();
				foreach($attr as $key => $a){
					$result[$key] = $this->quote($a);
				}
				return $result;
			}
		}else{
			return false;
		}
	}
}
?>