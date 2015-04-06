<?php
/**
 *
 * @author	Gianluca Raberger
 * @version 	1.3
 * @copyright	2015 Gianluca Raberger <http://www.grdev.io>
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
namespace DUER\System;

class Database {

    public $errorLogging		= true;
    public $errorLoggingDirectory	= null;
    public $charset			= "utf8";


    protected $mySQLi;
    protected $result;
    protected $dbc;

    private static $instance;

    public static function getInst() {
        if(self::$instance === null) {
            self::$instance = new Database;
        }

        return self::$instance;

    }

    public function __construct() {
        include("config.php");
        $dbcA = null;
        foreach($dbc as $key => $value) {
            $dbcA[$key] = $value;
            $this->dbc = $dbc;
        }

        $this->errorLoggingDirectory = 'logs/';
        if($this->preCheck() === true) {
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
        $dbc = $this->dbc;
        $this->mySQLi = new \MySQLi($dbc['host'], $dbc['username'], $dbc['password']);
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
        $dbc = $this->dbc;
        if ($this->mySQLi->select_db($dbc['dbname']) === false) {
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
     * @param	boolean		$debug		debug on or off.
     * @return 	integer				id of the query result
     */
    public function sendQuery($query, $debug = false) {
        $this->result = $this->mySQLi->query($query);
        if ($this->result === false) {
            $this->error("Invalid SQL: ".($debug == true ? $query."," : $this->getErrorDesc()));
        }
        if($debug == true) {
            echo "[Debug SQL-Query: ".$query."]<br />";
            $this->debugSave($query);
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
     * @param	integer				$result
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

    public function escapeArray($array) {
        foreach($array as $key => $value) {
            $array[$key] = $this->escapeString($value);
        }

        return $array;
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


    protected function debugSave($errorMessage = NULL) {
        if($this->errorLogging === true) {
            $file = fopen($this->errorLoggingDirectory."errorlog-".date("d.m.Y").".log", "a+");
            fwrite($file, "[".date("d/m/Y H:i:s")."]: DEBUG\r\n".$errorMessage."\r\n\r\n");
            fclose($file);
        }
    }


    /**
     * Initial test
     *
     */
    protected function preCheck() {
        $errorTotal = 0;
        $errors = array();

        //checking PHP Version
        if(!(version_compare(PHP_VERSION, '5.3.0') >= 0)) {
            $errorTotal++;
            $errors[] = "PHP Version is older than 5.3.0";
        }
        if(!function_exists('mysqli_connect')) {
            $errorTotal++;
            $errors[] = "MySQLi is not supported";
        }
        if(!is_writeable($this->errorLoggingDirectory) && $this->errorLogging === true) {
            $errorTotal++;
            $errors[] = "Error logging directory (".$this->errorLoggingDirectory.") is not writeable";
        }

        if($errorTotal == 0)
            return true;
        else {
            ?>
            <!DOCTYPE html>
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
            </html>
            <?php exit();
        }
    }
}
?>

