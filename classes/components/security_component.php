<?php
class security_component extends component{
	public function init(){
	
	}
	protected function do_login(
			$user_class = 'user',
			$user_field = 'email', 
			$pass_field = 'password',
			$post_user = 'username',
			$post_pass = 'password',
			$hash_function = 'md5'
		){
		$this->controler->dbConnect();
		$user_name = $this->clean_input($_POST[$post_user]);
		$pass = $hash_function($_POST[$post_pass]);
		$user = new $user_class();	
		$user->debug = $this->debug;
		$user->search_clause = "$user_field = '$user_name'";
		$user->debug = $this->debug;
		$users = $user->read("id,$pass_field");
		if($users){
			$user = $users[0];
			//compare the password
			if($user->$pass_field == $pass){
				if(isset($this->config->security_variable) && $this->config->security_variable == "cookie"){
					setcookie($this->config->session_name,$user->id, time() + 2592000, "/");
				}else{
					session_start();				
					$_SESSION[$this->config->session_name] = $user->id;	
				}
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
				return true;
			}else {
				return false;
			}				
		}else{
			
			if($start_session)
				session_start();
			if(isset($_SESSION[$this->config->session_name])){	
				$this->session_id = $_SESSION[$this->config->session_name];
				return true;
			}else 
				return false;
		}
	}
}

?>