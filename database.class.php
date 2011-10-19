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
		
		$this->connect();
		$this->selectDatabase();
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
	 * Returns true, if this database type is supported.
	 * 
	 * @return	boolean
	 */
	public static function isSupported() {
		return function_exists('mysqli_connect');
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
}
?>