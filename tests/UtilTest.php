<?php

// need to run with phpunit --bootstrap boostrap.php path/to/test.php

namespace uarsoftware\dbpatch\App;
use uarsoftware\dbpatch\Util\Util;

class UtilTest extends \PHPUnit_Framework_TestCase
{
    public function setUp() {
        \TestFiles::setUpFiles();
    }

    public function tearDown() {
        \TestFiles::tearDownFiles();
    }

    public function testRecursiveDirectoryFileSearch() {
        $configPath = Util::recursiveDirectoryFileSearch(normalizeDirectory(\TestFiles::$baseDir . '/level1'),"config.php");
        $this->assertEquals(normalizeDirectory(\TestFiles::$baseDir . '/level1/level2/config.php'),$configPath);

        $configPath = Util::recursiveDirectoryFileSearch(normalizeDirectory(\TestFiles::$baseDir . '/level1'),"level2.php");
        $this->assertEquals(normalizeDirectory(\TestFiles::$baseDir . '/level1/level2/level2.php'),$configPath);
    }

}
