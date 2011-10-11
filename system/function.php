<?php
//
function __autoload($class_name) {
	$file = ROOT_PATH.'/library/'.strtolower($class_name).'.php';

	if (is_file($file) && file_exists($file)) {
		require_once $file;
	}else{
		show_error('Can not load the file named "'.$class_name.'"');
	}
}

// Process Timer Method
function start_time() {
	$time_parts = explode(" ",microtime());
	return $time_parts[1].substr($time_parts[0],1);
}

function process_time() {
	global $start_timer;
	$time_parts = explode(" ",microtime());
	$end_time = $time_parts[1].substr($time_parts[0],1);
	return round($end_time-$start_timer,6);
}

// Error Handle Method
function &loadClass($class_name) {
	require_once ROOT_PATH."/core/".$class_name.".php";
	$error = new Exceptions();
	return $error;
}

function _exceptionHandler($severity, $message, $file_path, $line) {
	if ($severity == E_STRICT) return;
	$error = &loadClass('exceptions');
	if (($severity & error_reporting()) == $severity) $error->show_php_error($severity, $message, $file_path, $line);
}

function show_error($message, $title = 'System Error') {
	$error = &loadClass('exceptions');
	echo $error->show_error($title, $message);
	exit;
}
?>