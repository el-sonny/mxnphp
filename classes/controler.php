<?php
abstract class controler{
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
	
	public function controler($config,$security=false){
		$this->config = $config;
		$this->security = $security;
	}
	//otro comentario
	protected function do_login(
			$user_class = 'user',
			$user_field = 'email', 
			$pass_field = 'password',
			$post_user = 'username',
			$post_pass = 'password'
		){
		$this->dbConnect();
		$user_name=$_POST[$post_user];
		$pass=md5($_POST[$post_pass]);
		$user = new $user_class();	
		$user->debug = $this->debug;
		$user->search_clause = "$user_field = '$user_name'";
		$user->debug = $this->debug;
		$users = $user->read("id,$pass_field");
		if($users){
			$user = $users[0];
			//compare the password
			if($user->$pass_field == $pass){
				session_start();				
				$_SESSION[$this->config->session_name] = $user->id;				
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
	public function dbConnect(){
		$conn = mysql_connect($this->config->db_host, $this->config->db_user, $this->config->db_pass) or die ('Error connecting to mysql');
		mysql_select_db($this->config->db_name);
		mysql_query("SET NAMES 'utf8'");
		return $conn;
	}
	protected function dbDisconect(){
		mysql_close();
	}
	public function verify_login($start_session = true){
 		if($start_session)
			session_start();
		if(isset($_SESSION[$this->config->session_name])){	
			$this->session_id = $_SESSION[$this->config->session_name];
			return true;
		}else 
			return false;
	}
	protected function delete_file($file,$dir){
		chown($dir,999);
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
	protected function clean_input($input){
		return mysql_real_escape_string(trim($input));
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
	protected function make_thumb($image,$target,$width,$height){
		require_once 'ThumbLib.inc.php';
		$thumb = PhpThumbFactory::create($image);
		$thumb->adaptiveResize($width, $height);
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
	public function default_action($action){
		$this->include_template($action);
	}
	protected function include_template($template,$template_group=false){
		$file = $this->config->document_root.$this->template_folder($template_group).$template.".php";
		if(file_exists($file)){
			include $file;
		}else{
			echo $file.' does not exist';
		}
	}
	public function print_css_tag($file="main",$folder="css"){
		$css = $this->config->http_address.$this->template_folder($folder).$file.".css";
		echo "<link href='$css' rel='stylesheet' type='text/css' />";
	}
	public function print_js_tag($file="interactions",$folder="js"){
		$js = $this->config->http_address.$this->template_folder($folder).$file.".js";
		echo "<script src='$js' type='text/javascript'></script>";
	}
	public function print_img_tag($file,$alt=false,$folder="img",$class=false){
		$img = $this->config->http_address.$this->template_folder($folder).$file;
		$alt = $alt?$alt:$file;
		$class = $class?"class='$class'":"";
		echo "<img src='$img' alt='$alt' $class />";
	}
	
	public function include_theme($theme="index",$template="index",$folder="themes"){
		$this->template = $template;
		$this->include_template($theme,$folder);
	}
	protected function curl_request($url){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$feed = curl_exec($ch);
		curl_close($ch);
		return $feed;
	}
}
?>