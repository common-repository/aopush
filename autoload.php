<?php 

function aoph_autoloader($class_name) {
	if (strpos($class_name, 'Aopush')!==false) {
		$path = realpath(AOPH_AOPUSH_DIR) . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . $class_name . '.php';
		if (file_exists($path) && !class_exists($class_name)) {	
			require_once $path;
		}
	}
}

spl_autoload_register('aoph_autoloader');