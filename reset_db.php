<?php
// if we weren't supplied a wrapper from an include, define our own path
// use this path everywhere
if (!defined('DBPATCH_BASE_PATH')) define('DBPATCH_BASE_PATH', dirname(__FILE__));

require_once("Console/Getopt.php");

// get overridden db class
require_once (DBPATCH_BASE_PATH  . "/db.php");

require_once(dirname(__FILE__) . "/app/PatchEngine.php");
require_once(dirname(__FILE__) . "/app/PatchFileBundler.php");
require_once (dirname(__FILE__) . '/trackers/TrackerInterface.php');
require_once (dirname(__FILE__) . '/trackers/XmlFileVersionTracker.php');
require_once (dirname(__FILE__) . '/trackers/FileTrackerFactory.php');

$masterConfig = new db();
$singleDbConfigs = $masterConfig->getSingleDbConfigs();

foreach ($singleDbConfigs as $db) {
    /* @var $db DbPatch_Config_SingleDb */
    require_once(dirname(__FILE__) . '/database_drivers/' . $db->dbClassFile);
}

require_once(dirname(__FILE__) . "/printers/cli.php");

// set up the printer
$printLevel = 1; // used with quiet and verbose
$printer = new printer($printLevel);

foreach ($singleDbConfigs as $config) {
    $app = new Patch_Engine($config, $printer, DBPATCH_BASE_PATH);

    // get db connection
    $db = $app->getDb();

    // reset database
    echo "Dropping database " . $config->dbName . PHP_EOL;
    $db->execute('DROP DATABASE `' . $config->dbName . '`');
    if ($db->has_error()) die('error');

    // reinit so it reselects the database
    unset($app);
    $app = new Patch_Engine($config, $printer, DBPATCH_BASE_PATH);

    // import base sql
    $output = array();
    $basePath = DBPATCH_BASE_PATH . $config->basepath . '/' . $config->basefile;
    $cmd = 'mysql -h ' . $config->dbHost . ' -u ' . $config->dbUsername . ' --password="' . $config->dbPassword . '" ' . $config->dbName . ' < ' . $basePath;
    $retval = null;
    exec($cmd, $output, $retval);
    if ($retval != 0) {
        echo 'failed to import base sql' . PHP_EOL;
    } else {
        echo 'database imported successfully' . PHP_EOL;
    }
    echo implode(PHP_EOL, $output);
    if ($retval != 0) die();

    // import patches
    $app->apply_patches();

    echo "Database " . $config->dbName . " updated." . PHP_EOL;
}