<?php
class mxnphp_controler_generator extends mxnphp_code_generator{
	public function create(){
		$this->controler_dir = $this->config->document_root."controlers/"; 
		if(!file_exists($this->controler_dir)){
			mkdir($this->controler_dir);
		}
		$this->filter_inputs();		
		$index = $this->create_index();	
		$multi_ops = $this->create_multi_loads();
		$multi_loads = isset($this->multi_loads) ? implode("",$this->multi_loads) : "";
		$edit = $this->create_edit();
		$common_data = $this->create_common_data();
		$create = $this->create_create();
		$update = $this->create_update();
		$destroy = $this->create_destroy();
		$messages = $this->create_messages();
		$controler = <<<EOD
<?php
class {$this->table->table_name} extends main{
$index
$edit
$common_data
$create
$update
$destroy
$messages{$multi_ops}{$multi_loads}
}
?>
EOD;
		$controler_file = fopen($this->controler_dir."controler.{$this->table->table_name}.php",'w+');
		fwrite($controler_file,$controler);
	}
	private function create_index(){
		$index = <<<EOD
	public function index(){
		\$this->common_data();
		\$this->include_theme("index","index");
	}
EOD;
		return $index;
	}
	private function create_edit(){
		$edit = <<<EOD
	public function edit(){
		\$this->common_data();
		\$this->edit_{$this->class_name} = new {$this->class_name}(\$_GET['id']);
		\$this->edit_{$this->class_name}->read("{$this->read_inputs}{$this->rel_fields}");
		\$this->include_theme("index","edit");
	}	
EOD;
		return $edit;
	}
	private function create_common_data(){
		$multi_load_calls = isset($this->multi_load_calls) ? $this->multi_load_calls : "";
		$menu = isset($this->table->menu) ? "
		\$this->menu = '{$this->table->menu}';" : "";
		$submenu = isset($this->table->submenu) ? "
		\$this->submenu = '{$this->table->submenu}';" : "";		
		$listing_search_field = isset($this->table->listing_search_field) ? $this->table->listing_search_field : key(array_slice($this->table->inputs,0,1));
		$listing_cells = isset($this->table->list_cells) ? implode(",",$this->table->list_cells) : $this->read_inputs;
		$per_page = isset($this->per_page) ? $this->per_page : 10;
		$common_data = <<<EOD
	public function common_data(){{$menu}{$submenu}{$multi_load_calls}
		\$this->location = "{$this->table->table_name}";
		\${$this->class_name}_query = new {$this->class_name}();
		if(isset(\$_REQUEST['q'])){
			\${$this->class_name}_query->search_clause("{$listing_search_field}",\$_REQUEST['q'],'LIKE',true);
		}else{
			\${$this->class_name}_query->search_clause = "1";
		}
		\${$this->class_name}_query->order_by = "name";		
		\$this->{$this->class_name}_pagination = new pagination('{$this->class_name}',$per_page,\${$this->class_name}_query->search_clause);
		\$this->{$this->table->table_name} = \${$this->class_name}_query->read("{$this->table->key},{$listing_cells}");
	}	
EOD;
		return $common_data;
	}
	private function create_create(){
		$multi_creates = "";
		if(isset($this->multi_load_creates)){
			$multi_creates = <<<EOD
if(\${$this->class_name}){{$this->multi_load_creates}
		}			
EOD;
		}
		$create = <<<EOD
	public function create(){
		\${$this->class_name} = \$this->create_record("{$this->create_inputs}","{$this->class_name}");
		$multi_creates
		\$message = \${$this->class_name}?"m=cs":"e=ce";
		header("Location: /{$this->table->table_name}/\$message");
	}
EOD;
		return $create;
	}
	private function create_update(){
		$sections = $this->clean_sections();
		$inputs = '$inputs = array("'.implode('","',$sections).'");';
		$update = <<<EOD
	public function update(){
		$inputs
		\$id = \$_POST['{$this->class_name}_{$this->table->key}'];
		\$message = \$this->update_record("{$this->class_name}",\$inputs[\$_GET['id']],\$id)?"m=us":"e=ue";
		header("Location: /{$this->table->table_name}/edit/\$id/\$message");
	}	
EOD;
		return $update;
	}
	private function create_destroy(){
		$destroy = <<<EOD
	public function destroy(){
		\$message = \$this->destroy_record(\$_GET['id'],"{$this->class_name}")?"m=ds":"e=de";
		header("Location: /{$this->table->table_name}/\$message");
	}	
EOD;
		return $destroy;
	}
	private function create_messages(){
		$class_name = ucwords($this->class_name);
		$messages = <<<EOD
	public function get_error(){
		\$error = \$_GET['e'];
		\$errors['ce'] = 'Create $class_name Error: All Fields are Required';
		\$errors['ue'] = 'Update $class_name Error: All Fields are Required';
		\$errors['de'] = 'Delete $class_name Error';
		return \$errors[\$error];
	}
	public function get_message(){
		\$error = \$_GET['m'];
		\$errors['cs'] = 'Create $class_name Success';
		\$errors['us'] = 'Update $class_name Success';
		\$errors['ds'] = 'Delete $class_name Success';
		return \$errors[\$error];
	}	
EOD;
		return $messages;
	}
	private function create_multi_loads(){	
		$this->rel_fields = "";
		foreach($this->table->inputs as $field => $parameters){
			$parameters = explode(",",$parameters);
			if($parameters[1] == 'multi'){
				$multi_object = new $field();				
				$rel_class = $this->table->has_many[$multi_object->table_name];
				$rel_key = $this->table->has_many_keys[$multi_object->table_name] ? $this->table->has_many_keys[$multi_object->table_name] : $this->class_name;
				$this->rel_fields .= ",{$multi_object->table_name}=>{$field}=>{$parameters[2]},{$multi_object->table_name}=>{$field}=>{$multi_object->key},{$multi_object->table_name}=>id"; 
				$function_name = "load_{$multi_object->table_name}";
				$this->multi_load_creates .= "
			\$this->create_rels('$rel_class','{$rel_key},{$field}',\${$this->class_name}->{$this->table->key},\$_POST['{$field}_input']);";
				$this->multi_ajax_create .= <<<EOD
				
	public function add_$field(){
		\$record = new {$this->class_name}_$field();
		\$record = \$this->create_record("$field,$rel_key","{$this->class_name}_$field",array(\$_POST['son'],\$_POST['parent']));
		echo \$record->id;
	}
EOD;
				$this->multi_ajax_deletes .= <<<EOD
				
	public function delete_$field(){
		\$this->destroy_record(\$_POST['id'],"{$this->class_name}_$field");
	}			
EOD;
				if(!isset($this->multi_loads) || !in_array($field,array_keys($this->multi_loads))){
					$this->multi_load_calls .= "
		\$this->$function_name();";

					$this->multi_loads[$field] = <<<EOD
				
	protected function $function_name(){
		\$query = new $field();
		\$query->search_clause = "1";
		\$this->{$multi_object->table_name} = \$query->read("{$multi_object->key},{$parameters[2]}");
	}		
EOD;
				}
			}
		}
		return $this->multi_ajax_create.$this->multi_ajax_deletes;
	}
	private function filter_inputs(){
		foreach($this->table->inputs as $input => $parameters){
			$parameters = explode(",",$parameters);
			if($parameters[1] != "multi"){
				if($parameters[1] == 'object'){
					$object = new $input();
					$this->read_inputs[$i++] = $input."=>".$parameters[2];
					$this->read_inputs[$i++] = $input."=>".$object->key;
					$this->create_inputs[$j++] =  $input;
					$function_name = "load_{$object->table_name}";
					$this->multi_load_calls .= "
		\$this->$function_name();";
					$this->multi_loads[$input] = <<<EOD
				
	protected function $function_name(){
		\$query = new $input();
		\$query->search_clause = "1";
		\$this->{$object->table_name} = \$query->read("{$object->key},{$parameters[2]}");
	}			
EOD;
				}else{
					$this->read_inputs[$i++] = $input;
					$this->create_inputs[$j++] = $input;
				}
			}
		}
		$this->create_inputs = implode(",",$this->create_inputs);
		$this->read_inputs = implode(',',$this->read_inputs);
	}
	private function clean_sections(){
		foreach($this->table->sections as $section){
			$fields = explode(",",$section);
			foreach($fields as $field){
				$parameters = explode(",",$this->table->inputs[$field]);
				if($parameters[1] != "multi"){
					$new_section[$i++] = $field;
				}
			}
			$new_sections[$j++] = implode(",",$new_section);
			$new_section = null;
			$i = 0;
		}
		return $new_sections;
	}
}
?>