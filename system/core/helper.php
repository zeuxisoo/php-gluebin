<?php
class Helper {	
	public static function auto_quote($string, $force = 0) {
		if(!get_magic_quotes_gpc() || $force) {
			if(is_array($string)) {
				foreach($string as $key => $val) {
					$string[$key] = self::auto_quote($val, $force);
				}
			} else {
				$string = addslashes($string);
			}
		}
		return $string;
	}

	public static function get_client_ip() {
	   if (getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"), "unknown")) {
		   $ip = getenv("HTTP_CLIENT_IP");
	   }else if (getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), "unknown")) {
		   $ip = getenv("HTTP_X_FORWARDED_FOR");
	   }else if (getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknown")) {
		   $ip = getenv("REMOTE_ADDR");
	   }else if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown")) {
		   $ip = $_SERVER['REMOTE_ADDR'];
	   }else{
		   $ip = "unknown";
	   }
	   return $ip;
	}

	public static function redirect($url, $time=0, $msg='') {
		$url = str_replace(array("\n", "\r"), '', $url);
		if(empty($msg)) $msg = "{$time}s will auto redirect to {$url}�I";
		if (!headers_sent()) {
			header("Content-Type:text/html; charset=utf-8");
			if($time === 0) {
				header("Location: ".$url);
			}else{
				header("refresh:{$time};url={$url}");
			}
			exit();
		}else{
			$str = "<meta http-equiv='Refresh' content='{$time};URL={$url}'>";
			if($time != 0) $str .= $msg;
			exit($str);
		}
	}

	public static function add_cookie($name, $value, $time) {
		global $cookie_config;
		setcookie($name, $value, $time, $cookie_config['path'], $cookie_config['domain'], ($_SERVER['SERVER_PORT'] == 443 ? 1 : 0));	
	}

	public static function date_format($dateTime, $format = '') {
		global $date_time_config;
		if (empty($format)) $format = $date_time_config['format'];
		return gmdate($format, $dateTime+$date_time_config['zone']*3600);
	}

	public static function show_message($msg, $kind = 'MSG', $url = '', $timeout = 3) {
		View::factory('show_message.html')->set(array(
			'msg' => $msg,
			'kind' => $kind,
			'url' => $url,
			'timeout' => $timeout
		))->display();
		exit;
	}
}
?>