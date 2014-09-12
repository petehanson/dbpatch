<?php

class Config {

    protected $id;
    protected $host;
    protected $port;
    protected $databasename;
    protected $user;
    protected $pass;

    protected $trackPatchesInFile;

    public function __construct($id,$host = null,$databasename = null,$user = null,$pass = null,$port = null) {

        $this->id = $id;

        if ($host !== null) $this->setHost($host);
        if ($databasename !== null) $this->setDatabaseName($databasename);
        if ($user !== null) $this->setUser($user);
        if ($pass !== null) $this->setPassword($pass);
        if ($port !== null) $this->setPort($port);

        $this->disableTrackingPatchesInFile();
    }

    /**
     * @param mixed $databasename
     */
    public function setDatabaseName($databasename)
    {
        $this->databasename = $databasename;
    }

    /**
     * @return mixed
     */
    public function getDatabaseName()
    {
        return $this->databasename;
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
     * @param mixed $pass
     */
    public function setPass($pass)
    {
        $this->pass = $pass;
    }

    /**
     * @return mixed
     */
    public function getPass()
    {
        return $this->pass;
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

    public function getID() {
        return $this->id;
    }

    public function enableTrackingPatchesInFile() {
        $this->trackPatchesInFile = true;
    }

    public function disableTrackingPatchesInFile() {
        $this->trackPatchesInFile = false;
    }


}