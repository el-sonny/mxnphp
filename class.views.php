<?php
class form{
	public $has_files;
	public $debug;
	public $this_shit;
	public $charset;
	public $submit_text;
	public $method;
	public $breaks;
	public $multi_line;
	public $img_src;
	public $has_object = false;
	public $object_id;
	public $values;
	public $header = 0;
	public $header_size = "h2";
	public $obj;
	public $css_class;
	public $css_id;
	public $css_ids;
	public $css_classes;
	public $sql_id;
	public $get_id = false;
	public $submit = "<input type='submit' value='guardar' />";
	public $select;
	function form(){
		$this->files = false;
		$this->charset = "utf-8";
		$this->method = "post";
		$this->multi_line = true;
		$this->img_src = false;
		$this->debug = false;
	}
	function print_form($fields,$labels,$types,$action){
		$class = "";
		$css_id = "";
		if(isset($this->css_ids['submit'])){
			$this->submit = "<input type='submit' id='{$this->css_ids['submit']}' value='guardar' />";
		}
		if(isset($this->css_class))
			$class = "class='".$this->css_class."'";
		if(isset($this->css_id))
			$css_id = "id='".$this->css_id."'";
		if($this->has_object){
			$this->{$this->has_object} = new $this->has_object($this->object_id);
			$this->{$this->has_object}->read($fields);

			foreach($this->{$this->has_object}->fields as $key => $val){
				$this->values[$key] = $val;
			}
		}

		$fields = explode(",",$fields);
		$types = explode(",",$types);
		$labels = explode(",",$labels);
		if($this->has_files){
			$mult = "enctype='multipart/form-data'";
		}else
			$mult = "";

		echo "<form $css_id $class action='$action' method='".trim($this->method)."' accept-charset='".trim($this->charset)."' $mult>";
		if($this->header){
			preg_match_all('/%(\w+)/i', $this->header, $result, PREG_PATTERN_ORDER);
			echo "<".$this->header_size.">";
			foreach($result[1] as $result){
				echo $this->{$this->has_object}->$result." ";
			}
			echo "</".$this->header_size.">";
		}
		if(!$this->multi_line)
			echo "<p>";
		for($i=0;$i<count($fields);$i++){
			$css_id = "";
			$css_class = "";
			if(isset($this->css_ids[$fields[$i]]))
				$css_id = "id='{$this->css_ids[$fields[$i]]}'";
			if(isset($this->css_classes[$fields[$i]]))
				$css_class = "class='{$this->css_classes[$fields[$i]]}'";
			if(isset($this->breaks[$fields[$i]])){
				if($this->breaks[$fields[$i]])
					echo "<p>";
			}else if($this->multi_line)
				echo "<p>";
			$field = trim($fields[$i]);
			$type = trim($types[$i]);
			$label = $labels[$i];
			if(isset($this->values[$field]))
				$value = "value='".$this->values[$field]."'";
			else
				$value = "";

			switch($type){
				case "text":
					if(isset($this->sizes[$field])){
						$size = "size='".$this->sizes[$field]."'";
					}else
						$size = "";
					echo "<label for='".$field."_input'><b>$label</b></label><input type='$type' name='".$field."_input' $css_id $css_class $size $value />";
				break;
				case "submit":
					echo "<input type='submit' value='$label' $css_class/>";
				break;
				case "textarea":
					if(isset($this->values[$field]))
						$value = $this->values[$field];
					if(isset($this->sizes[$field]['rows'])){
						$rows = "rows='".$this->sizes[$field]['rows']."'";
					}else
						$rows = "";
					if(isset($this->sizes[$field]['rows'])){
						$cols = "cols='".$this->sizes[$field]['cols']."'";
					}else
						$cols = "";
					echo "<label for='".$field."_input'><b>$label</b><br/></label><textarea name='".$field."_input' $css_id $css_class $rows $cols >$value</textarea>";
				break;
				case "hidden":
					echo "<input type='hidden' $value name='".$field."_input' $css_id $css_class />";
				break;
				case "password":
					echo "<label for='".$field."_input'><b>$label</b></label><input type='password' $value name='".$field."_input' $css_id $css_class />";
				break;
				case "select";
					if(isset($this->select[$field])){
						echo "<label for='".$field."_input'><b>$label</b></label>";
						echo "<select name='{$field}_input' $css_id $css_class>";
						foreach($this->select[$field] as $key => $val){
							echo "<option value='$key'>$val</option>";
						}
						echo "</select>";
					}else{
						echo "error: no select values in \$this->select[$field]";
					}
				break;
				case "file":
					echo "<label for='".$field."_input'><b>$label</b></label><input type='file' $value name='".$field."_input' $css_id $css_class />";
				break;
				default:
					echo "'$type' does not exist as a form type";
				break;
			}
			if($this->multi_line && !isset($this->breaks[$fields[$i]]))
				echo "</p>";
		}
		if($this->multi_line)
			echo "<p>";
		echo $this->submit;
		if($this->multi_line)
			echo "</p>";
		if(!$this->multi_line)
			echo "</p>";
		echo "</form>";

	}
	function create_from_post($fields,$class){
		$fields2 = explode(",",$fields);
		$values = "";
		foreach($fields2 as $field){
			$field = trim($field);
			if(isset($this->values[$field])){
				$values = $values.str_replace(",","(;)",$this->values[$field]).",";
			}else{
				$values = $values.str_replace(",","(;)",$_POST[$field."_input"]).",";
			}
		}
		$values = substr($values,0,-1);
		$obj = new $class(0);
		$obj->debug = $this->debug;
		$obj->get_id = $this->get_id;
		$this->obj = $obj;
		return $obj->create($fields,$values);
	}
	function update_from_post($fields){
		$class = $this->has_object;
		$fields2 = explode(",",$fields);
		$values = "";
		foreach($fields2 as $field){
			$field = trim($field);
			$values = $values.str_replace(",","(;)",$_POST[$field."_input"]).",";
		}
		$values = substr($values,0,-1);
		if(isset($this->object_id))
			$obj = new $class($this->object_id);
		else{
			$obj = new $class(0);
			$obj->fields[$obj->key] = $_POST[$obj->key."_input"];
			$this->object_id = $_POST[$obj->key."_input"];
		}
		if($this->debug)
			$obj->debug = true;
		return $obj->update($fields,$values);
	}
	function read_select($option, $key, $table){
		$sql = "SELECT $option, $key FROM $table";
		if($this->debug)
			echo $sql;
		$result = mysql_query($sql);
		while($row = mysql_fetch_array($result)){
			$this->select[$table][$row[$key]] = $row[$option];
		}
	}
	function set_select($field, $keys, $values){
		$keys = explode(",",$keys);
		$values = explode(",",$values);
		$i = 0;
		foreach($keys as $key){
			$key = trim($key);
			$value = trim($values[$i++]);
			$this->select[$field][$key] = $value;
		}
	}
	function set_sizes($fields,$sizes){
		$fields = explode(",",$fields);
		$sizes = explode(",",$sizes);
		$i = 0;
		foreach($fields as $field){
			$field = trim($field);
			$size = trim($sizes[$i++]);
			$this->sizes[$field] = $size;
		}
	}
	function set_css_classes($fields,$classes){
		$fields = explode(",",$fields);
		$classes = explode(",",$classes);
		$i = 0;
		foreach($fields as $field){
			$field = trim($field);
			$class = trim($classes[$i++]);
			$this->css_classes[$field] = $class;
		}
	}
}
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