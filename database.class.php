<?php
/**
 *
 * @author		Gianluca Raberger
 * @version 	1.0
 * @copyright	2011 Gianluca Raberger <http://www.gianluca311.com>
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
 
class Database {
	
	public $errorLogging			= true;
	public $errorLoggingDirectory	= null;
	public $charset					= "utf8";
	
	
	protected $mySQLi;
	protected $result;
	protected $host;
	protected $user;
	protected $password;
	protected $database;
	

	public function __construct($dbc) {
		$this->host = $dbc['host'];
		$this->user = $dbc['username'];
		$this->password = $dbc['password'];
		$this->database = $dbc['dbname'];
		$this->errorLoggingDirectory = dirname(__FILE__).'/logs/';
		if($this->test() === true) {
			$this->connect();
			$this->selectDatabase();
		}
		else
			$this->error('Initial test failed');
	}
	
	/**
	 * Connects to MySQL Server
	 */
	protected function connect() {
		$this->mySQLi = new MySQLi($this->host, $this->user, $this->password);
		if (mysqli_connect_errno()) {
			$this->error('Connection failed to database host');
		}
		
		// set connection character set
		if (!empty($this->charset)) {
			$this->setCharset($this->charset);
		}
	}

	/**
	 * Sets the charset of the database connection.
	 * 
	 * @param	string		$charset
	 */
	public function setCharset($charset) {
		$this->mySQLi->set_charset($charset);
	}

	/**
	 * Selects a MySQL database.
	 */
	protected function selectDatabase() {
		if ($this->mySQLi->select_db($this->database) === false) {
			$this->error("Cannot select database");
		}
	}

	/**
	 * Returns MySQL error number for last error.
	 *
	 * @return 	integer		MySQL error number
	 */
	public function getErrorNumber() {
		return $this->mySQLi->errno;
	}

	/**
	 * Returns MySQL error description for last error.
	 *
	 * @return 	string		MySQL error description
	 */
	public function getErrorDesc() {
		return $this->mySQLi->error;
	}

	/**
	 * Sends a database query to MySQL server.
	 *
	 * @param	string		$query 		a database query
	 * @return 	integer				id of the query result
	 */
	public function sendQuery($query) {		
		$this->result = $this->mySQLi->query($query);
		if ($this->result === false) {
			$this->error("Invalid SQL: ".$query.", ".$this->getErrorDesc());
		}
		
		return $this->result;
	}

	/**
	 * Gets a row from MySQL database query result.
	 *
	 * @param				$result	
	 * @param	integer		$type 		fetch type
	 * @return 	array				a row from result
	 */
	public function fetchArray($result = null, $type = null) {
		if ($result !== null) $this->result = $result;

		if ($type === null) {
			$type = MYSQLI_ASSOC;
		}

		$row = $this->result->fetch_array($type);
		
		return $row;
	}
	
	/**
	 * Counts number of rows in a result returned by a SELECT query.
	 *
	 * @param			$result	
	 * @return 	integer				number of rows in a result
	 */
	public function countRows($result = null) {
		if ($result !== null) $this->result = $result;
		
		return $this->result->num_rows;
	}
	
	/**
	 * Counts number of affected rows by the last sql statement (INSERT, UPDATE or DELETE).
	 *
	 * @return 	integer				number of affected rows
	 */
	public function getAffectedRows() {
		return $this->mySQLi->affected_rows;
	}

	/**
	 * Returns ID from last insert.
	 *
	 * @param 	string		$table
	 * @param	string		$field
	 * @return 	int		last insert ID
	 */
	public function getInsertID($table = '', $field = '') {
		return $this->mySQLi->insert_id;
	}


	/**
	 * Returns the mysql version.
	 * 
	 * @return 	string
	 */
	public function getVersion() {
		return $this->mySQLi->server_info;
	}
	
	/**
	 * Alias for mysqli::escape_string()
	 * @see mysqli::escape_string()
	 */
	public function escapeString($string) {
		return $this->mySQLi->escape_string($string);
	}
	
	
	/**
	 * Error logging
	 *
	 * @param	string	errorTitle
	 * @param	string	errorMessage
	 */
	 
	 protected function error($errorTitle, $errorMessage = NULL) {
		if($this->errorLogging === true) {
			$file = fopen($this->errorLoggingDirectory."errorlog-".date("d.m.Y").".log", "a+");
			fwrite($file, "[".date("d/m/Y H:i:s")."]: ".$errorTitle."\r\n".$errorMessage."\r\n\r\n");
			fclose($file);
		}
		die("[".$errorTitle."] ".$errorMessage);
	}
	
	/**
	 * Initial test
	 *
	 */
	 
	 protected function test() {
		$errorTotal = 0;
		$errors = array();
		
		//checking PHP Version
		if(strnatcmp(phpversion(),'5.2.10') < 0) {
			$errorTotal++;
			$errors[] = "PHP Version is older than 5.2.10";
		}
		if(!function_exists('mysqli_connect')) {
			$errorTotal++;
			$errors[] = "MySQLi is not supported";
		}
		if(!is_writeable($this->errorLoggingDirectory)) {
			$errorTotal++;
			$errors[] = "Error logging directory is not writeable";
		}
		
		if($errorTotal == 0)
			return true;
		else {
			?> <!DOCTYPE html>
<html>
<head>
  <meta charset='utf-8'>
  <title>phpMySQLiDatabase-class</title>
  <style type="text/css">
	body {
		margin: 0;
		font-family: Helvetica, Arial, FreeSans, san-serif;
		color: #000000;
	}
	#header {
		width: 100%;
		margin-top: 5px;
		font-size: 0.8em;
		padding-bottom:5px;
		border-bottom:1px solid #000;
	}
	#container {
		margin: 0 auto;
		width: 700px;
		padding-top: 40px;
		font-size: 0.8em;
	}
  </style>
</head>
<body>
	<div id="header">
		<strong>&nbsp;phpMySQLiDatabase-class</strong>
	</div>
	<div id="container">
		<h2>Error while testing</h2>
		<table>
		<?php
		foreach($errors as $values) {
		?>
		<tr><td><img src="data:image/gif;base64,R0lGODlhEAAQANUAAO8AAP3ExPdBMfkoI/5RUfEaE//x8e4SEv9GQvcyMv7q6v8AAP9pXPhKO/k6
OvYcF/tWSP5hVPUJCf8TE/ouKfUfGf/39/dKQv/MzP8zM/xKSv1eUf5mWfwoKPYQEO8XF/okHvcA
APw7N/wmIftXSfpSQ/9aV////wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA
AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACH5BAEHACcALAAAAAAQABAAAAaQwJNw
SCwWJ4ukcmI8TToMjpQTGXmOUERmSxFtQFfhk2HCECgUDUYDAQsXHATGYNBoFAYMpQR4czIEeHR0
Dg8NfScLERlpggYOHh6HbxEdHXeDkJKICxsdCI4GCgkSApwbGXMKDg54AQ+mQh4gJHKaCQEJAgUf
Q7MlA5EeErC8RR4VDcoCzMZGBwDR0gdN1UVBADs=" /></td><td><?php echo $values; ?></td></tr>
		<?php } ?>
		</table>
	</div>
</body>
</html><?php exit();
		}
	 }
}
?>