<?php

namespace uarsoftware\dbpatch\App;
use uarsoftware\dbpatch\Util\Util;

class ConfigManager {

    protected $configs = array();

    public function __construct() {
    }

    public function determineConfig($configOption,$dbPatchBasePath) {

        if ($configOption) {
            $path = $this->configFullPath($configOption,$dbPatchBasePath);
        } else {
            $path = $this->findConfigFile($dbPatchBasePath);
        }


        $config = $this->getConfig($path);
        $config->setConfigFilePath($path);
        return $config;
    }

    public function configFullPath($configPath,$basePath) {

        $fullPath = realpath($configPath);
        if ($fullPath !== false) {
            return $fullPath;
        }

        $fullPath = $basePath . DIRECTORY_SEPARATOR . $configPath;
        $fullPath = Util::getAbsolutePath($fullPath);
        $fullPath = realpath($fullPath);

        if ($fullPath === false) {
            throw new \exception("Full path retrieval for the config file failed, config file or path doesn't exist");
        }

        return $fullPath;
    }

    public function findConfigFile($rootPath,$configFileName = 'config.php') {
        $result = Util::recursiveDirectoryFileSearch($rootPath,$configFileName);

        if ($result === false) {
            throw new \exception("Could not find a file at {$rootPath} called {$configFileName}");
        }

        return $result;
    }

    public function getConfig($path) {
        if (!file_exists($path)) {
            throw new \exception("Config file loading failed. Config file does not exist at {$path}");
        }
        $config = require($path);
        return $config;
    }

    public function createConfigFolders($rootPath,$configDir) {
        $path = $rootPath . DIRECTORY_SEPARATOR . $configDir;
        $path = Util::getAbsolutePath($path);

        if (!file_exists($path)) {
            mkdir($path);
        }

        $configFile = $path . DIRECTORY_SEPARATOR . "config.php";

        $contents = <<<'EOF'
<?php

$myconfig = new uarsoftware\dbpatch\App\Config("myconfigid","mysql","localhost","mydbname","myuser","mypass");
$myconfig->setPort(3306);
$myconfig->disableTrackingPatchesInFile();
$myconfig->setConfigFilePath(__FILE__);
$myconfig->setBasePath(dirname(__FILE__));

return $myconfig;
EOF;

        if (!file_exists($configFile)) {
            file_put_contents($configFile,$contents);
        }

        $sqlPath = $path . DIRECTORY_SEPARATOR . "sql";
        if (!file_exists($sqlPath)) mkdir($sqlPath);
        $sqlPath = $path . DIRECTORY_SEPARATOR . "sql" . DIRECTORY_SEPARATOR . "init";
        if (!file_exists($sqlPath)) mkdir($sqlPath);
        $sqlPath = $path . DIRECTORY_SEPARATOR . "sql" . DIRECTORY_SEPARATOR . "schema";
        if (!file_exists($sqlPath)) mkdir($sqlPath);
        $sqlPath = $path . DIRECTORY_SEPARATOR . "sql" . DIRECTORY_SEPARATOR . "data";
        if (!file_exists($sqlPath)) mkdir($sqlPath);
        $sqlPath = $path . DIRECTORY_SEPARATOR . "sql" . DIRECTORY_SEPARATOR . "script";
        if (!file_exists($sqlPath)) mkdir($sqlPath);

        return $path;
    }
}
