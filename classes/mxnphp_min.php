<?php
class mxnphp_min{
	//Check scripts and compress if cached file is older
	public function mxnphp_min($config,$scripts = false, $ext = 'js', $name_base = "min-cached",  $folder = false){
		//Assign variables directly
		$this->config = $config;
		$folder = $folder ? $folder : $ext;		
		$this->name_base = $name_base ? $name_base : "min";
		$this->ext = $ext;
		//Concatenated Variables
		$this->file_root = $this->config->document_root."/templates/".$this->config->theme."/".$folder."/";
		$this->base_url = "/templates/".$this->config->theme."/".$folder."/";
		$this->cache_file = $this->name_base.".".$this->ext.".php";
		//Variables obtained through functions
		$this->scripts = $scripts ? $scripts : $this->get_all_scripts();
		
		//Check if cache file exists
		if(!file_exists($this->file_root.$this->cache_file)){
			$this->combine_and_compress($scripts);			
		//Check if There are updates newer than the cached file
		}else if($this->check_for_updates()){
			$this->combine_and_compress($scripts);			
		}else{
			//No updates so no compression is necesary
		}
	}
	//Print the cached files corresponding HTML tag, if dev_mode is enabled in config file print the tags for each of the scripts instead
	public function tag($type = "js"){
		$scripts = $this->config->dev_mode ? $this->scripts : array($this->cache_file);		
		foreach($scripts as $script){
			if($type == 'js'){
				echo "<script src='{$this->base_url}{$script}' type='text/javascript' ></script>";
			}else if($type == 'css'){
				echo "<link type='text/css' rel='stylesheet' href='{$this->base_url}{$script}' />";			
			}else{
				//error
			}
		}
	}
	//Check if there are updates newer than the cached file
	private function check_for_updates(){
		$dir = opendir($this->file_root);
		$i = 0;
		foreach($this->scripts as $script){
			if(file_exists($this->file_root.$script)){
				$scripts[$i] = $script;
				$scripts_date[$i++] =  filemtime($this->file_root.$script);
			}
		}
		$cache_date = filemtime($this->file_root.$this->cache_file);
		$max_date = max($scripts_date);
		return $cache_date < $max_date;
	}
	//Concatenate and compress scripts
	private function combine_and_compress(){
		$contents = "";		
		foreach($this->scripts as $script){
			$contents .= file_exists($this->file_root.$script) ? file_get_contents($this->file_root.$script) : "";
		}
		$output = gzencode($contents,9);
		$type = $this->ext == "js" ? "application/javascript" : "text/css";
		$output = "<?php 
header('Content-type: $type; charset: UTF-8'); 
header('Vary: Accept-Encoding');
header('Content-Encoding: gzip');
?>".$output;		
		
		file_put_contents($this->file_root.$this->cache_file,$output);
	}
	//Get all of the scripts in the file_root
	private function get_all_scripts(){
		$dir = opendir($this->file_root);
		$i = 0;
		while (false !== ($file = readdir($dir))){
			if ($file != "." && $file != ".." && !is_dir($this->file_root.$file)) {
				$scripts[$i++] = $file;
			}
		}
		closedir($dir);
		return $scripts;
	}
}
?>