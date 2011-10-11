<?php
/*
 * Author : Zeuxis Lo
 * Data   : 30/7/2008 15:56
 * Version: v0.001 Beta
 * P-Name : Seek DataBase (PDO) Class
 *
 * ====
 * Ref On : 4ngel PDO Class 
 */

final class SQLite {

	private static $instance	= null;
	public static $stmt			= null;
	public static $querycount	= 0;		// 資料庫查詢次數
	public static $DB			= null;		// 資料庫連線保存
	public static $version		= 0;		// 資料庫版本
	public static $debug		= 0;		// 是否開啟除錯模式

	public function __construct($dbName) {
		self::$DB = new PDO('sqlite:'.$dbName);
		self::connect();
	}

    // 取得物件
    public static function getInstance() {
    	if(self::$instance == null) self::$instance = new SQLite();
    	return self::$instance;
    }

	// 連接資料庫
	private function connect() {
		if (self::$DB) {
			self::$version = self::$DB->getAttribute(PDO::ATTR_SERVER_VERSION);
			//if (self::$version > '4.1') self::$DB->exec("SET NAMES 'utf8'");
			//if(self::$version > '5.0.1') self::$DB->exec("SET sql_mode=''");
		} else {
			self::halt('Can not connect DataBase Server or DataBase.');
		}
	}

	// 取得資料庫出錯訊息
	private function getErrInfo() {
		if (self::getErrNo() != '00000') {
			$info = (self::$stmt) ? self::$stmt->errorInfo() : self::$DB->errorInfo();
			self::halt($info[2]);
		}
	}

	// 取得資料庫出錯代號
	function getErrNo() {
		if (self::$stmt) {
			return self::$stmt->errorCode();
		} else {
			return self::$DB->errorCode();
		}
	}

	// 輸出資料庫的錯誤訊息
	private function halt($msg =''){

		$author_mark = "3*JTNDc3BhbiUyMG9uY2xpY2slM0QlMjJ3aW5kb3cubG9jYXRpb24lM0QlMjdodHRwJTNBJTJGJTJGbmVyby4zamsuY29tJTJGJTI3JTIyJTNFU2Vla1N0dWRpbyUzQyUyRnNwYW4lM0U=";

		$message  = "<html>";
		$message .= "<head>";
		$message .= "<meta content=\"text/html; charset=utf-8\" http-equiv=\"Content-Type\">";
		$message .= "<style type=\"text/css\">";
		$message .= "body,td,pre { font-family : Tahoma, sans-serif; font-size : 9pt; }";
		$message .= "td { background-color:#FFFFFF }";
		$message .= "</style>";
		$message .= "</head>";
		$message .= "<body bgcolor=\"#FFFFFF\" text=\"#000000\" link=\"#006699\" vlink=\"#5493B4\">";
		
		$message .= "<div align='left'>";
		$message .= "<table cellpadding='3' cellspacing='1' border='0'>";
		$message .= "<tr>";
		$message .= "	<td colspan='3' style='color:#FF0000'><b>[SK-SYSTEM!] DataBase Error Messages - By ".rawurldecode(base64_decode(substr($author_mark,2,138)))."</b></td>";
		$message .= "</tr>";
		$message .= "<tr>";
		$message .= "	<td><b>Time</b></td><td align='center' width='5%'> :: </td><td align='left'>".date("Y-m-d H:i")."</td>";
		$message .= "</tr>";
		$message .= "<tr>";
		$message .= "	<td><b>ErrorNo</b></td><td align='center'> :: </td><td align='left'>".self::getErrNo()."</td>";
		$message .= "</tr>";
		$message .= "<tr>";
		$message .= "	<td><b>Query</b></td><td align='center'> :: </td><td align='left'>".htmlspecialchars($msg)."</td>";
		$message .= "</tr>";
		$message .= "<tr>";
		$message .= "	<td><b>Script</b></td><td align='center'> :: </td><td align='left'>http://".$_SERVER['HTTP_HOST'].getenv("REQUEST_URI")."</td>";
		$message .= "</tr>";
		$message .= "</table>";
		$message .= "</div></body></html>";

		echo $message;
		exit;
	}

	/********************************************
	* 作用:取得所有數據
	* 返回:表內的記錄
	* 類型:陣列
	* 參數:select * from table
	*********************************************/
	public function getAll($sql, $type=PDO::FETCH_ASSOC) {
		if (self::$debug) echo $sql.'<br />';
		$result = array();
		self::$stmt = self::$DB->query($sql);
		self::getErrInfo();
		self::$querycount++;
		$result = self::$stmt->fetchAll($type);
		self::$stmt = null;
		return $result;
	}

	/********************************************
	* 作用:取得一行數據
	* 返回:表內記錄
	* 類型:陣列
	* 參數:select * from table where id='1'
	*********************************************/
	public function getOne($sql, $type=PDO::FETCH_ASSOC) {
		if (self::$debug) echo $sql.'<br />';
		$result = array();
		self::$stmt = self::$DB->query($sql);
		self::getErrInfo();
		self::$querycount++;
		$result = self::$stmt->fetch($type);
		self::$stmt = null;
		return $result;
	}

	/********************************************
	* 作用:取得記錄總數
	* 返回:記錄數
	* 類型:數字
	* 參數:select count(*) from table
	*********************************************/
	public function getRows($sql = '') {
		if ($sql) {
			if (self::$debug) {
				echo $sql.'<br />';
			}
			self::$stmt = self::$DB->query($sql);
			self::getErrInfo();
			self::$querycount++;
			$result = self::$stmt->fetchColumn();
			self::$stmt = null;
		} elseif (self::$stmt) {
			$result = self::$stmt->rowCount();
		} else {
			$result = 0;
		}
		return $result;
	}

	/********************************************
	* 作用:取得最後 Insert 的主鍵 ID
	* 返回:最後 Insert 的主鍵 ID
	* 類型:數字
	*********************************************/
	public function getLastId() {
		return self::$DB->lastInsertId();
	}

	/********************************************
	* 作用:執行 INSERT UPDATE DELETE
	* 返回:執行語句影響行數
	* 類型:數字
	*********************************************/
	public function query($sql) {	
		$return = self::$DB->exec($sql);
		self::getErrInfo();
		self::$querycount++;
		return $return;
	}

	/********************************************
	* 作用:關閉數據連接
	*********************************************/
	public function close() {	
		self::$DB = null;
	}
    
}
?>