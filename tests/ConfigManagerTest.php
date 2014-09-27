<?php

// need to run with phpunit --bootstrap boostrap.php path/to/test.php

namespace uarsoftware\dbpatch\App;

class ConfigManagerTest extends \PHPUnit_Framework_TestCase
{
    protected $configManager;

    public function setUp() {
        \TestFiles::setUpFiles();
        $this->configManager = new ConfigManager();
    }

    public function tearDown() {
        $this->configManager = null;
        \TestFiles::tearDownFiles();
    }

    public function testInitialization() {
        $this->assertInstanceOf('uarsoftware\dbpatch\App\ConfigManager',$this->configManager);
    }

    public function testConfigFileFullPath() {

        // test just the concatination of the base dir and the relative path to the config
        $configPath = normalizeDirectory('level1/level1.php');
        $path = $this->configManager->configFullPath($configPath,\TestFiles::$baseDir);
        $this->assertEquals(normalizeDirectory(\TestFiles::$baseDir . '/level1/level1.php'),$path);

        // test just the concatination of the base dir and the relative path to the config, but also expanding .. and . operations
        $configPath = normalizeDirectory('./../level2/level2.php');
        $path = $this->configManager->configFullPath($configPath,normalizeDirectory(\TestFiles::$baseDir . '/level1/baddir'));
        $this->assertEquals(normalizeDirectory(\TestFiles::$baseDir . '/level1/level2/level2.php'),$path);

        // test passing a full reference to an actual config file
        $configPath = normalizeDirectory(\TestFiles::$baseDir . '/level1/level1.php');
        $path = $this->configManager->configFullPath($configPath,normalizeDirectory(\TestFiles::$baseDir . '/level1/baddir'));
        $this->assertEquals(normalizeDirectory(\TestFiles::$baseDir . '/level1/level1.php'),$path);
    }

    public function testFindConfigFile() {
        $rootPath = normalizeDirectory(\TestFiles::$baseDir . '/level1');

        $configPath = $this->configManager->findConfigFile($rootPath);
        $this->assertEquals(normalizeDirectory(\TestFiles::$baseDir . '/level1/level2/config.php'),$configPath);


        $configFileName = 'level2.php';
        $configPath = $this->configManager->findConfigFile($rootPath,$configFileName);
        $this->assertEquals(normalizeDirectory(\TestFiles::$baseDir . '/level1/level2/level2.php'),$configPath);

    }

    public function testConfig() {
        $configPath = normalizeDirectory(\TestFiles::$baseDir . '/level1/level1.php');
        $path = $this->configManager->configFullPath($configPath,normalizeDirectory(\TestFiles::$baseDir . '/level1/baddir'));
        $config = $this->configManager->getConfig($path);
        $this->assertInstanceOf('uarsoftware\dbpatch\App\ConfigInterface',$config);

    }

}
