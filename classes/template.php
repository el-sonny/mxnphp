<?php
class template{
	private $file_data;
	private $file;
	private $render;
	
	public function template($filename){
		$this->load_file($filename);
		$this->set_template_variables();
	}
	private function load_file($filename){	
		$fh = fopen($filename, 'r');
		$this->file_data = fread($fh, filesize($filename));
	}
	public function render($variables = false){
		$scanner = new template_scanner($this->file_data);
		$parser = new template_parser($scanner);
		if($variables){
			$vars = array_merge($this->template_variables,$variables);
		}else{
			$vars = $this->template_variables;
		}
		$this->render = $parser->parse($vars);
	}
	public function save_render($filename){
		$fh = fopen($filename, 'w') or die("can't open file");
		$result = fwrite($fh,$this->render);
		return $result;
	}
	public function echo_render(){
		echo $this->render();
	}
}
class template_parser{
	private $scanner;
	public function template_parser($scanner){
		$this->scanner = $scanner;
	}
	public function parse($variables){
		$this->scanner->scan();
		$translation = "";
		while($token = $this->scanner->next_token()){
			switch($token->type){
				case "string":
					$string = $token->data;
				break;
				case "variable":
					$string = $variables[$token->data];
				break;
			}
			$translation .= $string;
		}
		return $translation;
	}
}
class template_scanner{
	private $current_token = 0;
	private $current_char = 0;
	private $tokens;
	private $data;
	private $file_array;
	private $max_variable_size = 100;
	public function template_scanner($data){
		$this->data = $data;
		$this->file_array = str_split($data);
	}
	public function scan(){	
		$string;
		$i = 0;
		while($ch = $this->next_char()){
			if($ch == "{"){
				$ch2 = $this->next_char();
				if($ch2 == ":"){
					$string = implode($string);
					$this->tokens[$this->current_token++] = new token($string,"string");
					unset($string);
					$i = 0;
					$this->scan_variable();
				}else if(!$ch2){
					break;
				}else{
					$string[$i++] = $ch;
					$string[$i++] = $ch2;
				}
			}else{
				$string[$i++] = $ch;
			}
		}
		if(count($string)){
			$string = implode($string);
			$this->tokens[$this->current_token++] = new token($string,"string");
		}
		$this->current_token = 0;
	}
	private function scan_variable(){
		$scanning = true;
		$i = 0;
		$string;
		while($scanning){
			$ch = $this->next_char();
			if($ch == ":" ){
				$ch2 = $this->next_char();
				if($ch2 == "}"){
					$scanning = false;
					$string = implode($string);
					$this->tokens[$this->current_token++] = new token($string,"variable");				
				}else if(!$ch2){
					break;
				}else{
					$string[$i++] = $ch;
					$string[$i++] = $ch2;
				}
			}else{
				$string[$i++] = $ch;
			}
		}
	}
	private function next_char(){
		if(isset($this->file_array[$this->current_char])){
			return $this->file_array[$this->current_char++];
		}else{
			return false;
		}
	}
	public function next_token(){
		if(isset($this->tokens[$this->current_token])){
			return $this->tokens[$this->current_token++];
		}else{
			return false;
		}
	}
}
class token{
	public $data;
	public $type;
	public function token($data,$type){
		$this->data = $data;
		$this->type = $type;
	}	
}
?>