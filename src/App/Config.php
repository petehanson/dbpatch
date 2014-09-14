<?php

namespace uarsoftware\dbpatch\App;

class Config {

    protected $id;

    protected $driver;
    protected $host;
    protected $port;
    protected $databaseName;
    protected $user;
    protected $password;
    protected $trackPatchesInFile;


    protected $basePath;
    protected $initFile;
    protected $initPartialPath;
    protected $initPath;
    protected $schemaPartialPath;
    protected $schemaPath;
    protected $dataPartialPath;
    protected $dataPath;
    protected $scriptPartialPath;
    protected $scriptPath;



    protected $standardizedTimezone;
    protected $rootLevelCommands;


    public function __construct($id,$driver,$host,$databaseName,$user,$pass,$port = null) {

        $this->id = $id;

        if ($driver !== null) $this->setDriver($driver);
        if ($host !== null) $this->setHost($host);
        if ($databaseName !== null) $this->setDatabaseName($databaseName);
        if ($user !== null) $this->setUser($user);
        if ($pass !== null) $this->setPassword($pass);
        if ($port !== null) $this->setPort($port);

        $this->disableTrackingPatchesInFile();


        $this->initFile = "initialize.sql";
        $this->initPartialPath = "sql" . DIRECTORY_SEPARATOR . "init";
        $this->schemaPartialPath = "sql" . DIRECTORY_SEPARATOR . "schema";
        $this->dataPartialPath = "sql" . DIRECTORY_SEPARATOR . "data";
        $this->scriptPartialPath = "sql" . DIRECTORY_SEPARATOR . "script";

        $this->setBasePath(dirname(__FILE__));


        $this->standardizedTimezone = "UTC";
        $this->rootLevelCommands = array( "EVENT", "TRIGGER", "DROP DATABASE", "SHUTDOWN", "FILE", "GRANT", "CREATE USER", "REVOKE" );
    }



    public function getID() {
        return $this->id;
    }

    public function setTrackingPatchesInFile($input) {
        $this->trackPatchesInFile = ($input) ? true : false;
    }

    public function enableTrackingPatchesInFile() {
        $this->trackPatchesInFile = true;
    }

    public function disableTrackingPatchesInFile() {
        $this->trackPatchesInFile = false;
    }

    public function getRootLevelCommands() {
        return $this->rootLevelCommands;
    }

    public function addRootLevelCommands($input) {
        if (is_array($input)) {
            $this->rootLevelCommands = array_merge($this->rootLevelCommands,$input);
        } else {
            array_push($this->rootLevelCommands,$input);
        }
    }

    public function resetRootLevelCommands() {
        $this->rootLevelCommands = array();
    }

    public function getDSN() {
        $parts = array();

        if ($this->getHost()) {
            $parts[] = "host=" . $this->getHost();
        }

        if ($this->getPort()) {
            $parts[] = "port=" . $this->getPort();
        }

        if ($this->getDatabaseName()) {
            $parts[] = "dbname=" . $this->getDatabaseName();
        }

        return $this->getDriver() . ":" . implode(";",$parts);
    }

    protected function configurePaths() {
        $this->initPath = $this->basePath . DIRECTORY_SEPARATOR . $this->initPartialPath;
        $this->schemaPath = $this->basePath . DIRECTORY_SEPARATOR . $this->schemaPartialPath;
        $this->dataPath = $this->basePath . DIRECTORY_SEPARATOR . $this->dataPartialPath;
        $this->scriptPath = $this->basePath . DIRECTORY_SEPARATOR . $this->scriptPartialPath;
    }

    /**
     * @param mixed $databaseName
     */
    protected function setDatabaseName($databaseName)
    {
        $this->databaseName = $databaseName;
    }

    /**
     * @return mixed
     */
    public function getDatabaseName()
    {
        return $this->databaseName;
    }

    /**
     * @param mixed $driver
     */
    protected function setDriver($driver)
    {
        $this->driver = $driver;
    }

    /**
     * @return mixed
     */
    public function getDriver()
    {
        return $this->driver;
    }

    /**
     * @param mixed $host
     */
    protected function setHost($host)
    {
        $this->host = $host;
    }

    /**
     * @return mixed
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @param mixed $password
     */
    protected function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * @return mixed
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param mixed $port
     */
    public function setPort($port)
    {
        $this->port = $port;
    }

    /**
     * @return mixed
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * @param mixed $user
     */
    protected function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param string $basePath
     */
    public function setBasePath($basePath)
    {
        $this->basePath = $basePath;

        $this->configurePaths();
    }

    /**
     * @return string
     */
    public function getBasePath()
    {
        return $this->basePath;
    }

    /**
     * @return mixed
     */
    public function getDataPath()
    {
        return $this->dataPath;
    }

    /**
     * @return mixed
     */
    public function getInitPath()
    {
        return $this->initPath;
    }

    /**
     * @return mixed
     */
    public function getSchemaPath()
    {
        return $this->schemaPath;
    }

    /**
     * @return mixed
     */
    public function getScriptPath()
    {
        return $this->scriptPath;
    }

    /**
     * @param string $dataPartialPath
     */
    public function setDataPartialPath($dataPartialPath)
    {
        $this->dataPartialPath = $dataPartialPath;

        $this->configurePaths();
    }

    /**
     * @return string
     */
    public function getDataPartialPath()
    {
        return $this->dataPartialPath;
    }

    /**
     * @param string $initFile
     */
    public function setInitFile($initFile)
    {
        $this->initFile = $initFile;
    }

    /**
     * @return string
     */
    public function getInitFile()
    {
        return $this->initFile;
    }

    /**
     * @param string $initPartialPath
     */
    public function setInitPartialPath($initPartialPath)
    {
        $this->initPartialPath = $initPartialPath;
        $this->configurePaths();
    }

    /**
     * @return string
     */
    public function getInitPartialPath()
    {
        return $this->initPartialPath;
    }

    /**
     * @param string $schemaPartialPath
     */
    public function setSchemaPartialPath($schemaPartialPath)
    {
        $this->schemaPartialPath = $schemaPartialPath;
        $this->configurePaths();
    }

    /**
     * @return string
     */
    public function getSchemaPartialPath()
    {
        return $this->schemaPartialPath;
    }

    /**
     * @param string $scriptPartialPath
     */
    public function setScriptPartialPath($scriptPartialPath)
    {
        $this->scriptPartialPath = $scriptPartialPath;
        $this->configurePaths();
    }

    /**
     * @return string
     */
    public function getScriptPartialPath()
    {
        return $this->scriptPartialPath;
    }

    /**
     * @param string $standardizedTimezone
     */
    public function setStandardizedTimezone($standardizedTimezone)
    {
        $this->standardizedTimezone = $standardizedTimezone;
    }

    /**
     * @return string
     */
    public function getStandardizedTimezone()
    {
        return $this->standardizedTimezone;
    }




}