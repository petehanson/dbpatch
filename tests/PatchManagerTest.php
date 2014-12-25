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
        $this->config->setBasePath(realpath("/tmp"));

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
        $this->assertCount(5,$unappliedPatches);

    }

    public function testSpecificPatchToApply() {
        $patch = new Patch(\TestFiles::$files[0]);
        $this->pm->addSpecificPatchToApply($patch);
        $unappliedPatches = $this->pm->getUnappliedPatches();
        $this->assertCount(1,$unappliedPatches);
        $this->assertEquals($patch,$unappliedPatches[0]);

        $this->pm->resetSpecificPatchesToApply();
        $unappliedPatches = $this->pm->getUnappliedPatches();
        $this->assertCount(5,$unappliedPatches);


        $this->pm->resetSpecificPatchesToApply();
        $patch1 = new Patch(\TestFiles::$files[0]);
        $patch2 = new Patch(\TestFiles::$files[1]);
        $this->pm->addSpecificPatchToApply($patch1);
        $this->pm->addSpecificPatchToApply($patch2);
        $unappliedPatches = $this->pm->getUnappliedPatches();
        $this->assertCount(2,$unappliedPatches);

        $this->pm->resetSpecificPatchesToApply();
        $patch1 = new Patch(\TestFiles::$files[0]);
        $patch2 = new Patch(\TestFiles::$files[1]);
        $patches = array($patch1,$patch2);
        $this->pm->addSpecificPatchesToApply($patches);
        $unappliedPatches = $this->pm->getUnappliedPatches();
        $this->assertCount(2,$unappliedPatches);
    }

    public function testPatchOrder() {
        $unappliedPatches = $this->pm->getUnappliedPatches();

        $correct_order = array();
        $correct_order[] = new Patch(\TestFiles::$files[0]);
        $correct_order[] = new Patch(\TestFiles::$files[4]);
        $correct_order[] = new Patch(\TestFiles::$files[3]);
        $correct_order[] = new Patch(\TestFiles::$files[5]);
        $correct_order[] = new Patch(\TestFiles::$files[1]);

        //print_r($unappliedPatches);
        //print_r($correct_order);

        $this->assertEquals($correct_order[0],$unappliedPatches[0]);
        $this->assertEquals($correct_order[1],$unappliedPatches[1]);
        $this->assertEquals($correct_order[2],$unappliedPatches[2]);
        $this->assertEquals($correct_order[3],$unappliedPatches[3]);
        $this->assertEquals($correct_order[4],$unappliedPatches[4]);





    }

    public function testCreatePatchList() {
        $patches = array("10test.sql","11test.sql");

        $patchObjects = $this->pm->createPatchList($patches);

        $this->assertCount(2,$patchObjects);
        foreach ($patchObjects as $patch) {
            $this->assertInstanceOf('uarsoftware\dbpatch\App\Patch',$patch);
        }
    }

    public function testApplyPatch() {

        $files = \TestFiles::$files;
        $patch = new Patch($files[0]);
        $resultPatch = $this->pm->applyPatch($patch);

        $this->assertTrue($resultPatch->isSuccessful());

    }

    public function testApplyPatchException() {

        $this->setExpectedException('\exception');

        $config = new Config("test","mysql","localhost","test","root","root");
        $config->setBasePath(realpath("/tmp"));

        $db = new \MockDatabase($config);
        $db->queryFailed(100,"test");
        $pm = new PatchManager($config,$db);


        $files = \TestFiles::$files;
        $patch = new Patch($files[0]);

        $resultPatch = $pm->applyPatch($patch);


    }

    public function testCreatePatch() {

        $timestamp = 1414624456;

        $compareFilename = "20141029_231416.a_test_description.sql";
        $compareFilename = $this->config->getSchemaPath() . DIRECTORY_SEPARATOR . $compareFilename;

        $description = "A test description";
        $filename = $this->pm->createSchemaPatchFile($description,"sql",$timestamp);
        $this->assertTrue(file_exists($filename));
        $this->assertEquals($compareFilename,$filename);

        unlink($filename);

        $description = "Data Patch Test";
        $timestamp = 1414625055;
        $compareFilename = $this->config->getDataPath() . DIRECTORY_SEPARATOR . "20141029_232415.data_patch_test.sql";

        $filename = $this->pm->createDataPatchFile($description,"sql",$timestamp);
        $this->assertTrue(file_exists($filename));
        $this->assertEquals($compareFilename,$filename);

        unlink($filename);


        $description = "PHP Patch Test";
        $timestamp = 1414625055;
        $compareFilename = $this->config->getScriptPath() . DIRECTORY_SEPARATOR . "20141029_232415.php_patch_test.php";

        $filename = $this->pm->createScriptPatchFile($description,"php",$timestamp);
        $this->assertTrue(file_exists($filename));
        $this->assertEquals($compareFilename,$filename);

        unlink($filename);
    }

}
