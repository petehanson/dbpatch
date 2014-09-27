<?php

$test_config = new uarsoftware\dbpatch\App\Config("test","mysql","localhost","test","root","root");
$test_config->setPort(3306);
$test_config->disableTrackingPatchesInFile();

return $test_config;
