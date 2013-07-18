<?php 
/**
* Class mxnphp
*
* Selecciona la configuracion, modelo y control 
* que va a cargar el framework.
* 
*/
class mxnphp{
	/** 
	* @var $config configuracion que se cargara.
	*/
	public $config;
	/**	
	* @var $security carga el controlador de seguridad.
	*/ 
	public $security;
	/**
	* Función mxnphp
	*
	* Carga la configuracion de usuario 
	* 
	* @param string $config nombre de la 
	* configuración a utilizar.
	* Se tomara la configuracion default en 
	* caso de que la $config sea falso.
	* 
	*/

	protected $__mxnphp_classes_loaded__;

	public function __construct($config = false){
		if(!$config)
			$this->config = new default_config();
		else
			$this->config = $config;
	}
	/**
	* Funcin load_model
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
		global $__mxnphp_classes_loaded__ ;
		$controler_name = isset($_GET['controler']) ? $_GET['controler'] : $this->config->default_controler;
		$this->config->controler = $controler_name;
		$action = isset($_GET['action']) ? $_GET['action'] : $this->config->default_action;
		$controler_name = str_replace("-","_",$controler_name);
		$action = str_replace("-","_",$action);
		mxnphp_registry::set('__mxnphp_config__',$this->config);
		mxnphp_registry::set('__mxnphp_security__',$this->security);
		if(class_exists($controler_name)){
			$security = isset($this->config->secured) && $this->config->secured ? new $this->config->security_controler($this->config):false;
			$controler = new $controler_name($this->config,$security);
			$controller_loaded = isset($__mxnphp_classes_loaded__[$controler_name]) && $__mxnphp_classes_loaded__[$controler_name]=="controller"?true:false;
			if($controller_loaded){
				$event = new event(array("controler" => &$controler_name, "action" => &$action));
				$controler->dispatch_event("pre_method",$event);
				if(method_exists($controler,$action)){
					$controler->$action();
				}else{
					//echo "template not found";
					// $controler->config->document_root = $this->config->document_root."public/";
					$controler->default_action($action);
				}
			}else{
				if(isset($this->config->redirect) && $this->config->redirect){
					header('location: '.$this->config->redirect);
				}else echo "<p>$controler_name does not exist</p>";	
			}
		}else{
			if(isset($this->config->redirect) && $this->config->redirect){
					header('location: '.$this->config->redirect);							
					//header("HTTP/1.0 404 Not Found");
			}else
				echo "<p>$controler_name does not exist</p>";
		}
	}
}
?>