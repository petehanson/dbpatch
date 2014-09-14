<?php


$test_config = new Config("test");
$test_config->setDriver("mysql");
$test_config->setHost("localhost");
$test_config->setPort(3306);
$test_config->setDatabaseName("test");
$test_config->setUser("root");
$test_config->setPassword("root");
$test_config->disableTrackingPatchesInFile();


$test1_config = new Config("test1");
$test1_config->setDriver("mysql");
$test1_config->setHost("localhost");
$test1_config->setPort(3306);
$test1_config->setDatabaseName("test1");
$test1_config->setUser("root");
$test1_config->setPassword("root");
$test1_config->disableTrackingPatchesInFile();

return array($test_config,$test1_config);


/*
return array (
    "test" => array (
        "driver"=>"mysql",
        "host"=>"localhost",
        "port"=>"3306",
        "name"=>"test",
        "user"=>"root",
        "password"=>"root",
        "track_patches_in_file"=>false,
        "use_cli_client_for_reset"=>false,
    ),
    "test1" => array (
        "driver"=>"mysql",
        "host"=>"localhost",
        "port"=>"3306",
        "name"=>"test1",
        "user"=>"root",
        "password"=>"root",
        "track_patches_in_file"=>false,
        "use_cli_client_for_reset"=>false,
    ),
);
*/
