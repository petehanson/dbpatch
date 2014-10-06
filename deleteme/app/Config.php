<?php

class Config {

    protected $id;

    protected $driver;
    protected $host;
    protected $port;
    protected $databaseName;
    protected $user;
    protected $password;
    protected $trackPatchesInFile;

    protected $baseFile;
    protected $basePath;
    protected $schemaPath;
    protected $dataPath;
    protected $scriptPath;
    protected $standardizedTimezone;
    protected $rootLevelCommands;


    public function __construct($id,$driver = null, $host = null,$databaseName = null,$user = null,$pass = null,$port = null) {

        $this->id = $id;

        if ($driver !== null) $this->setDriver($driver);
        if ($host !== null) $this->setHost($host);
        if ($databaseName !== null) $this->setDatabaseName($databaseName);
        if ($user !== null) $this->setUser($user);
        if ($pass !== null) $this->setPassword($pass);
        if ($port !== null) $this->setPort($port);

        $this->disableTrackingPatchesInFile();


        $this->baseFile = "base.sql";
        $this->basePath = "sql" . DIRECTORY_SEPARATOR . "base";
        $this->schemaPath = "sql" . DIRECTORY_SEPARATOR . "schema";
        $this->dataPath = "sql" . DIRECTORY_SEPARATOR . "data";
        $this->scriptPath = "sql" . DIRECTORY_SEPARATOR . "scripts";

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
            array_merge($this->rootLevelCommands,$input);
        } else {
            array_push($this->rootLevelCommands,$input);
        }
    }

    public function resetRootLevelCommands() {
        $this->rootLevelCommands = array();
    }

    /**
     * @param mixed $databaseName
     */
    public function setDatabaseName($databaseName)
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
    public function setDriver($driver)
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
    public function setHost($host)
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
    public function setPassword($password)
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
    public function setUser($user)
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
     * @param string $baseFile
     */
    public function setBaseFile($baseFile)
    {
        $this->baseFile = $baseFile;
    }

    /**
     * @return string
     */
    public function getBaseFile()
    {
        return $this->baseFile;
    }

    /**
     * @param string $basePath
     */
    public function setBasePath($basePath)
    {
        $this->basePath = $basePath;
    }

    /**
     * @return string
     */
    public function getBasePath()
    {
        return $this->basePath;
    }

    /**
     * @param string $dataPath
     */
    public function setDataPath($dataPath)
    {
        $this->dataPath = $dataPath;
    }

    /**
     * @return string
     */
    public function getDataPath()
    {
        return $this->dataPath;
    }

    /**
     * @param string $schemaPath
     */
    public function setSchemaPath($schemaPath)
    {
        $this->schemaPath = $schemaPath;
    }

    /**
     * @return string
     */
    public function getSchemaPath()
    {
        return $this->schemaPath;
    }

    /**
     * @param string $scriptPath
     */
    public function setScriptPath($scriptPath)
    {
        $this->scriptPath = $scriptPath;
    }

    /**
     * @return string
     */
    public function getScriptPath()
    {
        return $this->scriptPath;
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