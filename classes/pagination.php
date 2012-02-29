<?php 
class pagination{
	public $limit;
	public $pages;
	public $current_page;
	private $object2;
	private $per_page;
	private $page_variable;
	private $name_class  = false;
	private $clause_table = "";
	public function pagination($class,$per_page=10,$clause = false,$page_variable = "p"){
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
			$sql = "SELECT COUNT(*) FROM $tables $clause {$this->clause_table};";
			$result = mysql_query($sql);
			if($result){
				$result = mysql_fetch_array($result);
				$count = $result?$result[0]:0;
				$start = (isset($_GET[$page_variable])) ? ($_GET[$page_variable]-1)*$per_page : 0;
				$end = $per_page;
				$this->document_pages = ceil(($count) / $per_page);	
				$this->current_page = (isset($_GET[$page_variable])) ? $_GET[$page_variable] : 1;
				$this->limit = ($count > $per_page) ? "$start, $end" : false;
			}else{
				$start = 0;
				$end = 1;
				$this->document_pages = 1;	
				$this->current_page = 1;
				$this->limit = false;
			}
		}else{
			$this->object2 = $class;
			$this->per_page = $per_page;
			$this->page_variable = $page_variable;
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
	public function echo_paginate($base_link,$variable="p",$show_pages = false,$class = false){
		$start = 1;
		$end = $this->document_pages;
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
				echo "<a href='$base_link$variable=1' class='$class first_page'>&lt;&lt;</a> ";
				echo "<a href='$base_link$variable=$previous' class='$class prev_page'>&lt;</a> ";
			}
			if($end > $this->document_pages){
				$start -= $end - $this->document_pages;
				$end = $this->document_pages;
				$end_print = "";
			}else{
				$next = $this->current_page + $show_pages;
				if($next > $this->document_pages)
					$next = $this->document_pages;
				$end_print = "<a href='$base_link$variable=$next' class='$class next_page' >&gt;</a> <a href='$base_link$variable={$this->document_pages}' class='$class last_page'>&gt;&gt;</a> ";
			}
		}
		if($this->document_pages > 1){
			for($i = $start;$i<=$end;$i++){
				$on = $i == $this->current_page ? " on" : "";
				$classy = $class ? "class='$class$on'" : "class='$on'";
				echo "<a href='$base_link$variable=$i' $classy >$i</a> ";
			}
		}
		if($show_pages && $show_pages < $this->document_pages){
			echo $end_print;
		}
	}
}
?>