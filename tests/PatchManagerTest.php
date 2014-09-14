<?php

// need to run with phpunit --bootstrap boostrap.php path/to/test.php

namespace uarsoftware\dbpatch\App;

use uarsoftware\dbpatch\App\Config;
use uarsoftware\dbpatch\App\Patch;
use uarsoftware\dbpatch\App\PatchManager;
use uarsoftware\dbpatch\App\DatabaseInterface;

class PatchManagerTest extends \PHPUnit_Framework_TestCase
{
    protected $config;
    protected $pm;

    public function setUp() {

        setUpFiles("/tmp");

        $this->config = new Config("test","mysql","localhost","test","root","root");
        $this->config->setBasePath("/tmp");

        $db = new \MockDatabase($this->config);
        $db->setAppliedPatches(array("3test.sql"));

        $this->pm = new PatchManager($this->config,$db);
    }

    public function tearDown() {

        $this->pm = null;

        tearDownFiles("/tmp");
    }

    public function testInstantiation() {
        $this->assertInstanceOf('uarsoftware\dbpatch\App\PatchManager',$this->pm);
    }

    public function testPatchDifference() {
        $unappliedPatches = $this->pm->getUnappliedPatches();
        $this->assertCount(2,$unappliedPatches);

    }

    public function testPatchCount() {
        //$patches = array("sql/schema/1test.sql","2test.sql");
        //$this->assertCount(2,$this->pm->getPatches());
    }
}
