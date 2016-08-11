<?php

// need to run with phpunit --bootstrap boostrap.php path/to/test.php

namespace uarsoftware\dbpatch\App;

use uarsoftware\dbpatch\App\Config;
use uarsoftware\dbpatch\App\ConfigInterface;
use uarsoftware\dbpatch\App\Database;
use uarsoftware\dbpatch\App\DatabaseInterface;

class DatabaseTest extends \PHPUnit_Framework_TestCase
{
    protected $db;
    protected $config;
    protected $dbname;

    public function setUp() {

        $this->dbname = "test" . \BootstrapUtil::generateRandomString();

        $this->runCliCommand("create database " . $this->dbname,false);

        $this->config = new Config('testid','mysql','localhost',$this->dbname,'root','root');
        $this->db = new Database($this->config);

        $this->runCliCommand("insert into " . $this->config->getAppliedPatchesTableName() . " (applied_patch) values ('1test.sql')");
        $this->runCliCommand("insert into " . $this->config->getAppliedPatchesTableName() . " (applied_patch) values ('2test.sql')");
    }

    public function tearDown() {


        $this->runCliCommand("drop database " . $this->dbname,false);
    }

    // may want to work this into a flexible class for use in the bootstrapper
    protected function runCliCommand($sql,$includeDbName = true) {
        $binary = \CLI::$mysqlBinary;
        $user = \CLI::$mysqlUser;
        $pass = \CLI::$mysqlPass;

        exec("{$binary} -V",$output,$returnVar);
        if ($returnVar == 127) {
            throw new exception("Command mysql couldn't be found in the path, please add it.");
        }


        $command = $binary . ' -u ' . $user . ' -p' . $pass . ' -e "' . $sql . '"';
        if ($includeDbName == true) {
            $command .= " " . $this->dbname;
        }

        exec($command,$output,$returnVar);

        if ($returnVar != 0) {
            throw new exception("command {$command} produced an error");
        }
    }

    public function testInstantiation() {
        $this->assertInstanceOf('uarsoftware\dbpatch\App\Database',$this->db);

        $rows = $this->db->query("select * from " . $this->config->getAppliedPatchesTableName());
        $this->assertInstanceOf('\PDOStatement',$rows);
    }

    public function testAppliedPatches() {
        $patches = $this->db->getAppliedPatches();
        $this->assertCount(2,$patches);
    }

    public function testExecuteQuery() {
        $result = $this->db->executeQuery("select * from " . $this->config->getAppliedPatchesTableName());
        $this->assertTrue($result->status);

        $data = $result->statement->fetchAll();
        $this->assertCount(2,$data);

        $result = $this->db->executeQuery("create table test (id int)");
        $this->assertTrue($result->status);

        $result = $this->db->executeQuery("insert into test values (1)");
        $this->assertTrue($result->status);

        $result = $this->db->executeQuery("select * from test");
        $this->assertTrue($result->status);

        $data = $result->statement->fetch();
        $this->assertEquals(1,$data['id']);
    }

    public function testRecordPatch() {
        $patch = new Patch("test_patch");
        $this->assertTrue($this->db->recordPatch($patch));

        $appliedPatches = $this->db->getAppliedPatches();
        $this->assertCount(3,$appliedPatches);

    }
}
