<?php
  ob_start(); 
  define("PRIVATE_PATH", dirname(__FILE__));
  define("PROJECT_PATH", dirname(PRIVATE_PATH));
  define("PUBLIC_PATH", PROJECT_PATH . '/FVSCIS');
  define("SHARED_PATH", PRIVATE_PATH . '/shared');
  $public_end = strpos($_SERVER['SCRIPT_NAME'], '/public') + 7;
  $doc_root = substr($_SERVER['SCRIPT_NAME'], 0, $public_end);
  define("WWW_ROOT", $doc_root);

  require_once('functions.php');
  require_once('status_error_functions.php');
  require_once('db_credentials.php');
  require_once('database_functions.php');
  require_once('validation_functions.php');
	
  function my_autoload($class) {
		if(preg_match('/\A\w+\Z/', $class)) {
			include('classes/' . $class . '.class.php');
		}
	}

	spl_autoload_register('my_autoload');
  
  
	$database = db_connect();
	DatabaseObject::set_database($database);

	$el_db = db_el_connect();
	DatabaseObjectEl::set_database($el_db);

	//$fi_db = db_fi_connect();
	//DatabaseObjectFi::set_database($fi_db);

  $session = new Session;
	
?>
