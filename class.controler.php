<?php
abstract class controler{
	//Database Properties
	public $dbhost;
	public $dbuser;
	public $dbpass;
	public $dbname;
	public $debug;
	//Session Properties
	public $session_name;
	public $session_id;
	//Site Properties
	public $document_root;
	public $site_address;
	//Pagination Variables
	public $document_pages;
	public $current_page;
	//Time measuring Variables
	protected $measure_time_start;
	protected $measure_time_stop;
	
	public function controler(){
		$session_name = "username";
		$debug = false;
	}
	protected function login(
			$table = 'users',
			$db_user = 'username', 
			$db_pass = 'password',
			$post_user = 'username',
			$post_pass = 'password'
		){
		$conn = $this->dbConnect();
		$user_name=$_POST[$post_user];
		$pass=md5($_POST[$post_pass]); 
		$sql="SELECT $db_user, $db_pass FROM $table WHERE $db_user='$user_name';";
		if($this->debug)
			echo $sql;
		$result = mysql_query($sql,$conn);
		if($result){
			$row = mysql_fetch_array($result); 
			if(mysql_num_rows($result)>0){
				//compare the password
				if(strcmp($row[$db_pass],$pass)==0){
				session_start();
				$_SESSION[$this->session_name] = $user_name;
				return "success";
				}else{
					//Wrong Password
					return "pass";
				}
			}else{
				//Wrong Username
				return "username";
			}
		}else
			echo mysql_error($conn);
	}
	public function dbConnect(){
		$conn = mysql_connect($this->dbhost, $this->dbuser, $this->dbpass) or die ('Error connecting to mysql');
		mysql_select_db($this->dbname);
		mysql_query("SET NAMES 'utf8'");
		return $conn;
	}
	protected function dbDisconect(){
		mysql_close();
	}
	public function verify_login($start_session = true){
		if($start_session)
			session_start();
		if(isset($_SESSION[$this->session_name])){
			$this->session_id = $_SESSION[$this->session_name];
			return true;
		}else 
			return false;
	}
	protected function delete_file($file,$dir){
		chown($dir,999);
		return unlink($file);
	}
	protected function create_record($fields,$object_name,$array = false,$verify_login = true){
		if(!$verify_login || $this->verify_login()){
			if($this->dbConnect()){
				$object = new $object_name(0);
				$object->debug = $this->debug;
				if($object->create($fields,$array)){
					return $object;
				}else
					return false;
			}
		}
		return false;
	}
	protected function clean_input($input){
		return mysql_real_escape_string(trim($input));
	}
	
	protected function destroy_record($record_id,$object_name,$verify_login = true){
		if(!$verify_login || $this->verify_login()){
			if($this->dbConnect()){
				$object = new $object_name($record_id);
				$object->debug = $this->debug;
				return $object->destroy();
			}
		}
		return false;
	}
	protected function update_record($object_name,$fields,$record_id,$array = false,$verify_login = true){
		if(!$verify_login || $this->verify_login()){
			if($this->dbConnect()){
				$object = new $object_name($record_id);
				$object->debug = $this->debug;
				return $object->update($fields,$array);
			}
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
	public function paginate($class,$per_page,$page_variable,$clause = false){
		$this->dbConnect();
		$object = new $class();
		$table = $object->table_name;
		$key = $object->key;
		$clause = $clause ? "WHERE $clause" : "";
		$sql = "SELECT COUNT('$key') FROM $table $clause;";
		$result = mysql_fetch_array(mysql_query($sql));
		$count = $result[0];
		$start = (isset($_GET[$page_variable])) ? ($_GET[$page_variable]-1)*$per_page : 0;
		$end = $start + $per_page;
		$this->document_pages = ceil(($count-2) / $per_page);	
		$this->current_page = (isset($_GET[$page_variable])) ? $_GET[$page_variable] : 1;
		return ($count > $per_page) ? "$start, $end" : false;
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
	public function echo_paginate($base_link,$variable,$show_pages = false){
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
		for($i = $start;$i<=$end;$i++){
			$on = $i == $this->current_page ? "class='on'" : "";
			echo "<a href='$base_link$variable=$i' $on>$i</a> ";
		}
		if($show_pages && $show_pages < $this->document_pages){
			echo $end_print;
		}
	}
}
?>