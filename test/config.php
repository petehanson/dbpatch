<?php

$test_config = new uarsoftware\dbpatch\App\Config("test","mysql","localhost","test","root","root");
$test_config->setPort(3306);
$test_config->disableTrackingPatchesInFile();
$test_config->setConfigFilePath(__FILE__);
$test_config->setBasePath(dirname(__FILE__));

return $test_config;
