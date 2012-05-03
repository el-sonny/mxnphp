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
	
abstract class controler extends event_dispatcher{
	public $config;
	public $security;
	public $debug = false;
	public $session_id;
	//Pagination Variables
	public $document_pages;
	public $current_page;
	//Time measuring Variables
	protected $measure_time_start;
	protected $measure_time_stop;
	protected $components = array();
	protected $_escape = "htmlspecialchars";
	protected $_encoding = "UTF-8";
/**
* Funcion controler
* 
* Crea el objeto configuracion y seguridad
*  
*
* @param string $config nombre de la configuracion
* 
* @param bool $security  
* 
*
*/	
	public function controler($config,$security=false){
		$this->config = $config;
		$this->security = $security;
	}
	protected function do_login(
			$user_class = 'user',
			$user_field = 'email', 
			$pass_field = 'password',
			$post_user = 'username',
			$post_pass = 'password',
			$hash_function = 'md5'
		){
		$this->dbConnect();
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
     /**
	 * Funcion dbConnect
	 * 
	 * Realiza la conexion con la base de datos
	 *  
	 * Realiza la conexion y valida si la conexion fue exitosa
	 * @return string $conn regresa la variable con los datos de conexion 
	 *
	 */		
	public function dbConnect(){
		$conn = mysql_connect($this->config->db_host, $this->config->db_user, $this->config->db_pass) or die ('Error connecting to mysql');
		mysql_select_db($this->config->db_name);
		mysql_query("SET NAMES 'utf8'");
		return $conn;
	}
	protected function dbDisconect(){
		mysql_close();
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
	protected function delete_file($file,$dir){
		//chown($dir,999);
		return unlink($dir.$file);
	}
	protected function create_record($fields,$object_name,$array = false){
		if($this->dbConnect()){
			$object = new $object_name(0);
			$object->debug = $this->debug;
			if($object->create($fields,$array)){
				return $object;
			}
		}	
		return false;
	}
	protected function post_var($variable){
		return $this->clean_input($_POST[$variable]);
	}
	protected function clean_input($input){
		return mysql_real_escape_string(trim($input));
	}
	protected function get($variable){
		return isset($_GET[$variable]) ? $this->clean_input($_GET[$variable]) : false;
		
	}
	protected function post($variable){
		return isset($_POST[$variable]) ? $this->clean_input($_POST[$variable]) : false;
	}
	protected function cookie($variable){
		return isset($_COOKIE[$variable]) ? $this->clean_input($_COOKIE[$variable]) : false;
	}
	protected function request($variable){
		if($this->get($variable)){
			$val = $this->get($variable);
		}else if($this->post($variable)){
			$val = $this->post($variable);
		}else{
			$val = false;
		}
		return $val;
	}
	protected function destroy_record($record_id,$object_name){
		if($this->dbConnect()){
			$object = new $object_name($record_id);
			$object->debug = $this->debug;
			return $object->destroy();
		}
		return false;
	}
	protected function update_record($object_name,$fields,$record_id,$array = false){
		if($this->dbConnect()){
			$object = new $object_name($record_id);
			$object->debug = $this->debug;
			return $object->update($fields,$array);
		}
		return false;
	}
	protected function create_rels($class,$fields,$parent,$children){
		if($parent){
			$children = explode(",",$children);
			if($children){
				$object = new $class();
				$object->debug = $this->debug;
				foreach($children as $child){					
					$object->create($fields,array($parent,$child));
				}
			}
		}
	}
	/*
		Eliminate record and relations
	*/
	protected function delete_rels($parent_class,$parent,$children_class,$children){
		if($parent){
			$d =  new $parent_class($parent);
			if($children){
				$d->read("id,$children=>id");
				foreach($d->$children as $c){
					$this->destroy_record($c->id,$children_class);
				}
			}
			$this->destroy_record($parent,$parent_class);
		}
	}
	protected function make_thumb($image,$target,$width,$height,$type='adaptive'){
		require_once 'ThumbLib.inc.php';
		try{$thumb = PhpThumbFactory::create($image);}
		catch(Exception $e){echo $image." does not exist";}
		switch($type){
			case "best fit":
				$thumb->resize($width,$height);
			break;
			case "adaptive":
				$thumb->adaptiveResize($width, $height);
			break;
		}		
		return $thumb->save($target);
	}
	protected function save_post_file($file,$dir,$filename = false){
		if(!$filename)
			$filename = str_replace(" ","_",$file['name']);
		$location = $dir.$filename;
		return move_uploaded_file($file['tmp_name'], $location) ? $filename : false;
	}
	protected function send_email($to,$subject,$message,$from,$from_name){
		$subject = utf8_decode($subject);
		$headers = "MIME-Version: 1.0" . "\r\n";
		$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
		$headers .= "From: ".utf8_decode($from_name)." <".$from.">\r\n";
		$headers .= 'To: <'.$to.'>'."\r\n";
		$mailit = mail($to,$subject,$message,$headers);
		return $mailit;
	}
	protected function start_measure_time(){
		$time = microtime();
		$time = explode(' ', $time);
		$this->measure_time_start = $time[1] + $time[0];
		return $this->measure_time_start;
	}
	protected function stop_measure_time(){
		$time = microtime();
		$time = explode(' ', $time);
		$this->measure_time_stop = $time[1] + $time[0];
		return round($this->measure_time_stop - $this->measure_time_start,4);
	}
	protected function verify_email($email) {
		return preg_match('/\A(?:(?:[a-z0-9!#$%&\'*+\/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&\'*+\/=?^_`{|}~-]+)*|"(?:[\x01-\x08\x0b\x0c\x0e-\x1f\x21\x23-\x5b\x5d-\x7f]|\\\\[\x01-\x09\x0b\x0c\x0e-\x7f])*")@(?:(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?|\[(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?|[a-z0-9-]*[a-z0-9]:(?:[\x01-\x08\x0b\x0c\x0e-\x1f\x21-\x5a\x53-\x7f]|\\\\[\x01-\x09\x0b\x0c\x0e-\x7f])+)\]))\Z/i', $email); 
	}
	protected function rand_str($length = 32, $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890'){
		$chars_length = (strlen($chars) - 1);
		$string = $chars{rand(0, $chars_length)};
		for ($i = 1; $i < $length; $i = strlen($string)){
			$r = $chars{rand(0, $chars_length)};
			if ($r != $string{$i - 1}) $string .=  $r;
		}
		return $string;
	}
	
	protected function display_template($filename){
		$template = new template($filename);
		$template->render();
		$template->echo_render();
	}
	protected function template_folder($group=false){
		$folder = $group ? $group:get_class($this);
		return "templates/".$this->config->theme."/".$folder."/";
	}
	/**
	 * Funcion default_action
	 * 
	 * Incluye el template con el nombre de la variable $action 
	 *  que se le pase a la funcion
	 * 
	 * @param string $action nombre del template que se incluye
	 *
	 */	
	
	public function default_action($action){
		$this->include_template($action);
	}
	protected function include_template($template,$template_group=false){
		$file = $this->config->document_root.$this->template_folder($template_group).$template.".php";
		if(file_exists($file)){
			$event = new event(array('template' => $template, 'template_group' => $template_group, "file" => $file));
			$this->dispatch_event("pre_template",$event);
			include $file;
		}else{
			header("Status: 404 Not Found");
			echo $file.' does not exist';
		}
	}
	/**
	 * Funcion print_css_tag
	 * 
	 * Imprime la linea de la ruta del archivo css 
	 * 
	 * @param string $file nombre del archivo css por default tomara  el nombre "main"
	 *
	 * @param string $folder carpeta donde se encuentra el archivo css 
	 * por default tomara el nombre de "css"
	 *
	 */	
	
	public function print_css_tag($file="main",$folder="css"){
		$css = $this->config->http_address.$this->template_folder($folder).$file.".css";
		echo "<link href='$css' rel='stylesheet' type='text/css' />";
	}
	/**
	 * Funcion print_js_tag
	 * 
	 * Imprime la linea de la ruta del javascript 
	 * 
	 * @param string $file nombre del archivo javascript por default tomara el nombre de "interactions"
	 *
	 * @param string $folder carpeta donde se encuentra el archivo javascript
	 * por default tomara el nombre de "js"
	 */	
	
	public function print_js_tag($file="interactions",$folder="js"){
		$js = $this->config->http_address.$this->template_folder($folder).$file.".js";
		echo "<script src='$js' type='text/javascript'></script>";
	}
	/**
	 * Funcion print_img_tag
	 * 
	 * Imprime la linea de la ruta de la imagen que se agregara 
	 * 
	 * @param string $file nombre del archivo de la imagen
	 *
	 * @param string $folder carpeta donde se encuentra la imagen por default tomara el nombre de "img"
	 *
	 * @param bool $alt agrega los alt a la imagen por default es falso
	 * 
	 * @param bool $class es la clase de la imagen por default es false
	 */	
	public function print_img_tag($file,$alt=false,$folder="img",$class=false){
		$img = $this->config->http_address.$this->template_folder($folder).$file;
		$alt = $alt?$alt:$file;
		$class = $class?"class='$class'":"";
		echo "<img src='$img' alt='$alt' $class />";
	}
	/**
	 * Funcion include_theme
	 * 
	 * Incluye el template 
	 * 
	 * Mediante las variables que se le pasen a esta funcion 
	 * se determina el template que se utilizara.	
	 *
	 * @param string $theme nombre del tema que se incluye
	 * 
	 * @param string $template nombre del template
	 *
	 * @param string $folder carpeta donde se encuentra el template
	 */	
	public function include_theme($theme="index",$template="index",$folder="themes"){
		$this->template = $template;
		$event = new event(array('template' => &$template, 'theme' => &$theme, "file" => &$folder));
		$this->dispatch_event("pre_theme",$event);
		$this->include_template($theme,$folder);		
	}
	protected function curl_request($url,$fields=array()){
		$fields_string = "";

		foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
		rtrim($fields_string,'&');

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);

		if(count($fields)>0){
			curl_setopt($ch, CURLOPT_POST,count($fields));
			curl_setopt($ch,CURLOPT_POSTFIELDS,$fields_string);
		}
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

		$feed = curl_exec($ch);
		curl_close($ch);
		return $feed;
	}
	protected function load_languages($folder = "languages"){
		$this->LanXML->content = simplexml_load_file($this->config->document_root."{$folder}/".$this->config->controler.".xml");
		$this->LanXML->global = simplexml_load_file($this->config->document_root."{$folder}/global.xml");
	}
	protected function translate($id,$mod=false){
		$controler = $this->config->controler;
		$lang = $this->config->lang;
		if(!$mod){
			$text = (string)$this->LanXML->content->$lang->$id;
		}else{
			$text = (string)$this->LanXML->$mod->$lang->$id;
		}
		return $text;
	}
	protected function generate_salt($password){
		$salt = md5('~'.$password.'~'.microtime(TRUE).'~');
		$salt = substr($salt,rand(0,30),10);
		return $salt;
	}
	protected function hash_password($p, $s, $iter=5) {
		// ALWAYS return a multiple hashed pass salt combination
		$hash = md5(md5($p.$s).md5(strrev($p).strrev($s)));
		// Rehashing the hash will make cracking process much slower
		for($i=0;$i<=$iter;++$i)
			$hash = md5(md5($hash).md5(strrev($hash)));
		return $hash;
	} 
	
	//Cookie Functions
	protected function set_cookie($key,$value){
		setcookie($key,$value,time() + 2592000, "/");
	}
	
	//Component Related Functions
	public function add_component($component,$params=false){
		$this->components[$component] = new $component($this,$params);		
	}
	public function escape($var){
		//Zend Code
		if (in_array($this->_escape, array('htmlspecialchars', 'htmlentities'))) {
			return call_user_func($this->_escape, $var, ENT_COMPAT, $this->_encoding);
		}

		if (1 == func_num_args()) {
			return call_user_func($this->_escape, $var);
		}
		$args = func_get_args();
		return call_user_func_array($this->_escape, $args);
    }
}
?>