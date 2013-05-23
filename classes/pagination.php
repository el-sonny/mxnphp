<?php 
class pagination{
	public $limit;
	public $pages;
	public $current_page;
	private $object2;
	protected $per_page;
	protected $page_variable;
	private $name_class  = false;
	private $clause_table = "";
	public function pagination($class,$per_page=10,$clause = false,$page_variable = "p"){
		$this->page_variable = $page_variable;
		$this->per_page = $per_page;
		if (!is_object($class)){
			$classes = explode(",",$class);
			$tables ="";
			$keys = "";
			if(!$this->name_class){
				foreach($classes as $class){
					if ($class != ""){
						$object = new $class();
						$table = $object->table_name;
						$tables = $tables.$table.",";
					}
				}
			}else{
				foreach($classes as $class){
					if($class != "")
						$tables = $tables.$class.",";
				}
			}
			$tables = substr($tables,0,-1);
			$clause = $clause ? "WHERE $clause" : "";
			$sql = "SELECT COUNT(1) as total FROM $tables $clause {$this->clause_table};";
			$result  = $this->exec_query($sql);
			$this->calc_pages($result);
		}else{
			$this->object2 = $class;
		}
	}
	protected function calc_beginLimit(){
		return (isset($_REQUEST[$this->page_variable])) ? ($_REQUEST[$this->page_variable]-1)*$this->per_page : 0;
	}
	protected function calc_pages($result){
		if($result){
			$count = $result?$result[0]->total:0;
			$start = $this->calc_beginLimit();
			$end = $this->per_page;
			$this->document_pages = ceil(($count) / $this->per_page);	
			$this->current_page = (isset($_REQUEST[$this->page_variable])) ? $_REQUEST[$this->page_variable] : 1;
			$this->limit = ($count > $this->per_page) ? "$start, $end" : false; 
		}else{
			$start = 0;
			$end = 1;
			$this->document_pages = 1;	
			$this->current_page = 1;
			$this->limit = false;
		}		
	}
	public function read($fields){
		$tables = $this->object2->get_tables($fields);
		$tables = implode(",",$tables);
		$this->name_class = true;
		$this->clause_table = $this->object2->clause;
		$this->pagination($tables,$this->per_page,$this->object2->search_clause,$this->page_variable);
		$this->clause_table = "";
		$this->name_class = false;
		$this->object2->limit = $this->limit;
		return $this->object2->read($fields);
	}
	public function echo_paginate($base_link,$variable="p",$show_pages = false,$class = false,$labels = false){
		$start = 1;
		$end = $this->document_pages;
		$hash = isset($labels->hash) ? $labels->hash : '';
		//$class_p = $class ? "class='$class'" : "";
		if($show_pages && $show_pages < $this->document_pages){
			$offset = floor($show_pages/2);
			$start = $this->current_page - $offset;
			$end = $this->current_page + $offset;

			if($start <= 0){
				$end += 1 - $start;
				$start = 1;
			}else{
				$previous = $this->current_page - $show_pages;
				if($previous <= 0)
					$previous = 1;
				$prev_label = isset($labels->prev) ? $labels->prev : '&lt;&lt;';
				$prev_page_label = isset($labels->prev_page) ? $labels->prev_page : '&lt';
				
				echo "<a href='$base_link$variable=1$hash' class='$class first_page'>$prev_page_label</a> ";
				echo "<a href='$base_link$variable=$previous$hash' class='$class prev_page'>$prev_label</a> ";
			}
			if($end > $this->document_pages){
				$start -= $end - $this->document_pages;
				$end = $this->document_pages;
				$end_print = "";
			}else{
				$next = $this->current_page + $show_pages;
				if($next > $this->document_pages)
					$next = $this->document_pages;
				$next_label = isset($labels->next) ? $labels->next : '&gt;';
				$next_page_label = isset($labels->next_page) ? $labels->next_page : '&gt;&gt;';
				$end_print = "<a href='$base_link$variable=$next$hash' class='$class next_page' >$next_label</a> <a href='$base_link$variable={$this->document_pages}$hash' class='$class last_page'>$next_page_label</a> ";
			}
		}
		if($this->document_pages > 1){
			for($i = $start;$i<=$end;$i++){
				$on = $i == $this->current_page ? " on" : "";
				$classy = $class ? "class='$class$on'" : "class='$on'";
				echo "<a href='$base_link$variable=$i$hash' $classy >$i</a> ";
			}
		}
		if($show_pages && $show_pages < $this->document_pages){
			echo $end_print;
		}
	}
    protected function exec_query($query){
		$result = mysql_query($query);
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
}
?>