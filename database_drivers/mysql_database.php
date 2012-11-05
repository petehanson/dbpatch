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
require_once(dirname(__FILE__) . "/driverinterface.php");
require_once(dirname(__FILE__) . "/../lib/sql.php");

/**
 *   class database, for mysql processing.
 *
 * @package mysql_database
 *
 *
 */
class mysql_database implements driverinterface {

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
    protected $dbExists;
    protected $baseFile;
    protected $dbInformationSchema;

    /**
     *   __construct function, describes environment and sets up connection.
     *
     */
    public function __construct($host, $db, $username, $password, printer $printer, $basefile = null, $suppressDbCreation = false) {
        $this->host = $host;
        $this->dbName = $db;
        $this->dbInformationSchema = "information_schema";
        $this->username = $username;
        $this->password = $password;
        $this->baseFile = $basefile;

        $this->printer = $printer;

        $this->hasError = false;
        $this->inTransaction = false;
        $this->applyBase = false;

        $this->is_new = false;

        $this->connect_and_initialize($suppressDbCreation);
    }

    /**
     * Connect and initialize db
     */
    private function connect_and_initialize($suppressDbCreation = false) {
        $this->connection = new mysqli($this->host, $this->username, $this->password, null);

        if (mysqli_connect_error())
            throw new exception("Failed to connect to the database (" . mysqli_connect_errno() . ")");

        // Try to select the database, creating it and applying the base schema if it doesn't exist
        $this->dbExists = true;
        if ($this->connection->select_db($this->dbName) === false) {

            if (!$suppressDbCreation) {
                if ($this->baseFile === null) {
                    $this->executeBase();
                } else {
                    $this->executeBase(file_get_contents($this->baseFile));
                }
            }
        }
    }

    /**
     * Change user of database connection
     * @return true or false
     */
    public function change_user($username, $password) {
        $this->username = $username;
        $this->password = $password;

        $this->connect_and_initialize(true);
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
		varchar(255) NOT NULL, date_patch_applied date )";
            $this->execute($sql);
        }
    }

    /**
     * Check if user has a privilege
     * @param String $privilegeName
     * @return true or false
     */
    public function userHasPrivilege($privilegeName) {
        // Temporary select information_schema db
        $this->connection->select_db($this->dbInformationSchema);

        $sql = "SELECT * FROM `USER_PRIVILEGES` WHERE `GRANTEE` like '%" .
                $this->username . "%' and `PRIVILEGE_TYPE` = '$privilegeName'";

        $rowExists = $this->rowExists($sql);

        // re-select regular DB
        $this->connection->select_db($this->dbName);

        return $rowExists;
    }

    /**
     * function rowExists: generic test for existing rows in table.
     *
     */
    protected function rowExists($sql) {
        $result = $this->execute($sql, true);

        if (is_array($result)) {
            return false;
        }
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
     * function get_applied_patch_names:  returns a 2 dimensional array of of patch data back to the client code
     * Returns a list of applied patches.
     * @return array
     */
    public function get_applied_patch_names() {
        $return_array = array();
        $sql = "select * from dbversion";

        $results = $this->execute($sql, true);
        if (!empty($results)) {
            while ($row = $results->fetch_assoc()) {
                $return_array[] = $row['applied_patch'];
            }
        }

        return $return_array;
    }

    /**
     * Get applied patch items from DB
     * @return associative array of patch items
     */
    public function get_applied_patch_items() {
        $return_array = array();
        $sql = "select * from dbversion";

        $results = $this->execute($sql, true);
        if (!empty($results)) {
            while ($row = $results->fetch_assoc()) {

                $versioningItem = array("item" =>
                    array("applied_patch" => $row['applied_patch'],
                        "date_patch_applied" => $row['date_patch_applied']));

                $return_array[] = $versioningItem;
            }
        }

        return $return_array;
    }

    /**
     *  function insertVersion:  assuming all is good, insert the version
     *  info into the dbversion table.
     *
     */
    public function insertVersion($id, $date) {
        $versionInsertSQL = "INSERT INTO dbversion VALUES ('%s','%s')";
        $sql = sprintf($versionInsertSQL, $this->connection->escape_string($id), $this->connection->escape_string($date));

        return $this->execute($sql);
    }

    public function insertTrackingItem($tracking_item) {
        $this->insertVersion(
                $tracking_item["item"]["applied_patch"], $tracking_item["item"]["date_patch_applied"]);
    }

    /**
     * function clearResults: clear any MySQL result sets in the queue
     */
    protected function clearResults() {
        do {
            if ($r = $this->connection->use_result()) {
                $r->free();
            }
        } while ($this->connection->next_result());
    }

    /**
     * function storeResults: store any MySQL result sets. Make sure to call ->free() on
     *    each result object when you're done using it.
     * @return array of MySQLi_Result objects or a single MySQLi_Result object
     */
    protected function storeResults() {
        $results = array();
        do {
            if ($r = $this->connection->store_result()) {
                $results[] = $r;
            }
        } while ($this->connection->next_result());
        if (count($results) === 1) {
            return $results[0];
        }
        return $results;
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

        $dump_file = "mysql -h " . $this->host . " -u " . $this->username . " --password=\"" . $this->password . "\"  " . $this->dbName . "<" . $file;

        $this->printer->write("executing statement:" . $dump_file, 2);

        $output = array();
        $return_var = 0;

        exec($dump_file, $output, $return_var);

        if ($return_var == 1) {
            $this->hasError = true;
        }

        return $return_var;
    }

    public function execute($sql, $storeResults = false) {
        $this->printer->write("executing statement:", 2);
        $this->printer->write($sql, 2);

        // Execute the SQL statement(s)
        $this->connection->multi_query($sql);


        if ($this->connection->error) {
            $this->hasError = true;
            $this->printer->write('SQL error: ' . $this->connection->error, 1);
            $this->clearResults();
            return false;
        }
        if ($storeResults) {
            return $this->storeResults();
        }
        $this->clearResults();
        return true;
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
     * function isNewDB: Tells if the base schema should be applied. Like is ten case when the datbase is created
     *
     */
    public function isNewDB() {
        return ($this->is_new) ? true : false;
    }

    /**
     * function dbExists: Tells whether or not the database actually exists on the server.
     */
    public function dbExists() {
        return ($this->dbExists) ? true : false;
    }

    /**
     * function createDatabase: Attempts to creates the database if it does not exist
     */
    protected function createDatabase() {
        $answer = $this->printer->ask("Database {$this->dbName} does not exist. Want to create the database right now? (y/n)");
        if ($answer == 'y') {
            $configuredUser = $this->username;
            $configuredPassword = $this->password;
            $hasPrivilege = $this->userHasPrivilege("CREATE");

            // If current user doesn't have the privilege required ask for a root user
            if (isset($hasPrivilege) && !$hasPrivilege) {

                $tryNumber = 0;

                // try getting the correct user with "create" privilege up to 3 times
                while ($tryNumber <= 3 && !$hasPrivilege) {

                    $this->printer->write("\nUser $this->username does not have 'CREATE' privilege. Please enter a MySQL root user credentials:\n");
                    $username = $this->printer->askWithRetriesIfEmpty("Username: ", 2);
                    $password = $this->printer->askWithRetriesIfEmpty("Password: ", 2);

                    $this->change_user($username, $password);

                    $hasPrivilege = $this->userHasPrivilege("CREATE");

                    $tryNumber++;
                }
            }

            if ($hasPrivilege) {
                if ($this->connection->query("CREATE DATABASE `{$this->dbName}`")) {
                    $this->printer->write("Database created");
                    $this->is_new = true;
                    $this->dbExists = true;

                    // escape underscores if the db name contains them
                    // this is needed for grant to work correctly
                    $grantPermissionsToConfigUserQuery = "GRANT ALL PRIVILEGES ON `" .
                            str_replace('_', '\_', $this->dbName)  . "`.* to '" .
                                    $configuredUser . "'@'localhost' IDENTIFIED BY '" . $configuredPassword . "';";

                    if ($this->connection->query($grantPermissionsToConfigUserQuery)) {

                        $this->connection->query("FLUSH PRIVILEGES;");

                        $this->printer->write("All privileges granted to user $configuredUser");
                    }
                    else
                    {
                         throw new exception("Error granting all permissions to $configuredUser user");
                    }

                    if ($this->connection->select_db($this->dbName) === false) {
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

    // below are methods I think we can remove from the driver file
    // these four shouldn't be here, since any thing that requiring connection parameters, should be handled in the driver class
    public function getHost() {
        return $this->host;
    }

    public function getUser() {
        return $this->username;
    }

    public function getPassword() {
        return $this->password;
    }

    public function getDBName() {
        return $this->dbName;
    }

    public function getConnection()
    {
        return $this->connection;
    }

    public function clearError()
    {
        $this->hasError = false;
    }

    public function ping_db() {
        return $this->connection->ping();
    }

}

?>
