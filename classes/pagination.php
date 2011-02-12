<?php 
class pagination{
	public $limit;
	public $pages;
	public $current_page;
	public function pagination($class,$per_page,$clause = false,$page_variable = "p"){
		$object = new $class();
		$table = $object->table_name;
		$key = $object->key;
		$clause = $clause ? "WHERE $clause" : "";
		$sql = "SELECT COUNT('$key') FROM $table $clause;";
		$result = mysql_fetch_array(mysql_query($sql));
		$count = $result?$result[0]:0;
		$start = (isset($_GET[$page_variable])) ? ($_GET[$page_variable]-1)*$per_page : 0;
		$end = $start + $per_page;
		$this->document_pages = ceil(($count) / $per_page);	
		$this->current_page = (isset($_GET[$page_variable])) ? $_GET[$page_variable] : 1;
		$this->limit = ($count > $per_page) ? "$start, $end" : false;
	}
	
	public function echo_paginate($base_link,$variable="p",$show_pages = false){
		$start = 1;
		$end = $this->document_pages;
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
				echo "<a href='$base_link$variable=1'>&lt;&lt;</a> ";
				echo "<a href='$base_link$variable=$previous'>&lt;</a> ";
			}
			if($end > $this->document_pages){
				$start -= $end - $this->document_pages;
				$end = $this->document_pages;
				$end_print = "";
			}else{
				$next = $this->current_page + $show_pages;
				if($next > $this->document_pages)
					$next = $this->document_pages;
				$end_print = "<a href='$base_link$variable=$next'>&gt;</a> <a href='$base_link$variable={$this->document_pages}'>&gt;&gt;</a> ";
			}
		}
		if($this->document_pages > 1){
			for($i = $start;$i<=$end;$i++){
				$on = $i == $this->current_page ? "class='on'" : "";
				echo "<a href='$base_link$variable=$i' $on>$i</a> ";
			}
		}
		if($show_pages && $show_pages < $this->document_pages){
			echo $end_print;
		}
	}
}
?>