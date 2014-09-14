<?php

namespace uarsoftware\dbpatch\App;

class Database extends \PDO implements \DatabaseInterface{

    public function __construct(Config $config) {
        parent::__construct($config->getDSN(),$config->getUser(),$config->getPassword());
    }
}