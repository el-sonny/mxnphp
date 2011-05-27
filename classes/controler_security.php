<?php
/**
* Clase controler
* 
* Clase abstracta que modela la conexion, 
* tema, controlador y template 
*
* @param string $config
* @param bool $security  
* @param bool $debug 
* @param string $session_id 
* @param string $document_pages
* @param string $current_page 
* @param string $measure_time_start 
* @param string $measure_time_stop 
*
*
*/
class controler_security extends controler{
	protected $apply_hash = false;
	protected function do_login(
			$user_class = 'user',
			$user_field = 'email', 
			$pass_field = 'password',
			$post_user = 'username',
			$post_pass = 'password',
			$hash_function = 'md5',
			$salt_field = 'salt'
		){
		$this->dbConnect();
		$user_name = $this->clean_input($_POST[$post_user]);
		$user = new $user_class();	
		$user->debug = $this->debug;
		$user->search_clause = "$user_field = '$user_name'";
		$user->debug = $this->debug;

		if(!$this->apply_hash){
			$users = $user->read("id,$pass_field");
		}else{
			$users = $user->read("id,$pass_field,$salt_field");
		}
		if($users){
			$user = $users[0];
			//compare the password
			
			$cmp = $this->cmp_password($user,$hash_function,$post_pass,$pass_field,$salt_field);
			if($cmp){
				if(isset($this->config->security_variable) && $this->config->security_variable == "cookie"){
					setcookie($this->config->session_name,$user->id, time()+2592000, "/");
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
	/**
	 * Funcion verify_login
	 * 
	 * Verifica el inicio de sesion
	 *  
	 * @param bool $start_session si es verdadera se inicia la sesion 
	 * y se le asigna como campo a una variable de sesion 
	 *
	 * @return bool 
	 */

	protected function cmp_password($user,$hash_function,$post_pass,$pass_field,$salt_field){
		if(!$this->apply_hash){
			$pass = $hash_function($_POST[$post_pass]);
			return $user->$pass_field == $pass;
		}else{
			$password = $_POST[$post_pass];
			$hash = $user->$pass_field;
			$salt = $user->$salt_field;

			return $this->hash_password($password, $salt) == $hash;
		}
	}
	public function verify_login($start_session = true){
		if(isset($this->config->security_variable) && $this->config->security_variable == "cookie"){
			if(isset($_COOKIE[$this->config->session_name])){	
				$this->session_id = $_COOKIE[$this->config->session_name];
				return true;
			}else 
				return false;
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