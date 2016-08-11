<?php

// need to run with phpunit --bootstrap boostrap.php path/to/test.php

namespace uarsoftware\dbpatch\App;

class PatchEngineTest extends \PHPUnit_Framework_TestCase
{

    protected $pm;
    protected $db;
    protected $config;
    protected $app;

    public function setUp() {

        \TestFiles::setUpFiles();

        $this->config = new Config("test","mysql","localhost","test","root","root");
        $this->config->setBasePath("/tmp");

        $this->db = new \MockDatabase($this->config);
        $this->db->setAppliedPatches(array("3test.sql"));
        $this->pm = new PatchManager($this->config,$this->db);


        $output = new \MockOutput();

        $this->app = new PatchEngine($this->config,$this->db,$output);
    }

    public function tearDown() {
        \TestFiles::tearDownFiles();

        $this->config = null;
        $this->db = nul;
        $this->pm = null;
        $this->app = null;
    }

    public function testInitialization() {
        $this->assertInstanceOf('uarsoftware\dbpatch\App\PatchEngine',$this->app);
    }

    public function testApplyPatches() {

        $this->assertEquals(5,$this->app->applyPatches($this->pm));
    }

    public function testRecordPatches() {
        $patches = array();
        $patches[] = new Patch(\TestFiles::$files[0]);
        $patches[] = new Patch(\TestFiles::$files[1]);

        $this->assertCount(2,$this->app->recordPatches($patches));


    }
}
