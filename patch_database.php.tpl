<?php
// add a config.php and db.php here locally
// in this folder

// make DBPATCH_BASE_PATH local to here
define('DBPATCH_BASE_PATH', dirname(__FILE__));
// now get the database patcher
require_once(dirname(__FILE__) . '/../path/to/patch_database.php');
//require_once('/path/to/patch_database.php');