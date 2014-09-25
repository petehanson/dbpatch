<?php

// need to run with phpunit --bootstrap boostrap.php path/to/test.php

namespace uarsoftware\dbpatch\App;

class ConfigManagerTest extends \PHPUnit_Framework_TestCase
{

    protected $basePath;
    protected $configManager;

    public function setUp() {

        $this->basePath = realpath('/tmp');
        if ($this->basePath === false) {
            throw new exception("realpath() couldn't determine the proper basePath to use");
        }

        $this->configManager = new ConfigManager();
    }

    public function tearDown() {
        $this->configManager = null;
    }

    protected function setupConfigFile($cwdPath,$relativePath,$configFile = 'config.php') {
        $targetPath = $this->basePath . DIRECTORY_SEPARATOR . $cwdPath . DIRECTORY_SEPARATOR . $relativePath;
        $targetFile = $targetPath . DIRECTORY_SEPARATOR . $configFile;
        $sourceFile = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'ConfigManagerTest' . DIRECTORY_SEPARATOR . $configFile;
        if (!file_exists($targetPath)) {
            mkdir($targetPath,0777,true);
        }
        $result = copy($sourceFile,$targetFile);

        if ($result === false) {
            throw new exception("Copy failed from {$sourceFile} to {$targetFile}");
        }
    }

    protected function tearDownConfigFile($cwdPath,$relativePath,$configFile = 'config.php') {
        $targetPath = $this->basePath . DIRECTORY_SEPARATOR . $cwdPath . DIRECTORY_SEPARATOR . $relativePath;
        $targetFile = $targetPath . DIRECTORY_SEPARATOR . $configFile;

        unlink($targetFile);

        $tempPath = $targetPath;
        do {
            rmdir($tempPath);
            $tempPath = dirname($tempPath);
        } while($tempPath != $this->basePath);

    }

    public function testInitialization() {
        $this->assertInstanceOf('uarsoftware\dbpatch\App\ConfigManager',$this->configManager);
    }

    public function testConfigFileFullPath() {

        $base = 'base1';
        $relative = 'cmtest';
        $this->setupConfigFile($base,$relative);
        $configPath = 'cmtest' . DIRECTORY_SEPARATOR . 'config.php';
        $path = $this->configManager->configFullPath($configPath,$this->basePath . DIRECTORY_SEPARATOR . $base);
        $this->tearDownConfigFile($base,$relative);
        $this->assertEquals($this->basePath . DIRECTORY_SEPARATOR . 'base1/cmtest/config.php',$path);


        $base = 'base1/base2';
        $relative = 'cmtest';
        $this->setupConfigFile($base,$relative);
        $configPath = '../cmtest' . DIRECTORY_SEPARATOR . 'config.php';
        $path = $this->configManager->configFullPath($configPath,$this->basePath . DIRECTORY_SEPARATOR . $base . DIRECTORY_SEPARATOR . 'base3');
        $this->tearDownConfigFile($base,$relative);
        $this->assertEquals($this->basePath . DIRECTORY_SEPARATOR . 'base1/base2/cmtest/config.php',$path);

        $base = 'base1';
        $relative = 'cmtest';
        $this->setupConfigFile($base,$relative);
        $configPath = $this->basePath . DIRECTORY_SEPARATOR . $base . DIRECTORY_SEPARATOR . 'cmtest' . DIRECTORY_SEPARATOR . 'config.php';
        $path = $this->configManager->configFullPath($configPath,$this->basePath . DIRECTORY_SEPARATOR . 'garbage');
        $this->tearDownConfigFile($base,$relative);
        $this->assertEquals($this->basePath . DIRECTORY_SEPARATOR . 'base1/cmtest/config.php',$path);


    }

}
