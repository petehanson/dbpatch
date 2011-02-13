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
class database implements driverinterface


{
    protected $host;
    protected $dbName;
    protected $username;
    protected $password;

    protected $printer;

    protected $connection;

    protected $hasError;
    protected $inTransaction;
	protected $ddl_rollback;
	
/**        
 *   __construct function, describes environment and sets up connection.
 *
 */

	    public function __construct($host,$db,$username,
			$password,printer $printer)

	
    {
	$this->host = $host;
	$this->dbName = $db;
	$this->username = $username;
	$this->password = $password;

	$this->printer = $printer;

	$this->hasError = false;
	$this->inTransaction = false;

	$this->connection = 
		mysql_connect($this->host,$this->username,$this->password);  

	if ($this->connection === false) 
		throw new exception("Failed to connect to the database");
	if (mysql_select_db($this->dbName) === false) 
	throw new 
		exception("Failed to connect to the database {$this->dbName}");
    if ($this->autocommitOff() === false) 
		throw new exception("Unable to turn off autocommit in MySQL");
	}



/**        
 *  function close: closes db connection, no parms, no return.
 *
 */

    public function close()	
    {
	mysql_close($this->connection);
    }



/**        
 *  function doesTransactions:  says, yes, MySQL is transactional.  NOTE
 *  that it is only transactional if you inform that the table is so,
 *  and in order process correctly autocommit must be OFF.
 *
 */

    public function doesTransactions()
    {
	return true;
    }

/**        
 *  function startTransaction:  SQL to do BEGIN transaction
 *
 */

    public function startTransaction()
    {
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

    public function completeTransaction()
    {
	if ($this->inTransaction)
	{
	    if ($this->hasError === true)
	    {
		$sql = "ROLLBACK";
		$this->execute($sql);
		if (count($this->ddl_rollback) > 0) {
			$this->rollbackDDL();
		}
		$return = false;
	    }
	    else
	    {
		$sql = "COMMIT";
		$this->execute($sql);
		$return = true;
	    }
	    
		
	    return $return;
	}
	else
	{
	    return true;
	}
    }


/**        
 *  function failTransaction:  just say it's bad for downstream.
 *
 */

    public function failTransaction()
    {
	if ($this->inTransaction) $this->hasError = true;
    }

/**        
 *  function checkForDBVersion:  make sure the dbversion table is there
 *  and create if not.
 *
 */
    public function checkForDBVersion()
    {
	$sql = "show tables like 'dbversion'";
	$result = $this->rowExists($sql);
	if ($result == false)
	{
	    $sql = "CREATE TABLE dbversion ( db_version_id 
		varchar(255) NOT NULL, description text, version_date date, 
		initiating_person character varying(255))
		ENGINE=InnoDB";
	    $this->execute($sql);
	}
    }
	
/**        
 *   function:  autocommitOff - must turn off autocommit to avoid
 *   having all sql committed after DDL runs.
 * 
 *   Turning off autocommit avoids having to insert extra logic for
 *   multiple transactions within this process.  DDL causes an explicit
 *   commit, which ends the original transaction, then we would 
 *   have to start new transactions.  This allows us to do DDL, then 
 *   perform the user's SQL requests, then if a dryrun, rollback and 
 *   do the DDL undo input supplied.  Transaction is defined by 
 *   start of DDL.
 *
 */
	public function autocommitOff()
    {
	$sql = "SET autocommit = 0";
	$result = $this->execute($sql);
	if ($result === false)
	{
	   return false;
	}
	else
	{
		return true;
	}
    }

/**        
 * function rowExists: generic test for existing rows in table.
 *
 */
    protected function rowExists($sql)
    {
	$result = $this->execute($sql);
	if ($result) 
		{
		$numRows = mysql_num_rows($result);
		
		if ($numRows > 0)
			{
			return true;
			}
		else
			{
			return false;
			}
		}
		else {
			return false;
		}
	}

/**        
 *  function checkVersion:  see if this version is already in dbversion 
 *  table and if so, that is an indication that it already ran, stop 
 *  processing.
 *
 */
    public function checkVersion($versionID)
    {
	$versionSQL = "select * from dbversion where db_version_id = '%s'";
	
	$sql = sprintf($versionSQL,mysql_escape_string($versionID));	

	if ($this->rowExists($sql))
	{
	    return true;
	}
	else
	{
	    return false;
	}
    }

/**        
 *  function insertVersion:  assuming all is good, insert the version 
 *  info into the dbversion table.
 *
 */
    public function insertVersion($id,$description,$date,$person)
    {
	$date = date("Y-m-d",strtotime($date));
	$versionInsertSQL = "INSERT INTO dbversion VALUES ('%s','%s','%s','%s')";
	$sql = sprintf($versionInsertSQL,mysql_escape_string($id),mysql_escape_string($description),mysql_escape_string($date),mysql_escape_string($person));

	return $this->execute($sql);
    }

/**        
 *  function execute: generic sql processor function, with wto if
 *  bad.
 *  @param string $sql The sql statement to run.
 *  @return boolean Yea or nay on execution status.
 *  @todo Suggest write to printer altered to feedback back to 
 *  calling interface, separating interface from logic and execution.
 *
 */
    public function execute($sql)
    {
	$this->printer->write("executing statement: " . $sql,2);
	$result = mysql_query($sql);
	if ($result === false) {
		$this->hasError = true;
		$this->printer->write(mysql_error() . " is the result of the sql call", 1);
	}
	else {
	return $result;
    }
	
	}

/**        
 *  function getError:  generic MySQL error.
 *  @return string The error message itself.
 *
 */
    public function getError()
    {
	return mysql_error();
    }

/** 
 *   function addRollBack: insert new ddl for dryrun or rollback
 *   processing into the hold array for ddl undo statements.
 *   @param string $ddl_rollback sql statement to execute if the 
 *   process is to fail.
 * 
 * 
 */
		
    public function addRollBack($ddl_rollback)
    {
		$this->ddl_rollback[] = (string)$ddl_rollback;
    }

/**        
 * function rollBackDDL:  Do the rollback of DDL if needed.
 *
 */
		
	public function rollBackDDL()
    {
		foreach ($this->ddl_rollback as $sql_ddl)
		{
			$this->execute($sql_ddl);
		}
    }
    
}

?>
