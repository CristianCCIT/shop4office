<?php
/**
 * Created by PhpStorm.
 * User: Cristian
 * Date: 5/7/2015
 * Time: 7:46 AM
 */


function __autoload($class){
	$_exists = false;
	$_class = null;
	$_paths = array(
		root.'php'.DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR,
		root.'php'.DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR,
		DIR_WS_CLASSES
	);

	foreach($_paths as $_path){
		if(!$_exists){
			$_class = implode('', array($_path, implode(DIRECTORY_SEPARATOR,explode('_', $class)), '.php'));
			if(file_exists($_class)){
				$_exists = true;
			}
		}
	}

	if($_exists){
		require_once($_class);
		return true;
	}

	return false;
}