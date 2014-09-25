<?php

$test_config = new Config("test");
$test_config->setDriver("mysql");
$test_config->setHost("localhost");
$test_config->setPort(3306);
$test_config->setDatabaseName("test");
$test_config->setUser("root");
$test_config->setPassword("root");
$test_config->disableTrackingPatchesInFile();

return $test_config;
