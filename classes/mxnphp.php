<?php 
/**
* Class mxnphp
*
* Selecciona la configuración, modelo y control 
* que va a cargar el framework.
* 
*/
class mxnphp{
	/** 
	* @var $config configuración que se cargara.
	*/
	public $config;
	/**	
	* @var $security carga el controlador de seguridad.
	*/ 
	public $security;
	/**
	* Función mxnphp
	*
	* Carga la configuración de usuario 
	* 
	* @param string $config nombre de la 
	* configuración a utilizar.
	* Se tomara la configuración default en 
	* caso de que la $config sea falso.
	* 
	*/
	public function mxnphp($config = false){
		if(!$config)
			$this->config = new default_config();
		else
			$this->config = $config;
	}
	/**
	* Función load_model
	*
	* Carga el modelo que se utilizara 
	* 
	* @param string $model_name  
	* nombre de el modelo a utilizar.
	* comprueba si el parametro existe 
	* de lo contrario se toma el default
	* que es general.
	*
	*/
	public function load_model($model_name = "general"){
		$file = $this->config->document_root."/models/model.$model_name.php";
		if(file_exists($file)){
			include_once $file;
		}
	}
	/**
	* Function load_controler
	*
	* Carga el controlador que se utilizara 
	* al igual que el controlador de seguridad
	* 
	*
	*/
	public function load_controler(){
		$controler_name = isset($_GET['controler']) ? $_GET['controler'] : $this->config->default_controler;
		$action = isset($_GET['action']) ? $_GET['action'] : $this->config->default_action;
		if(class_exists($controler_name)){						
			$security = ($this->config->secured) ? new $this->config->security_controler($this->config):false;
			$controler = new $controler_name($this->config,$security);
			if(method_exists($controler,$action)){
				$controler->$action();
			}else{
				header("HTTP/1.0 404 Not Found");
				//echo $controler->default_action($action);
			};
		}else{
			header("HTTP/1.0 404 Not Found");
			//echo "<p>$controler_name does not exist</p>";
		}
	}
}
?>