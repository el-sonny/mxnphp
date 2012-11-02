<?php
class security_component extends component{
	public function init($params=false){
		$user_class = 'account';$user_field = 'email';$pass_field = 'password';$post_user = 'username';$post_pass = 'password';$hash_function = 'md5';
		if(!is_array($params)) $user_class = $params;
		$this->user_class = $user_class;
		$this->user_field = $user_field;
		$this->pass_field = $pass_field;
		$this->post_user = $post_user;
		$this->post_pass = $post_pass;
		$this->hash_function = $hash_function;
		if(is_array($params)) parent::init($params);
	}
	protected function do_login(){
		$this->controler->dbConnect();
		$user_name = $this->clean_input($_POST[$this->post_user]);
		$pass = $this->{$this->hash_function}($_POST[$this->post_pass]);
		$user = new $this->user_class();	
		$user->debug = $this->debug;
		$user->search_clause = "{$this->user_field} = '$user_name'";
		$user->debug = $this->debug;
		$users = $user->read("id,{$this->pass_field}");
		if($users){
			$user = $users[0];
			//compare the password
			if($user->{$this->pass_field} == $pass){
				if(isset($this->config->security_variable) && $this->config->security_variable == "cookie"){
					setcookie($this->config->session_name,$user->id, time() + 2592000, "/");
				}else{
					session_start();				
					$_SESSION[$this->config->session_name] = $user->id;	
				}
				$this->session_id = $user->id;
				return "success";
			}else{
				//Wrong Password
				return "pass";
			}
		}else{
			//Wrong Username
			return "username";
		}
	}
	public function verify_login($start_session = true){
		if(isset($this->config->security_variable) && $this->config->security_variable == "cookie"){
			if(isset($_COOKIE[$this->config->session_name])){	
				$this->session_id = $_COOKIE[$this->config->session_name];
				//$this->load_user_info();
				return true;
			}else {
				return false;
			}				
		}else{			
			if($start_session) session_start();
			if(isset($_SESSION[$this->config->session_name])){	
				$this->session_id = $_SESSION[$this->config->session_name];
				return true;
			}else 
				return false;
			
		}
	}
	protected function load_user_info(){
		if(isset($this->session_id)){
			
			$this->user = new $this->user_class($this->session_id);
			//$this->user->read();
		}else
			return false;
	}
	protected function md5($string){
		return md5($string);
	}
}

?>