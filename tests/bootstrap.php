<?php

$tests_dir = dirname(__FILE__);
$base_dir = dirname($tests_dir);

require_once($base_dir . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php');

use uarsoftware\dbpatch\App\Config;
use uarsoftware\dbpatch\App\Patch;
use uarsoftware\dbpatch\App\DatabaseInterface;


function setUpFiles($basePath) {

    $sqlPath =  $basePath . DIRECTORY_SEPARATOR . 'sql';
    $initPath = $basePath . DIRECTORY_SEPARATOR . 'sql' . DIRECTORY_SEPARATOR . 'init';
    $schemaPath = $basePath . DIRECTORY_SEPARATOR . 'sql' . DIRECTORY_SEPARATOR . 'schema';
    $dataPath = $basePath . DIRECTORY_SEPARATOR . 'sql' . DIRECTORY_SEPARATOR . 'data';
    $scriptPath = $basePath . DIRECTORY_SEPARATOR . 'sql' . DIRECTORY_SEPARATOR . 'script';

    $file1 = $schemaPath . DIRECTORY_SEPARATOR . "1test.sql";
    $file2 = $schemaPath . DIRECTORY_SEPARATOR . "2test.sql";
    $file3 = $dataPath . DIRECTORY_SEPARATOR . "3test.sql";

    mkdir($sqlPath);
    mkdir($initPath);
    mkdir($schemaPath);
    mkdir($dataPath);
    mkdir($scriptPath);

    file_put_contents($file1,"create table mytest2 (id int);");
    file_put_contents($file2,"alter table mytest2 add name char(1);");

    file_put_contents($file3,"insert into mytest2 values (1,'a');");
}

function tearDownFiles($basePath) {

    $sqlPath =  $basePath . DIRECTORY_SEPARATOR . 'sql';
    $initPath = $basePath . DIRECTORY_SEPARATOR . 'sql' . DIRECTORY_SEPARATOR . 'init';
    $schemaPath = $basePath . DIRECTORY_SEPARATOR . 'sql' . DIRECTORY_SEPARATOR . 'schema';
    $dataPath = $basePath . DIRECTORY_SEPARATOR . 'sql' . DIRECTORY_SEPARATOR . 'data';
    $scriptPath = $basePath . DIRECTORY_SEPARATOR . 'sql' . DIRECTORY_SEPARATOR . 'script';

    $file1 = $schemaPath . DIRECTORY_SEPARATOR . "1test.sql";
    $file2 = $schemaPath . DIRECTORY_SEPARATOR . "2test.sql";
    $file3 = $dataPath . DIRECTORY_SEPARATOR . "3test.sql";


    unlink($file3);
    unlink($file2);
    unlink($file1);

    rmdir($scriptPath);
    rmdir($dataPath);
    rmdir($schemaPath);
    rmdir($initPath);
    rmdir($sqlPath);

}

class MockDatabase implements DatabaseInterface {

    protected $appliedPatches;

    public function __construct(Config $config) {
        $this->appliedPatches = array();
    }

    public function setAppliedPatches(Array $list) {
        $this->appliedPatches = array();
        foreach ($list as $item) {
            $patch = new Patch($item);
            $this->appliedPatches[] = $patch;
        }
    }

    public function getAppliedPatches() {
        return $this->appliedPatches;
    }

}