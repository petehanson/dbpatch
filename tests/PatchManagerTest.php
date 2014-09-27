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

        \TestFiles::setUpFiles();

        $this->config = new Config("test","mysql","localhost","test","root","root");
        $this->config->setBasePath("/tmp");

        $db = new \MockDatabase($this->config);
        $db->setAppliedPatches(array("3test.sql"));

        $this->pm = new PatchManager($this->config,$db);
    }

    public function tearDown() {

        $this->pm = null;

        \TestFiles::tearDownFiles();
    }

    public function testInstantiation() {
        $this->assertInstanceOf('uarsoftware\dbpatch\App\PatchManager',$this->pm);
    }

    public function testPatchDifference() {
        $unappliedPatches = $this->pm->getUnappliedPatches();
        $this->assertCount(2,$unappliedPatches);

    }

    public function testSpecificPatchToApply() {
        $patch = new Patch("1test.sql");
        $this->pm->addSpecificPatchToApply($patch);
        $unappliedPatches = $this->pm->getUnappliedPatches();
        $this->assertCount(1,$unappliedPatches);
        $this->assertEquals($patch,$unappliedPatches[0]);

        $this->pm->resetSpecificPatchesToApply();
        $unappliedPatches = $this->pm->getUnappliedPatches();
        $this->assertCount(2,$unappliedPatches);


        $this->pm->resetSpecificPatchesToApply();
        $patch1 = new Patch("1test.sql");
        $patch2 = new Patch("2test.sql");
        $this->pm->addSpecificPatchToApply($patch1);
        $this->pm->addSpecificPatchToApply($patch2);
        $unappliedPatches = $this->pm->getUnappliedPatches();
        $this->assertCount(2,$unappliedPatches);

        $this->pm->resetSpecificPatchesToApply();
        $patch1 = new Patch("1test.sql");
        $patch2 = new Patch("2test.sql");
        $patches = array($patch1,$patch2);
        $this->pm->addSpecificPatchesToApply($patches);
        $unappliedPatches = $this->pm->getUnappliedPatches();
        $this->assertCount(2,$unappliedPatches);
    }

    public function testCreatePatchList() {
        $patches = array("10test.sql","11test.sql");

        $patchObjects = $this->pm->createPatchList($patches);

        $this->assertCount(2,$patchObjects);
        foreach ($patchObjects as $patch) {
            $this->assertInstanceOf('uarsoftware\dbpatch\App\Patch',$patch);
        }
    }
}
