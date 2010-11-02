<?php
class translated_table extends table{
	protected $translation_fields;
	protected $tt_fields;
	protected $reg_fields;
	public function read($fields,$language){		
		$this->separate_fields($fields);
		if(!$this->search_clause)
			$this->read_individual($language);
		else
			return $this->read_multiple($language);
	}
	protected function read_individual($language){
		parent::read($this->reg_fields);
		$this->table_name = $this->table_name."_$language";
		parent::read($this->tt_fields);
	}
	protected function read_multiple($language){
		$results1 = parent::read($this->reg_fields);
		$this->table_name = $this->table_name."_$language";
		$results2 = parent::read($this->tt_fields);
		for($i=0;$i<count($results1);$i++){
			if(isset($results2[$i])){
				$final[$i] = (object)array_merge((array)$results1[$i],(array)$results2[$i]);
			}
		}
		return $final;
	}
	public function separate_fields($fields){
		$fields = explode(",",$fields);
		$i = 0;
		$j = 0;
		foreach($fields as $field){
			$fileld = trim($field);
			if(in_array($field,$this->translation_fields)){
				$this->tt_fields[$i++] = $field;
			}else{
				$this->reg_fields[$j++] = $field;
			}
		}
		$this->tt_fields = implode(",",$this->tt_fields);
		$this->reg_fields = implode(",",$this->reg_fields);
	}
}
?>