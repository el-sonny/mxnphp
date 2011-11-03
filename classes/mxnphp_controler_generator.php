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
{$this->image_fields}
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
		//\${$this->class_name}_query->order_by = "name";		
		\$this->{$this->class_name}_pagination = new pagination('{$this->class_name}',$per_page,\${$this->class_name}_query->search_clause);
		\${$this->class_name}_query->limit = \$this->{$this->class_name}_pagination->limit;
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
		$this->image_fields = "";
		$this->multi_ajax_create = "";
		$this->multi_ajax_deletes = "";
		foreach($this->table->inputs as $field => $parameters){
			$parameters = explode(",",$parameters);
			if($parameters[1] == 'multi'){
				$this->generate_multi_functions($field,$parameters);
			}else if($parameters[1] == 'image'){
				$this->generate_image_functions($field,$parameters);
			}
		}
		return $this->multi_ajax_create.$this->multi_ajax_deletes;
	}
	private function filter_inputs(){
		$i = $j = 0;
		foreach($this->table->inputs as $input => $parameters){
			$parameters = explode(",",$parameters);
			if($parameters[1] != "multi"){
				if($parameters[1] == 'object'){
					$object = new $input();
					$this->read_inputs[$i++] = $input."=>".$parameters[2];
					$this->read_inputs[$i++] = $input."=>".$object->key;
					$this->create_inputs[$j++] =  $input;
					$field_reads = str_replace(";",',',$parameters[2]);
					$function_name = "load_{$object->table_name}";
					$this->multi_load_calls .= "
		\$this->$function_name();";
					$this->multi_loads[$input] = <<<EOD
				
	protected function $function_name(){
		\$query = new $input();
		\$query->search_clause = "1";
		\$this->{$object->table_name} = \$query->read("{$object->key},{$field_reads}");
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
		$j = $i = 0;
		foreach($this->table->sections as $section){
			$fields = explode(",",$section);
			foreach($fields as $field){
				$parameters = explode(",",$this->table->inputs[$field]);
				if($parameters[1] != "multi"){
					$new_section[$i++] = $field;
				}
			}
			if($new_section != null) $new_sections[$j++] = implode(",",$new_section);
			$new_section = null;
			$i = 0;
		}
		return $new_sections;
	}
	private function generate_multi_functions($field,$parameters){
		$multi_object = new $field();			
		$rel_temp = explode(";",$parameters[2]);
		$rel_loads = "{$multi_object->table_name}=>{$field}=>".implode(",{$multi_object->table_name}=>{$field}=>",$rel_temp);
		$rel_class = $this->table->has_many[$multi_object->table_name];
		$rel_key = $this->table->has_many_keys[$multi_object->table_name] ? $this->table->has_many_keys[$multi_object->table_name] : $this->class_name;
		$this->rel_fields .= ",$rel_loads,{$multi_object->table_name}=>{$field}=>{$multi_object->key},{$multi_object->table_name}=>id"; 
		$function_name = "load_{$multi_object->table_name}";
		$this->multi_load_creates .= "
			\$this->create_rels('$rel_class','{$rel_key},{$field}',\${$this->class_name}->{$this->table->key},\$_POST['{$field}_input']);";
		$this->multi_ajax_create .= <<<EOD
				
	public function add_$field(){
		\$record = new {$rel_class}();
		\$record = \$this->create_record("$field,$rel_key","{$rel_class}",array(\$_POST['son'],\$_POST['parent']));
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
			$field_reads = str_replace(";",',',$parameters[2]);
			$this->multi_loads[$field] = <<<EOD
				
	protected function $function_name(){
		\$query = new $field();
		\$query->search_clause = "1";
		\$this->{$multi_object->table_name} = \$query->read("{$multi_object->key},{$field_reads}");
	}		
EOD;
		}
	}
	private function generate_image_functions($field,$parameters){
		$image_creates = $this->make_image_folders($field);	
		$this->image_fields .= <<<EOD
		
	public function new_$field(){
		\$ext = explode('.',\$_FILES['Filedata']['name']);
		\${$this->class_name} = new {$this->class_name}();
		\$filename = (\${$this->class_name}->max_id()+1).".".strtolower(\$ext[count(\$ext)-1]);
		\$folder = \$this->config->uploads_dir."/{$this->table->table_name}/$field/";
		\$file = \$this->save_post_file(\$_FILES['Filedata'],\$folder."/original_size/",\$filename);
		if(\$file){			
			\$tiny = \$this->make_thumb(\$folder."/original_size/".\$file,\$folder.'/tiny/'.\$filename,100,100);$image_creates
			\$response->thumb = \$this->config->uploads_path."domains/$field/tiny/".\$filename;
			\$response->full = \$this->config->uploads_path."domains/$field/original_size/".\$filename;
			\$response->filename = \$filename;
			\$response->delete_url = "/{$this->table->table_name}/destroy_{$field}/".\$filename;
			echo json_encode(\$response);
		}else{
			echo "fail";
		}
	}	
EOD;
	}
	private function make_image_folders($field){
		$folders[0] = $this->config->uploads_dir."{$this->table->table_name}";
		$folders[1] = $folders[0]."/$field";
		$folders[2] = $folders[1]."/original_size";
		$folders[3] = $folders[1]."/tiny";
		$image_creates = "";
		if(isset($this->table->image_sizes[$field])){
			$i = 4;
			foreach($this->table->image_sizes[$field] as $label => $dimensions){
				$folders[$i++] = $folders[1]."/".$label;
				$dimensions = explode("x",$dimensions);
				$image_creates .= "
			\${$label} = \$this->make_thumb(\$folder.'/original_size/'.\$file,\$folder.'/$label/'.\$filename,{$dimensions[0]},{$dimensions[1]});";				
			}
		}
		foreach($folders as $folder){
			if(!file_exists($folder)){
				mkdir($folder);
			}
		}
		return $image_creates;
	}
}
?>