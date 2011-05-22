<?php

/**
 *  class database, for mysql processing.
 * 
 *  @package class mysql_database
 *
 */
/**
 *  requiring driverinterface.php
 *
 */
require_once("driverinterface.php");

/**
 *   class database, for mysql processing.
 * 
 * @package mysql_database
 *      
 *    
 */
class database implements driverinterface {

	protected $host;
	protected $dbName;
	protected $username;
	protected $password;
	protected $printer;
	protected $connection;
	protected $hasError;
	protected $inTransaction;
	protected $ddl_rollback;
	protected $is_new;
	
	/**
	 *   __construct function, describes environment and sets up connection.
	 *
	 */
	public function __construct($host, $db, $username, $password, printer $printer) {
		$this->host = $host;
		$this->dbName = $db;
		$this->username = $username;
		$this->password = $password;

		$this->printer = $printer;

		$this->hasError = false;
		$this->inTransaction = false;
		$this->applyBase = false;

		$this->connection = new mysqli($this->host, $this->username, $this->password);

		if(mysqli_connect_error())
			throw new exception("Failed to connect to the database (".mysqli_connect_errno().")");
		
		if($this->connection->select_db($this->dbName) === false) {
			$this->createDatabase();
			if($this->connection->select_db($this->dbName) === false) {
				throw new exception("Failed to connect to the database {$this->dbName}");	
			}
		}
	}

	/**
	 *  function close: closes db connection, no parms, no return.
	 *
	 */
	public function close() {
		$this->connection->close();
	}

	/**
	 *  function doesTransactions:  says, yes, MySQL is transactional.  NOTE
	 *  that it is only transactional if you inform that the table is so,
	 *  and in order process correctly autocommit must be OFF.
	 *
	 */
	public function doesTransactions() {
		return false;
	}

	/**
	 *  function startTransaction:  SQL to do BEGIN transaction
	 *
	 */
	public function startTransaction() {
		$this->hasError = false;
		$this->inTransaction = true;
		$sql = "BEGIN";
		$this->execute($sql);
	}

	/**
	 *  function completeTransaction:  finish up -  If dryrun or error,
	 *  do rollback, else commit and return a boolean on status of work.
	 *
	 */
	public function completeTransaction() {
		if ($this->inTransaction) {
			if ($this->hasError === true) {
				$sql = "ROLLBACK";
				$this->execute($sql);
				if (count($this->ddl_rollback) > 0) {
					$this->rollbackDDL();
				}
				$return = false;
			} else {
				$sql = "COMMIT";
				$this->execute($sql);
				$return = true;
			}


			return $return;
		} else {
			return true;
		}
	}

	/**
	 *  function failTransaction:  just say it's bad for downstream.
	 *
	 */
	public function failTransaction() {
		if ($this->inTransaction)
			$this->hasError = true;
	}

	/**
	 *  function checkForDBVersion:  make sure the dbversion table is there
	 *  and create if not.
	 *
	 */
	public function checkForDBVersion() {
		$sql = "show tables like 'dbversion'";
		$result = $this->rowExists($sql);
		if ($result == false) {
			$sql = "CREATE TABLE dbversion ( applied_patch
		varchar(255) NOT NULL, date_patch_applied date ) ENGINE=InnoDB";
			$this->execute($sql);
		}
	}

	/**
	 * function rowExists: generic test for existing rows in table.
	 *
	 */
	protected function rowExists($sql) {
		$result = $this->execute($sql);
		if ($result) {
			$numRows = $result->num_rows;

			if ($numRows > 0) {
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	/**
	 * function get_applied_patches:  returns a 2 dimensional array of of patch data back to the client code
	 * Returns a list of applied patches.
	 * @return array
	 */

	public function get_applied_patches() {
		$return_array = array();
		$sql = "select * from dbversion";

		$results = $this->execute($sql);
		if (!empty($results)) {
			while ($row = $results->fetch_assoc()) {
				$return_array[] = $row['applied_patch'];
			}
		}

		return $return_array;
	}


	/**
	 *  function checkVersion:  see if this version is already in dbversion
	 *  table and if so, that is an indication that it already ran, stop
	 *  processing.
	 *
	 */
	public function checkVersion($versionID) {
		$versionSQL = "select * from dbversion where db_version_id = '%s'";

		$sql = sprintf($versionSQL, $this->connection->escape_string($versionID));

		if ($this->rowExists($sql)) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 *  function insertVersion:  assuming all is good, insert the version
	 *  info into the dbversion table.
	 *
	 */
	public function insertVersion($id, $date) {
		$versionInsertSQL = "INSERT INTO dbversion VALUES ('%s','%s')";
		$sql = sprintf($versionInsertSQL,
			$this->connection->escape_string($id),
			$this->connection->escape_string($date));

		return $this->execute($sql);
	}

	/**
	 *  function execute: generic sql processor function, with wto if
	 *  bad.
	 *  @param string $sql The sql statement to run.
	 *  @return mixed Returns FALSE on SQL execution error. If exactly one of the SQL statements returns
	 *          results, returns that MySQLi_Result object. If more than one of the SQL statements returns
	 *          results, returns an array of MySQLi_Result objects. If the execution was successful, but
	 *          no results were returned (e.g. INSERT statements), returns TRUE.
	 *  @todo Suggest write to printer altered to feedback back to
	 *  calling interface, separating interface from logic and execution.
	 *
	 */
	public function execute($sql) {
		$this->printer->write("executing statement:", 2);
		$this->printer->write($sql, 2);
		
		// Execute the SQL statement(s)
		$this->connection->multi_query($sql);
		if($this->connection->error) {
			$this->hasError = true;
			$this->printer->write('SQL error: '.$this->connection->error, 1);
			return false;
		}
		
		// Retrieve result set
		$results = array();
		do {
			if($r = $this->connection->store_result()) {
				$results[] = $r;
			}
		} while($this->connection->next_result());
		
		// If none of the statements return results, but the execution was successful, return true.
		// If there was one result, return it. If there were more than one result-returning
		// statements, return all of their results as an array.
		if(count($results) === 0) {
			return true;
		} else if(count($results) === 1) {
			return $results[0];
		} else {
			return $results;
		}
	}

	/**
	 *  function getError:  generic MySQL error.
	 *  @return string The error message itself.
	 *
	 */
	public function getError() {
		return $this->connection->error;
	}

	/**
	 * Returns a boolean if the db object has an error or not
	 *
	 * @return bool
	 */
	public function has_error() {
		return $this->hasError;
	}

	/**
	 *   function addRollBack: insert new ddl for dryrun or rollback
	 *   processing into the hold array for ddl undo statements.
	 *   @param string $ddl_rollback sql statement to execute if the
	 *   process is to fail.
	 *
	 *
	 */
	public function addRollBack($ddl_rollback) {
		$this->ddl_rollback[] = (string) $ddl_rollback;
	}

	/**
	 * function rollBackDDL:  Do the rollback of DDL if needed.
	 *
	 */
	public function rollBackDDL() {
		foreach ($this->ddl_rollback as $sql_ddl) {
			$this->execute($sql_ddl);
		}
	}
	
	/**
	 * function isNewDB: Tells if the base schema should be applied. Like is ten case when the datbase is created
	 *
	 */
	public function isNewDB() {
		return ($this->is_new) ? true : false;
	}
	
	public function getHost () {
		return $this->host;
	}
	
	public function getUser () {
		return $this->username;
	}
	
	public function getPassword () {
		return $this->password;
	}
	
	public function getDBName () {
		return $this->dbName;
	}
	/**
	 * function createDatabase: Attempts to creates the datbase if it does not exists
	 */
	private function createDatabase () {
		$answer = $this->printer->ask("Database {$this->dbName} does not exist. Want to create the database right now? (y/n)");
		if ($answer == 'y') {
			if ($this->connection->query("CREATE DATABASE {$this->dbName}")) {
				$this->printer->write("Database created");
				$this->is_new = true;
			} else {
				throw new exception("Error creating database: " . $this->connection->error);
			}
		} elseif ($answer == 'n') {
			$this->printer->write("The database was not created. Process aborted.");
			die;
		} else { 
			$this->createDatabase();
		}
	}

}

?>
