<?php

// need to run with phpunit --bootstrap boostrap.php path/to/test.php

namespace uarsoftware\dbpatch\App;

class ConfigLocationTest extends \PHPUnit_Framework_TestCase
{
    protected $configLocation;

    public function setUp() {
        $file = \TestFiles::setUpDotConfig();
        $this->configLocation = new ConfigLocation($file);
    }

    public function tearDown() {
        $this->configLocation = null;
        //\TestFiles::tearDownDotConfig();
    }

    public function testInitialization() {
        $this->assertInstanceOf('uarsoftware\dbpatch\App\ConfigLocation',$this->configLocation);
    }

    public function testFileExists() {
        $this->assertTrue($this->configLocation->doesFileExist());
    }

    public function testConfigCount() {
        // this test checks that 2 rows are returned
        $file = \TestFiles::setUpDotConfig();
        \TestFiles::addLineDotConfig("test1: /tmp/test1/config.php");
        \TestFiles::addLineDotConfig("test2: /tmp/test2/config.php");
        $this->configLocation = new ConfigLocation($file);
        $this->assertEquals(2,$this->configLocation->configLocationCount());

        // this is a test to ensure the count returns 0 values
        $file = \TestFiles::setUpDotConfig();
        $this->configLocation = new ConfigLocation($file);
        $this->assertEquals(0,$this->configLocation->configLocationCount());

        // this test checks that 1 row is returned
        $file = \TestFiles::setUpDotConfig();
        \TestFiles::addLineDotConfig("test1: /tmp/test1/config.php");
        $this->configLocation = new ConfigLocation($file);
        $this->assertEquals(1,$this->configLocation->configLocationCount());


    }

    public function testConfigLocationExists() {
        $file = \TestFiles::setUpDotConfig();
        \TestFiles::addLineDotConfig("test1: /tmp/test1/config.php");
        \TestFiles::addLineDotConfig("test2: /tmp/test2/config.php");
        $this->configLocation = new ConfigLocation($file);

        // lets check to ensure that test1 exists
        $this->assertTrue($this->configLocation->doesConfigLocationExist("test1"));

        // lets check to ensure that test2 exists
        $this->assertTrue($this->configLocation->doesConfigLocationExist("test2"));

        // lets check to ensure that one that isn't in the file returns false
        $this->assertFalse($this->configLocation->doesConfigLocationExist("nothere"));
    }

    public function testConfigLocationRetrieval() {
        $file = \TestFiles::setUpDotConfig();
        \TestFiles::addLineDotConfig("test1: /tmp/test1/config.php");
        \TestFiles::addLineDotConfig("test2: /tmp/test2/config.php");
        $this->configLocation = new ConfigLocation($file);

        $this->assertEquals("/tmp/test1/config.php",$this->configLocation->getConfigLocation("test1"));
        $this->assertEquals("/tmp/test2/config.php",$this->configLocation->getConfigLocation("test2"));
        $this->assertEquals(null,$this->configLocation->getConfigLocation("notexist"));

    }

    public function testConfigLocationRetrievalFirst() {
        // loads two configs, then makes sure we get the first one
        $file = \TestFiles::setUpDotConfig();
        \TestFiles::addLineDotConfig("test1: /tmp/test1/config.php");
        \TestFiles::addLineDotConfig("test2: /tmp/test2/config.php");
        $this->configLocation = new ConfigLocation($file);
        $this->assertEquals("/tmp/test1/config.php",$this->configLocation->getFirstConfigLocation());

        // loads one config and makes sure we get it
        $file = \TestFiles::setUpDotConfig();
        \TestFiles::addLineDotConfig("test2: /tmp/test2/config.php");
        $this->configLocation = new ConfigLocation($file);
        $this->assertEquals("/tmp/test2/config.php",$this->configLocation->getFirstConfigLocation());

        // loads no configs and we don't get anything back, but null
        $file = \TestFiles::setUpDotConfig();
        $this->configLocation = new ConfigLocation($file);
        $this->assertEquals(null,$this->configLocation->getFirstConfigLocation());

    }

}
