<?php 
class mxnphp_pagination extends pagination{
	private $_currentPage;
	private $_itemsPerPage;
	private $_query;
	private $_total;
	public function __construct($query){
		$this->_query = $query;
		$this->_itemsPerPage = 20;
		$this->_currentPage = 1;
		$this->_varPage = 'p';
	}
	public function setCurrentPage($page){
		$this->_currentPage = $page;
		return $this;
	}
	public function setItemsPerPage($perPage){
		$this->_itemsPerPage = $perPage;
		$this->per_page = $this->_itemsPerPage;
		return $this;
	}
	public function printSql(){
		echo $this->_query;
	}
	public function getItems(){
		$begin = $this->calc_beginLimit();
		$this->_query->limit($begin,$this->_itemsPerPage);
		return $this->exec_query($this->_query);
	}
	private function _getTotal(){
		$query2 = clone $this->_query;
		$query2->reset(mxnphp_Db_select::FIELDS);
		$query2->reset(mxnphp_Db_select::LIMIT);
		$query2->select("COUNT(1) as total");
		$result = $this->exec_query($query2);
		return $result;
	}
	private function getTotal(){
		$result =  $this->_getTotal();
		return $result[0]->total;
	}
	public function echo_paginate($base_link,$variable="p",$show_pages = false,$class = false){

		$result = $this->_getTotal();
		$this->per_page = $this->_itemsPerPage;
		$this->calc_pages($result);
		parent::echo_paginate($base_link,$this->page_variable,$show_pages,$class);
	}
	public function setVarPage($var){
		$this->page_variable = $var;
	}
}
?>