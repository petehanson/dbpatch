<?php
/**        
 *  class database (for postgres)
 * 
 * @package pgsql_database.php
 * @ignore
 *
 */
/**
 * requiring driverinterface.php
 *
 */
require_once("driverinterface.php");
/**
 * class database (for postgres dbms)
 * 
 * @package pgsql_database.php
 * @ignore
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

    public function __construct($host,$db,$username,$password,printer $printer)
    {
	$this->host = $host;
	$this->dbName = $db;
	$this->username = $username;
	$this->password = $password;

	$this->printer = $printer;

	$this->hasError = false;
	$this->inTransaction = false;

	$connectionString = "host={$this->host} dbname={$this->dbName} user={$this->username} password={$this->password}";

	$this->connection = pg_connect($connectionString);

	if ($this->connection === false) throw new exception("Failed to connect to the database");
	//if (mysql_select_db($this->dbName) === false) throw new exception("Failed to connect to the database {$this->dbName}");
    }

    public function close()
    {
	pg_close($this->connection);
    }

    public function doesTransactions()
    {
	return true;
    }

    public function startTransaction()
    {

	if ($this->inTransaction == false)
	{
		$this->hasError = false;
		$this->inTransaction = true;
		$sql = "BEGIN"; 
		$this->execute($sql);
	}
    }

    public function completeTransaction()
    {
	if ($this->inTransaction)
	{
	    if ($this->hasError === true)
	    {
		$sql = "ROLLBACK";
		$return = false;
	    }
	    else
	    {
		$sql = "COMMIT";
		$return = true;
	    }
	    $this->execute($sql);
	    return $return;
	}
	else
	{
	    return true;
	}
    }

    public function failTransaction()
    {
	if ($this->inTransaction) $this->hasError = true;
    }

    public function checkForDBVersion()
    {
	$sql = "SELECT c.relname as \"Name\" FROM pg_catalog.pg_class c LEFT JOIN pg_catalog.pg_user u ON u.usesysid = c.relowner LEFT JOIN pg_catalog.pg_namespace n ON n.oid = c.relnamespace WHERE c.relkind IN ('r','v','S','') AND n.nspname NOT IN ('pg_catalog', 'pg_toast') AND pg_catalog.pg_table_is_visible(c.oid) and c.relname like 'dbversion'";

	$result = $this->rowExists($sql);
	if ($result == false)
	{
	    $sql = "CREATE TABLE dbversion ( db_version_id varchar(255) NOT NULL, description text, version_date date, initiating_person character varying(255))";
	    $this->execute($sql);
	}
    }

    protected function rowExists($sql)
    {
	$result = $this->execute($sql);
	$numRows = pg_num_rows($result);

	if ($numRows > 0)
	{
	    return true;
	}
	else
	{
	    return false;
	}
    }

    public function checkVersion($versionID)
    {
	$versionSQL = "select * from dbversion where db_version_id = '%s'";
	$sql = sprintf($versionSQL,pg_escape_string($versionID));	

	if ($this->rowExists($sql))
	{
	    return true;
	}
	else
	{
	    return false;
	}
    }

    public function insertVersion($id,$date)
    {
    	$versionInsertSQL = "INSERT INTO dbversion VALUES ('%s','%s')";
    	$sql = sprintf($versionInsertSQL,pg_escape_string($id),pg_escape_string($date));

    	return $this->execute($sql);
    }

    public function execute($sql)
    {
	$this->printer->write($sql,2);
	$result = pg_query($sql);
	if ($result === false) $this->hasError = true;
	return $result;
    }

    /**
     * function executeBase: Execute the base schema.
     * TODO: For now, this is just an alias for execute(), since the rest of the driver assumes the
     *       database already exists.
     * @return boolean TRUE on success, FALSE on failure
     */
    public function executeBase($sql) {
	return $this->execute($sql);
    }

    public function getError()
    {
	return pg_last_error();
    }
    
}

?>
