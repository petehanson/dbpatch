<?php

namespace uarsoftware\dbpatch\App;

class Database extends \PDO implements DatabaseInterface{

    protected $versionTable;

    public function __construct(Config $config) {
        parent::__construct($config->getDSN(),$config->getUser(),$config->getPassword());

        // get this from the config
        $this->versionTable = "dbversion";

        $this->checkAndCreateVersionTable();
    }

    protected function checkAndCreateVersionTable() {
        $sql = "DESC " . $this->versionTable;
        $statement = $this->query($sql);


        if ($statement === false) {
            // need to create the table
            $this->createDbVersionTable();
        } else {
            // check that we have valid fields

            if ($statement->rowCount() != 2) {
                throw new \exception($this->versionTable . " table does not have the correct number of columns");
            }

            $columns = $statement->fetchAll();

            if ($columns[0]['Field'] != "applied_patch") {
                throw new \exception("The " . $this->versionTable . ".applied_patch field does not exist in " . $this->versionTable);
            }

            if ($columns[1]['Field'] != "timestamp_patch_applied") {
                throw new \exception("The " . $this->versionTable . ".date_patch_applied field does not exist in " . $this->versionTable);
            }
        }
    }

    protected function createDbVersionTable() {
        $sql = 'CREATE TABLE ' . $this->versionTable . ' (applied_patch varchar(255) NOT NULL, timestamp_patch_applied TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP)';
        $statement = $this->query($sql);

        if ($statement === false) {
            var_dump($this->errorInfo());
            throw new \exception("Failed to create the " . $this->versionTable . " table");
        }
    }


    public function getAppliedPatches() {

        $patches = array();

        $statement = $this->prepare("SELECT applied_patch FROM " . $this->versionTable, array(\PDO::ATTR_CURSOR => \PDO::CURSOR_SCROLL));
        $statement->execute();



        while ($row = $statement->fetch(\PDO::FETCH_NUM, \PDO::FETCH_ORI_NEXT)) {
            $patch = new Patch($row[0]);
            array_push($patches,$patch);
        }
        $statement = null;

        return $patches;
    }
}