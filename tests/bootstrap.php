<?php

$tests_dir = dirname(__FILE__);
$base_dir = dirname($tests_dir);

TestFiles::$baseDir = realpath("/tmp");
if (TestFiles::$baseDir === false) {
    throw new exception("baseDir is invalid");
}

CLI::$mysqlBinary = 'mysql';
CLI::$mysqlUser = 'root';
CLI::$mysqlPass = 'root';

require_once($base_dir . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php');

use uarsoftware\dbpatch\App\Config;
use uarsoftware\dbpatch\App\Patch;
use uarsoftware\dbpatch\App\PatchInterface;
use uarsoftware\dbpatch\App\DatabaseInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Formatter\OutputFormatterInterface;


class TestFiles {

    static public $baseDir;
    static public $files;
    static public $schemaPath;
    static public $dataPath;
    static public $scriptPath;

    public static function setUpFiles() {

        // set up basic patches
        $sqlPath =  self::$baseDir . DIRECTORY_SEPARATOR . 'sql';
        $initPath = self::$baseDir . DIRECTORY_SEPARATOR . 'sql' . DIRECTORY_SEPARATOR . 'init';
        $schemaPath = self::$baseDir . DIRECTORY_SEPARATOR . 'sql' . DIRECTORY_SEPARATOR . 'schema';
        $dataPath = self::$baseDir . DIRECTORY_SEPARATOR . 'sql' . DIRECTORY_SEPARATOR . 'data';
        $scriptPath = self::$baseDir . DIRECTORY_SEPARATOR . 'sql' . DIRECTORY_SEPARATOR . 'script';

        self::$schemaPath = $schemaPath;
        self::$dataPath = $dataPath;
        self::$scriptPath = $scriptPath;

        $file1 = $schemaPath . DIRECTORY_SEPARATOR . "1test.sql";
        $file2 = $schemaPath . DIRECTORY_SEPARATOR . "2test.sql";
        $file3 = $dataPath . DIRECTORY_SEPARATOR . "3test.sql";
        $file4 = $dataPath . DIRECTORY_SEPARATOR . "20141009_123456_test4.sql";
        $file5 = $schemaPath . DIRECTORY_SEPARATOR . "20141008_114523_test5.sql";
        $file6 = $schemaPath . DIRECTORY_SEPARATOR . "20141009_140000_test6.sql";

        self::$files = array();
        self::$files[] = $file1;
        self::$files[] = $file2;
        self::$files[] = $file3;
        self::$files[] = $file4;
        self::$files[] = $file5;
        self::$files[] = $file6;

        mkdir($sqlPath);
        mkdir($initPath);
        mkdir($schemaPath);
        mkdir($dataPath);
        mkdir($scriptPath);

        file_put_contents($file1,"create table mytest2 (id int);");
        file_put_contents($file2,"alter table mytest2 add name char(1);");
        file_put_contents($file3,"insert into mytest2 values (1,'a');");
        file_put_contents($file4,"");
        file_put_contents($file5,"");
        file_put_contents($file6,"");

        // set up some test dirs for configFullPath loading
        mkdir(normalizeDirectory(self::$baseDir . '/level1'),0777,true);
        mkdir(normalizeDirectory(self::$baseDir . '/level1/level2'),0777,true);
        mkdir(normalizeDirectory(self::$baseDir . '/level1/level2/level3'),0777,true);

        self::writeConfigFile(normalizeDirectory(self::$baseDir . '/level1/level1.php'));
        self::writeConfigFile(normalizeDirectory(self::$baseDir . '/level1/level2/level2.php'));
        self::writeConfigFile(normalizeDirectory(self::$baseDir . '/level1/level2/config.php'));

    }

    public static function tearDownFiles() {

        $sqlPath = self::$baseDir . DIRECTORY_SEPARATOR . 'sql';
        $initPath = self::$baseDir . DIRECTORY_SEPARATOR . 'sql' . DIRECTORY_SEPARATOR . 'init';
        $schemaPath = self::$baseDir . DIRECTORY_SEPARATOR . 'sql' . DIRECTORY_SEPARATOR . 'schema';
        $dataPath = self::$baseDir . DIRECTORY_SEPARATOR . 'sql' . DIRECTORY_SEPARATOR . 'data';
        $scriptPath = self::$baseDir . DIRECTORY_SEPARATOR . 'sql' . DIRECTORY_SEPARATOR . 'script';

        /*
        $file1 = $schemaPath . DIRECTORY_SEPARATOR . "1test.sql";
        $file2 = $schemaPath . DIRECTORY_SEPARATOR . "2test.sql";
        $file3 = $dataPath . DIRECTORY_SEPARATOR . "3test.sql";
        $file4 = $dataPath . DIRECTORY_SEPARATOR . "20141009_123456_test4.sql";
        $file5 = $dataPath . DIRECTORY_SEPARATOR . "20141008_114523_test5.sql";
        $file6 = $dataPath . DIRECTORY_SEPARATOR . "20141009_140000_test6.sql";
        */

        foreach (self::$files as $file) {
            unlink($file);
        }

        /*
        unlink($file6);
        unlink($file5);
        unlink($file4);
        unlink($file3);
        unlink($file2);
        unlink($file1);
        */

        rmdir($scriptPath);
        rmdir($dataPath);
        rmdir($schemaPath);
        rmdir($initPath);
        rmdir($sqlPath);


        unlink(normalizeDirectory(self::$baseDir . '/level1/level2/config.php'));
        unlink(normalizeDirectory(self::$baseDir . '/level1/level2/level2.php'));
        unlink(normalizeDirectory(self::$baseDir . '/level1/level1.php'));

        rmdir(normalizeDirectory(self::$baseDir . '/level1/level2/level3'));
        rmdir(normalizeDirectory(self::$baseDir . '/level1/level2'));
        rmdir(normalizeDirectory(self::$baseDir . '/level1'));

    }

    public static function writeConfigFile($file) {
        $contents = '<?php

        $test_config = new \uarsoftware\dbpatch\App\Config("test","mysql","localhost","test","root","root");
        $test_config->setPort(3306);
        $test_config->disableTrackingPatchesInFile();
        $test_config->setConfigFilePath(__FILE__);
        $test_config->setBasePath(dirname(__FILE__));

        return $test_config;';

        file_put_contents($file,$contents);
    }
}

function normalizeDirectory($dir) {
    return str_replace(array("/","\\"),DIRECTORY_SEPARATOR,$dir);
}

class MockDatabase implements DatabaseInterface {

    protected $appliedPatches;
    protected $queryResult;

    public function __construct(Config $config) {
        $this->appliedPatches = array();

        $this->querySuccess();
    }

    public function setAppliedPatches(Array $list) {
        $this->appliedPatches = array();
        foreach ($list as $item) {
            $patch = new Patch($item);
            $patch->setAsAppliedPatch();
            $this->appliedPatches[] = $patch;
        }
    }

    public function getAppliedPatches() {
        return $this->appliedPatches;
    }

    public function querySuccess() {
        $this->queryResult = new stdClass();
        $this->queryResult->status = true;
    }

    public function queryFailed($code,$message) {
        $this->queryResult = new stdClass();
        $this->queryResult->status = false;
        $this->queryResult->errorCode = $code;
        $this->queryResult->errorMessage = $message;
    }

    public function executeQuery($sql) {
        return $this->queryResult;
    }

    public function recordPatch(PatchInterface $patch) {
        return true;
    }
}



class MockOutput implements OutputInterface {

    public function write($messages, $newline = false, $type = self::OUTPUT_NORMAL) {}

    public function writeln($messages, $type = self::OUTPUT_NORMAL) {}

    public function setVerbosity($level) {}
    public function getVerbosity() {}
    public function setDecorated($decorated) {}
    public function isDecorated() {}
    public function setFormatter(OutputFormatterInterface $formatter) {}
    public function getFormatter() {}
}


class CLI {
    static public $mysqlBinary;
    static public $mysqlUser;
    static public $mysqlPass;
}

class BootstrapUtil {

    static public function generateRandomString($length = 8) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $randomString;
    }
}