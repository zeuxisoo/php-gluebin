<?php
class Exceptions {
	private $obLevel;
	private $levelTable = array(
						E_ERROR				=>	'Error',
						E_WARNING			=>	'Warning',
						E_PARSE				=>	'Parsing',
						E_NOTICE			=>	'Notice',
						E_CORE_ERROR		=>	'Core::Error',
						E_CORE_WARNING		=>	'Core::Warning',
						E_COMPILE_ERROR		=>	'Compile::Error',
						E_COMPILE_WARNING	=>	'Compile::Warning',
						E_USER_ERROR		=>	'User::Error',
						E_USER_WARNING		=>	'User::Warning',
						E_USER_NOTICE		=>	'User::Notice',
						E_STRICT			=>	'Runtime Notice'
					);
	

	public function __construct() {
		$this->obLevel = ob_get_level();
	}

	public function show_error($heading, $message, $template = 'error_general') {
		$message = '<p>'.implode('</p><p>', is_array($message) ? $message : array($message)).'</p>';

		if (ob_get_level() > $this->obLevel + 1) ob_end_flush();	

		ob_start();
		include_once ROOT_PATH.'/error/'.$template.'.php';
		$buffer = ob_get_contents();
		ob_end_clean();
		echo $buffer;
	}

	public function show_php_error($severity, $message, $filePath, $line) {	
		$severity = isset($this->levelTable[$severity]) ? $this->levelTable[$severity] : $severity ;
		$filePath = str_replace("\\", "/", $filePath);

		if (strPos($filePath, '/') !== false) {
			$x = explode('/', $filePath);
			$filePath = $x[count($x) - 2].'/'.end($x);
		}
		
		if (ob_get_level() > $this->obLevel + 1) {
			ob_end_flush();	
		}

		ob_start();
		include_once ROOT_PATH.'/error/error_php.php';
		$buffer = ob_get_contents();
		ob_end_clean();
		echo $buffer;
	}

	private function display_error_page($path) {
		ob_start();
		include_once ROOT_PATH.'/error/'.$path.'.php';
		$buffer = ob_get_contents();
		ob_end_clean();
		echo $buffer;	
	}
}
?>