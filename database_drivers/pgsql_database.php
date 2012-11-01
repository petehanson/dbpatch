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
require_once(dirname(__FILE__) . "/driverinterface.php");
require_once(dirname(__FILE__) . "/../lib/sql.php");

/**
 * class database (for postgres dbms)
 *
 * @package pgsql_database.php
 * @ignore
 *
 */
class pg_database implements driverinterface {

    protected $host;
    protected $dbName;
    protected $username;
    protected $password;
    protected $printer;
    protected $connection;
    protected $hasError;
    protected $dbExists;
    protected $inTransaction;
    protected $baseFile;

    public function __construct($host, $db, $username, $password, printer $printer, $basefile = null, $suppressDbCreation = false) {
        $this->host = $host;
        $this->dbName = $db;
        $this->username = $username;
        $this->password = $password;

        $this->printer = $printer;
        $this->baseFile = $basefile;

        $this->hasError = false;
        $this->inTransaction = false;

        $this->connect_and_initialize($suppressDbCreation);

        if ($this->connection === false)
            throw new exception("Failed to connect to the database");
    }

    private function build_connection_string($includeDbName = false) {
        $cs = "host={$this->host} ";

        if ($includeDbName)
            $cs .= "dbname={$this->dbName} ";
        else
            $cs .= "dbname=postgres ";

        $cs .= "user={$this->username} ";
        $cs .= "password={$this->password} ";

        return $cs;
    }

    /**
     * Connect and initialize db
     */
    private function connect_and_initialize($suppressDbCreation = false) {
        $connectionString = $this->build_connection_string();
        $fullConnectionString = $this->build_connection_string(true);

        $this->connection = pg_connect($connectionString);

        $this->dbExists = $this->db_exists($this->dbName);

        if ($this->dbExists === false) {
            if (!$suppressDbCreation) {
                if ($this->baseFile === null) {
                    $this->executeBase();
                } else {
                    $this->executeBase(file_get_contents($this->baseFile));
                }
            }
        } else { // re-connect to correct db
            $this->connection = pg_connect($fullConnectionString);
        }
    }

    private function db_exists($dbName) {
        $result = pg_query("select count(datname) from pg_catalog.pg_database where datname like '" . $dbName . "'");

        while ($line = pg_fetch_array($result)) {
            foreach ($line as $colvalue) {
                if ($colvalue == 1)
                    return true;
            }
        }

        pg_free_result($result);

        return false;
    }

    /**
     * function executeBase: Execute the base schema, first creating the database if it isn't created
     * by the SQL string.
     * @return boolean TRUE on success, FALSE on failure
     */
    protected function executeBase($sql = null) {
        if ($sql === null) {
            $this->createDatabase();
            return true;
        } else {
            $sqlobj = new SQL($sql);
            if (!$sqlobj->createsDatabase()) {
                $this->createDatabase();
            }
            return $this->execute($sql);
        }
    }

    /**
     * function createDatabase: Attempts to creates the database if it does not exist
     */
    protected function createDatabase() {
        $answer = $this->printer->ask("Database {$this->dbName} does not exist. Want to create the database right now? (y/n)");
        if ($answer == 'y') {
            $configuredUser = $this->username;
            $hasPrivilege = $this->userHasDbCreatePrivilege();

            // If current user doesn't have the privilege required ask for a root user
            if (isset($hasPrivilege) && !$hasPrivilege) {

                $tryNumber = 0;

                // try getting the correct user with "create" privilege up to 3 times
                while ($tryNumber <= 3 && !$hasPrivilege) {

                    $this->printer->write("\nUser $this->username does not have 'CREATE' privilege. Please enter a MySQL root user credentials:\n");
                    $username = $this->printer->askWithRetriesIfEmpty("Username: ", 2);
                    $password = $this->printer->askWithRetriesIfEmpty("Password: ", 2);

                    $this->change_user($username, $password);

                    $hasPrivilege = $this->userHasDbCreatePrivilege();

                    $tryNumber++;
                }
            }

            if ($hasPrivilege) {
                if (pg_query("CREATE DATABASE \"{$this->dbName}\"")) {
                    $this->printer->write("Database created");
                    $this->is_new = true;
                    $this->dbExists = true;

                    // escape underscores if the db name contains them
                    // this is needed for grant to work correctly
                    $grantPermissionsToConfigUserQuery = "ALTER USER " . $configuredUser . " WITH SUPERUSER";

                    if (pg_query($grantPermissionsToConfigUserQuery)) {
                        $this->printer->write("All privileges granted to user $configuredUser");
                    } else {
                        throw new exception("Error granting all permissions to $configuredUser user");
                    }


                    $this->connection = pg_connect($this->build_connection_string(true));

                    if ($this->connection === false) {
                        $this->dbExists = false;
                        throw new exception("Failed to connect to the database {$this->dbName}");
                    }
                } else {
                    throw new exception("Error creating database: " . $this->connection->error);
                }
            } else {
                throw new exception("Error creating DB. The user configured does not have privilege to create a DB");
            }
        } elseif ($answer == 'n') {
            $this->printer->write("The database was not created. Process aborted.");
            die;
        } else {
            $this->createDatabase();
        }
    }

    /**
     * Check if user has a privilege
     * @param String $privilegeName
     * @return true or false
     */
    public function userHasPrivilege($privilegeName, $tablename = "dbversion") {
        $sql = "SELECT has_table_privilege('" . $this->username .
                "', '" . $tablename . "', '" . $privilegeName . "')";

        $result = pg_query($sql);

        while ($line = pg_fetch_array($result)) {
            if ($line["has_table_privilege"] == "f")
                return false;
        }

        return true;
    }

    public function userHasDbCreatePrivilege() {
        try {

            $hasPgAuthPrivilege = $this->userHasPrivilege("select", "pg_authid");

            if (!$hasPgAuthPrivilege)
                return false;

            $sql = "select rolcreatedb
          from pg_authid
          where rolname = '" . $this->username . "' ";

            $result = pg_query($sql);

            if (!$result)
                return false;

            while ($line = pg_fetch_array($result)) {
                if ($line["rolcreatedb"] == "f")
                    return false;
            }

            return true;
        } catch (Exception $exc) {
            return false;
        }
    }

    /**
     * Change user of current opened connection
     * @return true or false
     */
    public function change_user($username, $password) {
        $this->username = $username;
        $this->password = $password;

        $this->connect_and_initialize(true);
    }

    public function close() {
        pg_close($this->connection);
        $this->connection = null;
    }

    public function doesTransactions() {
        return true;
    }

    public function startTransaction() {

        if ($this->inTransaction == false) {
            $this->hasError = false;
            $this->inTransaction = true;
            $sql = "BEGIN";
            $this->execute($sql);
        }
    }

    public function completeTransaction() {
        if ($this->inTransaction) {
            if ($this->hasError === true) {
                $sql = "ROLLBACK";
                $return = false;
            } else {
                $sql = "COMMIT";
                $return = true;
            }
            $this->execute($sql);
            return $return;
        } else {
            return true;
        }
    }

    public function failTransaction() {
        if ($this->inTransaction)
            $this->hasError = true;
    }

    public function checkForDBVersion() {
        try {
            $sql = "SELECT c.relname as \"Name\" FROM pg_catalog.pg_class c LEFT JOIN pg_catalog.pg_user u ON u.usesysid = c.relowner LEFT JOIN pg_catalog.pg_namespace n ON n.oid = c.relnamespace WHERE c.relkind IN ('r','v','S','') AND n.nspname NOT IN ('pg_catalog', 'pg_toast') AND pg_catalog.pg_table_is_visible(c.oid) and c.relname like 'dbversion'";

            $result = $this->rowExists($sql);
            if ($result == false) {
                $sql = "CREATE TABLE dbversion ( applied_patch varchar(255) NOT NULL, date_patch_applied date)";
                $this->execute($sql);
            }

            return true;
        } catch (Exception $exc) {
            $this->printer->write("Error: " . $exc->getMessage());
            return false;
        }
    }

    protected function rowExists($sql) {
        $result = $this->execute($sql);

        if ($result === FALSE)
            return false;

        $numRows = pg_num_rows($result);

        pg_free_result($result);

        if ($numRows > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function checkVersion($versionID) {
        $versionSQL = "select * from dbversion where applied_patch = '%s'";
        $sql = sprintf($versionSQL, pg_escape_string($versionID));

        if ($this->rowExists($sql)) {
            return true;
        } else {
            return false;
        }
    }

    public function insertVersion($id, $date) {
        $versionInsertSQL = "INSERT INTO dbversion VALUES ('%s','%s')";
        $sql = sprintf($versionInsertSQL, pg_escape_string($id), pg_escape_string($date));

        return $this->execute($sql);
    }

    public function insertTrackingItem($tracking_item) {
        $this->insertVersion(
                $tracking_item["item"]["applied_patch"], $tracking_item["item"]["date_patch_applied"]);
    }

    public function execute($sql) {
        $this->printer->write($sql, 2);
        $result = pg_query($sql);

        if ($result === false) {
            $this->hasError = true;
            $this->printer->write("Error: " . pg_last_error());
        }

        return $result;
    }

    public function getError() {
        return pg_last_error();
    }

    /**
     *  function execute: generic sql processor function, with wto if
     *  bad.
     *  @param string $file The file to execute.
     *  @param boolean $storeResults Whether or not to return the result of the SQL query
     *  @return boolean FALSE on error. If $storeResults, return the result set from $this->storeResults()
     *       upon success. Otherwise, return TRUE upon success.
     *  @todo Suggest write to printer altered to feedback back to
     *  calling interface, separating interface from logic and execution.
     *
     */
    public function executeFile($file, $storeResults = false) {

        $fileData = file_get_contents($file);

        $this->printer->write("executing statement:" . $fileData, 2);

        if ($fileData != "")
            $return_var = $this->execute($fileData);
        else
            $return_var = true;

        if ($return_var === FALSE) {
            $this->hasError = true;
        }

        return $return_var;
    }

    public function get_applied_patch_names() {
        $return_array = array();
        $sql = "select * from dbversion";

        $results = pg_query($sql);

        while ($line = pg_fetch_array($results)) {
            $return_array[] = $line['applied_patch'];
        }

        pg_free_result($results);

        return $return_array;
    }

    /**
     * Get applied patch items from DB
     * @return associative array of patch items
     */
    public function get_applied_patch_items() {
        $return_array = array();
        $sql = "select * from dbversion";

        $results = pg_query($sql);

        while ($line = pg_fetch_array($results)) {

            $versioningItem = array("item" =>
                array("applied_patch" => $line['applied_patch'],
                    "date_patch_applied" => $line['date_patch_applied']));

            $return_array[] = $versioningItem;
        }

        pg_free_result($results);

        return $return_array;
    }

    public function has_error() {
        return $this->hasError;
    }

    public function ping_db() {
        return $this->connection != FALSE;
    }

}

?>
