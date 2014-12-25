<?php

namespace uarsoftware\dbpatch\App;

class Database extends \PDO implements DatabaseInterface{

    protected $appliedPatchesTable;

    public function __construct(Config $config) {
        parent::__construct($config->getDSN(),$config->getUser(),$config->getPassword());

        // get this from the config
        $this->appliedPatchesTable = $config->getAppliedPatchesTableName();

        $this->checkAndCreateVersionTable();
    }

    protected function checkAndCreateVersionTable() {
        $sql = "DESC " . $this->appliedPatchesTable;
        $statement = $this->query($sql);


        if ($statement === false) {
            // need to create the table
            $this->createDbVersionTable();
        } else {
            // check that we have valid fields

            if ($statement->rowCount() != 2) {
                throw new \exception($this->appliedPatchesTable . " table does not have the correct number of columns");
            }

            $columns = $statement->fetchAll();

            if ($columns[0]['Field'] != "applied_patch") {
                throw new \exception("The " . $this->appliedPatchesTable . ".applied_patch field does not exist in " . $this->appliedPatchesTable);
            }

            if ($columns[1]['Field'] != "timestamp_patch_applied") {
                throw new \exception("The " . $this->appliedPatchesTable . ".date_patch_applied field does not exist in " . $this->appliedPatchesTable);
            }
        }
    }

    protected function createDbVersionTable() {
        $sql = 'CREATE TABLE ' . $this->appliedPatchesTable . ' (applied_patch varchar(255) NOT NULL, timestamp_patch_applied TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP)';
        $statement = $this->query($sql);

        if ($statement === false) {
            var_dump($this->errorInfo());
            throw new \exception("Failed to create the " . $this->appliedPatchesTable . " table");
        }
    }

    public function getAppliedPatches() {

        $patches = array();

        $statement = $this->prepare("SELECT applied_patch FROM " . $this->appliedPatchesTable, array(\PDO::ATTR_CURSOR => \PDO::CURSOR_SCROLL));
        $statement->execute();



        while ($row = $statement->fetch(\PDO::FETCH_NUM, \PDO::FETCH_ORI_NEXT)) {
            $patch = new Patch($row[0]);
            $patch->setAsAppliedPatch();

            array_push($patches,$patch);
        }
        $statement = null;

        return $patches;
    }

    public function executeQuery($sql) {

        $result = new \stdClass();

        $statement = $this->prepare($sql);
        $result->status = $statement->execute();
        $result->statement = $statement;

        if ($result->status == false) {
            $err = $statement->errorInfo();
            $result->errorCode = $err[1];
            $result->errorMessage = $err[2];
        }

        return $result;
    }

    public function recordPatch(PatchInterface $patch) {
        $statement = $this->prepare("INSERT INTO " . $this->appliedPatchesTable . " (applied_patch) VALUES (:patch)");
        $statement->bindValue(':patch',$patch->getBaseName(),\PDO::PARAM_STR);

        $result = $statement->execute();

        if ($result == false) {
            throw new exception("Error occurred when recording " . $patch->getBaseName());
        }

        return $result;
    }
}
