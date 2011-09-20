<?php
class user extends table{
	function info(){
		$this->table_name = "users";
		$this->md5['password'] = true;
		$this->objects['setting'] = 'setting';
		$this->has_many['settings'] = 'user_setting';
		$this->menu = 'administration';
		$this->inputs = array(
			"name" => "Name,text,required",
			"last_name" => 'Last Name,text,required',
			"email" => 'Email,text,required email',
			"password" => 'Password,password,required',
			"setting" => 'Settings,multi,setting'
		);
		$this->sections = array(
			"Information" => 'name,last_name,email,setting',
			"Password" => 'password'			
		);
		$this->list_cells = array(
			"Name" => "name,last_name",
			"Email" => 'email'
		);
	}
}
class user_setting extends table{
	function info(){
		$this->table_name = "user_setting";
		$this->objects['user'] = 'user';
		$this->objects['setting'] = 'setting';		
	}
}
class setting extends table{
	function info(){
		$this->table_name = 'settings';
	}
}
?>