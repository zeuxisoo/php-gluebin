<?php
class Admin {

	function quit($message) {
		showError($message);
	}

	function import($f, $k = '') {
		switch($k) {
			case 'tpl':
				$path = ACP_ROOT . 'template/' . $f;
				break;
			case 'sys':
				$path = DB_DIR . 'system/' . $f;
				break;
			case 'txt':
				$path = DB_DIR . 'text/' . $f;
				break;
			default:
				$path = ACP_ROOT . $f;
				break;
		}
		if (!is_file($path)) {
			showError('Cant Not Load ['.$f.'] File From Admin Data');
		}else{
			return $path;
		}
	}

	function showMsg($msg, $kind = 'MSG', $url = '', $timeout = 3) {
		global $tpl;
		include_once $tpl->display('showmsg.html');
		exit;
	}

	function authCode($string, $operation = 'ENCODE') {
		return ($operation == 'DECODE' ? base64_decode($string) : base64_encode($string));
	}

	function makeURL($sk, $op, $action = '', $otherArr = array()) {
		$otherURL = '';
		if (!empty($otherArr) && is_array($otherArr)) {
			foreach($otherArr as $k => $v) $otherURL .= "&{$k}={$v}";
		}
		return "?sk={$sk}&op={$op}&action={$action}".$otherURL;
	}

	function sizeConvert($fileSize,$unit=array('Bytes','KB','MB','GB','TB','PB','EB','ZB','YB')) {
		return@round($fileSize/pow(1024,$i=floor(log($fileSize,1024))),2).' '.$unit[$i];
	}

	function dirSize($dir) { 
		@$dh = opendir($dir);
		$size = 0;
		while ($file = @readdir($dh)) {
			if ($file == '.' || $file == '..') continue;
			$path  = $dir."/".$file;
			$size += @is_dir($path) ? $this->dirSize($path) : filesize($path);
		}
		@closedir($dh);
		return $size;
	}

	function serverLoading() {
		if(strtolower(substr(PHP_OS, 0, 3)) === 'win') {

			return '(Not Support Window)';

		}elseif(@file_exists("/proc/loadavg")) {
			$load = @file_get_contents("/proc/loadavg");
			$serverload = explode(" ", $load);
			$serverload[0] = round($serverload[0], 4);
			if(!$serverload) {
				$load = @exec("uptime");
				$load = split("load averages?: ", $load);
				$serverload = explode(",", $load[1]);
			}
		} else {
			$load = @exec("uptime");
			$load = split("load averages?: ", $load);
			if (array_key_exists(1, $load)) {
				$serverload = explode(",", $load[1]);
			}else{
				return '(No Permission)';
			}
		}
		
		$returnload = trim($serverload[0]);
		
		if(!$returnload) $returnload = 'Unknown';
		
		return $returnload;
	}

	function getPHPCfg($varname) {
		switch($result = get_cfg_var($varname)) {
			case 0: return "OFF"; break;
			case 1: return "ON"; break;
			default: return $result; break;
		}
	}

	function getWebServer() {	
		$sapi_name = php_sapi_name();
		$wsregs = array();
		if (preg_match('#(Apache)/([0-9\.]+)\s#siU', $_SERVER['SERVER_SOFTWARE'], $wsregs)) {
			$webserver = "$wsregs[1] v$wsregs[2]";
			if ($sapi_name== 'cgi' OR $sapi_name== 'cgi-fcgi') {
				$webserver .= " ($sapi_name)";
			}
		}
		else if (preg_match('#Microsoft-IIS/([0-9\.]+)#siU', $_SERVER['SERVER_SOFTWARE'], $wsregs)) {
			$webserver = "IIS v$wsregs[1] $sapi_name";
		}
		else if (preg_match('#Zeus/([0-9\.]+)#siU', $_SERVER['SERVER_SOFTWARE'], $wsregs)) {
			$webserver = "Zeus v$wsregs[1] $sapi_name";
		}
		else if (strtoupper($_SERVER['SERVER_SOFTWARE']) == 'APACHE')
		{
			$webserver = 'Apache';
			if ($sapi_name== 'cgi' OR $sapi_name== 'cgi-fcgi') {
				$webserver .= " ($sapi_name)";
			}
		}
		else {
			$webserver = $sapi_name;
		}

		return $webserver;
	}

	function loadHeader($editor = 0, $editorElement = null) {
		include_once ACP_ROOT . 'template/header.php';
	}

	function loadFooter($editor = 0) {
		include_once ACP_ROOT . 'template/footer.php';
	}

	function strongText($text, $color, $bold = true) {
		$style = "color:{$color};";
		$style.= $bold ? "font-weight:bold" : "";
		return "<span style='{$style}'>{$text}</span>";
	}

}
?>