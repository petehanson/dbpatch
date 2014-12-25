<?php

// need to run with phpunit --bootstrap boostrap.php path/to/test.php

namespace uarsoftware\dbpatch\App;

class ConfigTest extends \PHPUnit_Framework_TestCase
{

    protected $config;

    public function setUp() {
        $this->config = new Config("test","mysql","localhost","test_db","root","root");
    }

    public function tearDown() {
        $this->config = null;
    }


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

    public function testPaths() {
        $this->config->setBasePath("/tmp");

        $this->assertEquals("/tmp/sql/init",$this->config->getInitPath());
        $this->assertEquals("/tmp/sql/schema",$this->config->getSchemaPath());
        $this->assertEquals("/tmp/sql/data",$this->config->getDataPath());
        $this->assertEquals("/tmp/sql/script",$this->config->getScriptPath());

        $this->config->setInitPartialPath("sql/initalt");
        $this->assertEquals("/tmp/sql/initalt",$this->config->getInitPath());

        $this->config->setSchemaPartialPath("sql/schemaalt");
        $this->assertEquals("/tmp/sql/schemaalt",$this->config->getSchemaPath());

        $this->config->setDataPartialPath("sql/dataalt");
        $this->assertEquals("/tmp/sql/dataalt",$this->config->getDataPath());

        $this->config->setScriptPartialPath("sql/scriptalt");
        $this->assertEquals("/tmp/sql/scriptalt",$this->config->getScriptPath());

    }
}
