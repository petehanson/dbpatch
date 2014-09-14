<?php

// need to run with phpunit --bootstrap boostrap.php path/to/test.php

namespace uarsoftware\dbpatch\App;

class PatchEngineTest extends \PHPUnit_Framework_TestCase
{

    protected $config;
    protected $app;

    public function setUp() {
        $this->config = new Config("test","mysql","localhost","test","root","root");
    }

    public function tearDown() {
        //$this->config = null;
    }

    public function testInitialization() {
    }

    /*
    public function testDSN() {

        $this->config->setPort(3306);

        $this->assertEquals("mysql:host=localhost;port=3306;dbname=test_db",$this->config->getDSN());

        $this->config->setPort("");

        $this->assertEquals("mysql:host=localhost;dbname=test_db",$this->config->getDSN());
    }

    public function testRootLevelCommands() {

        $this->config->resetRootLevelCommands();

        $this->assertCount(0,$this->config->getRootLevelCommands());

        $this->config->addRootLevelCommands("FOO");
        $this->assertCount(1,$this->config->getRootLevelCommands());

        $this->config->addRootLevelCommands(array("BAR","BAZ"));
        $this->assertCount(3,$this->config->getRootLevelCommands());

    }
    */
}
