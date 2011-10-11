<?php
// Stop Output Error
error_reporting(E_ALL & ~E_NOTICE);

// Set Output Page of Header Encoding
header('Content-Type: text/html; charset=utf-8');

// Set Gzip Handler
function_exists('ob_gzhandler') ? ob_start('ob_gzhandler') : ob_start();

// Check Request
if (isset($_REQUEST['GLOBALS']) OR isset($_FILES['GLOBALS'])) exit('Request tainting attempted.');

// Check Version
if(version_compare(PHP_VERSION, '6.0.0', '<')) @set_magic_quotes_runtime(0);

// Define Constant For Global Env
$path_info = pathinfo(__FILE__);
if (!defined('ROOT_PATH')) define('ROOT_PATH', $path_info['dirname'].DIRECTORY_SEPARATOR);
if (!defined('APP_PATH')) define('APP_PATH', substr($path_info['dirname'], 0, -7).DIRECTORY_SEPARATOR);
if (!defined('E_STRICT')) define('E_STRICT', 2048);

unset($path_info);

// Load Core
require_once ROOT_PATH."/config.php";
require_once ROOT_PATH."/function.php";
require_once ROOT_PATH."/core/view.php";
require_once ROOT_PATH."/core/helper.php";
require_once ROOT_PATH."/core/sqlite.php";

// Initial Timer
$start_timer = start_time();

// Set Time Zone
if(function_exists('date_default_timezone_set')) date_default_timezone_set($time_zone);

// Set Error Handler
set_error_handler("_exceptionHandler");

// Magic Control
if (!get_magic_quotes_gpc() && $_FILES) $_FILES = Helper::auto_quote($_FILES);

// Const Definition
define('ERR', 'ERR');
define('MSG', 'MSG');

//
class Controller {
	protected $db = null;
	protected $params = array();

	public function __construct() {
		global $base_config;

		// Configure SQLite
		$this->db = new SQLite($base_config['databse']);


		foreach(array($_COOKIE, $_POST, $_GET) as $_request) {
			foreach($_request as $_key => $_value) {
				$this->params[$_key] = Helper::auto_quote($_value);
			}
		}
	}
}
?>