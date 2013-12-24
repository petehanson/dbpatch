#!/usr/bin/php
<?php
/**
 *
 * Script to initiate db patching.
 *
 *   Command line invocation of process to create versioned
 *   updates to a data store.
 *
 *   Use command line switch --help to get documentation on input
 *   parameters.
 *   @package patch_database.php
 *   @author Pete Hanson  phanson@uarass.com
 *   @copyright Pete Hanson  2008
 *
 */
/**
 * variable $version documents the implementation level.
 *
 */
$version = "0.31";

/**
 * Set of files required to run the application
 *
 */
// sets the working directory to the folder the script runs from

ini_set('error_reporting', E_ALL & ~E_STRICT);

// if we weren't supplied a wrapper from an include, define our own path
// use this path everywhere
if (!defined('DBPATCH_BASE_PATH')) define('DBPATCH_BASE_PATH', dirname(__FILE__));

// require from global path
require_once("Console/Getopt.php");

// get overridden db class
require_once (DBPATCH_BASE_PATH  . "/db.php");

// pull in the rest of the engine
require_once (dirname(__FILE__) . "/app/PatchEngine.php");
require_once (dirname(__FILE__) . '/app/PatchFileBundler.php');
require_once (dirname(__FILE__) . '/trackers/TrackerInterface.php');
require_once (dirname(__FILE__) . '/trackers/XmlFileVersionTracker.php');
require_once (dirname(__FILE__) . '/trackers/FileTrackerFactory.php');
require_once (dirname(__FILE__) . '/database_drivers/DriverFactory.php');

$masterConfig = new db();
$singleDbConfigs = $masterConfig->getSingleDbConfigs();

foreach ($singleDbConfigs as $db) {
    /* @var $db DbPatch_Config_SingleDb */

    if (isset($db->dbType))
    {
        require_once(dirname(__FILE__) . '/database_drivers/' . $db->dbType . '_database.php');
    }
    else
        require_once(dirname(__FILE__) . '/database_drivers/' . $db->dbClassFile);

}

require_once(dirname(__FILE__) . "/printers/cli.php");

/**
 * pathToScript is the execution directory
 *
 */
/*
$pathToScript = dirname($_SERVER['SCRIPT_FILENAME']);

chdir($pathToScript);
 */

// chdir to the base path
chdir(DBPATCH_BASE_PATH);

/**
 *  Following here is the execution loop itself.
 * - use Console_Getopt to obtain exec switches
 * - test for invalid switches
 * - set default values
 * - parse the switches and set values
 *
 *
 */
try {


    // two types of options, actions and modifiers
    // actions: help, list, add, record, patch, create
    // modifiers: verbose, quiet, skip (works only with patch)
    $console = new Console_Getopt();
    $opts = $console->getOpt($argv, "hqvlpaf::s::S::r::c::m::", array("help", "list", "verbose", "quiet", "add=",
                "skip=", "skip-and-record=", "record=", "create=", "merge=", "patch", "create-database-folders="));

    //print_r($opts);

    if (PEAR::isError($opts)) {
        fwrite(STDERR, $opts->getMessage() . "\n");
        fwrite(STDERR, "Run for help: {$argv[0]} --help\n");
        exit(INVALID_OPTION);
    }


    // records the action type and value
    $action = null;
    $action_value = null;
    $actions_issued = 0;

    // modifier defaults
    $printLevel = 1; // used with quiet and verbose
    $skip_value = null;
    $record_value = null;

    if (is_array($opts[0])) {
        foreach ($opts[0] as $argParameter) {
            $key = $argParameter[0];
            $value = $argParameter[1];

            if ($key == "h" || $key == "--help") {
                $action = "help";
                $actions_issued++;
            }
            
            if ($key == "--create-database-folders") {
                $action = "create-database-folders";
                if (!empty($value)) {
                    $action_value = $value;
                    $actions_issued++;
                }
            }

            if ($key == "v" || $key == "--verbose") {
                $printLevel = 2;
            }

            if ($key == "q" || $key == "--quiet") {
                $printLevel = 0;
            }


            if ($key == "l" || $key == "--list") {
                $action = "list";
                $actions_issued++;
            }

            if ($key == "a" || $key == "--add") {
                $action = "add";
                $action_value = $value;
                $actions_issued++;
            }

            if ($key == "s" || $key == "--skip") {
                $skip_value = $value;
            }

            if ($key == "S" || $key == "--skip-and-record") {
                $skip_value = $value;
                $record_value = $value;
            }

            if ($key == "r" || $key == "--record") {
                $action = "record";
                $action_value = $value;
                $actions_issued++;
            }

            if ($key == "c" || $key == "--create") {
                $action = "create";
                $action_value = $value;
                $actions_issued++;
            }

            if ($key == "m" || $key == "--merge") {
                $action = "merge";
                $action_value = $value;
                $actions_issued++;
            }

            if ($key == "p" || $key == "--patch") {
                $action = "patch";
                $actions_issued++;
            }

            if ($key == "f" || $key == "--produce-patch-file") {
                $action = "producefile";
                $actions_issued++;
            }
        }
    }

    if ($actions_issued == 0) {
        echo "{$argv[0]} needs to be called with an action parameter.\n";
        echo "Help: {$argv[0]} --help\n";
        exit;
    }

    if ($actions_issued > 1) {
        echo "{$argv[0]} cannot be called with more than one action parameter.\n";
        echo "Help: {$argv[0]} --help\n";
        exit;
    }
    
    if ($action == 'help') {
        displayHelp();
        exit;
    } else if ($action == 'create-database-folders') {
        createDatabaseFolders($action_value);
        exit;   
    }

    // set up the printer
    $printer = new printer($printLevel);

    foreach ($singleDbConfigs as $config) {
        $printer->write('-------------------------------------------------');
        $printer->write('Working on database: ' . $config->dbName);
        $printer->write('-------------------------------------------------');
        $app = new Patch_Engine($config, $printer, DBPATCH_BASE_PATH);
        if ($action != "help") {
            $printer->write("Action: {$action}", 2);
            $printer->write("Action Value: {$action_value}", 2);
            $printer->write("Actions Issued: {$actions_issued}", 2);


            /*
              $printer->write("Base Path: {$basepath}");
              $printer->write("Schema Path: {$schemapath}");
              $printer->write("Data Path: {$datapath}");
             *
             */
        }

        // lets process any skip values we get
        if ($skip_value) {
            $skipList = explode(",", $skip_value);
            $app->skip_patches($skipList);
        }

        switch ($action) {
            case "patch":
                $app->apply_patches();

                // Process any values from --skip-and-record
                if ($record_value !== null) {
                    $recordList = explode(",", $record_value);
                    $app->record_patches($recordList);
                }
                break;

            case "create":
                $app->create_patch($action_value);
                break;

            case "list":
                $app->list_patches();
                break;

            case "add":
                $recordList = explode(",", $action_value);
                $app->add_patches($recordList);
                break;

            case "merge":
                $app->merge_patches($action_value);
                break;

            case "record":
                $recordList = explode(",", $action_value);
                $app->record_patches($recordList);
                break;

             case "producefile":
                $app->apply_patches(true);
                break;

            case "help":
            default:
                displayHelp();
                break 2; // exit the switch and the foreach
        }
    }
} catch (Exception $e) {
    echo "{$e->getMessage()}\n";
    echo "{$e->getTraceAsString()}\n";
    exit;
}

/**
 *   function displayHelp:  no input parms, displays help text.
 *
 */
function displayHelp() {
    global $version;

    $help = <<<EOHELP
Version: $version
Usage: ./patch_database [COMMAND] [COMMANDOPTS] [OPTIONS]


Where COMMAND is one of:

   -h or --help
      Show script usage information.

   -p or --patch
      Apply any patches that haven't already been applied to the database.

   -l or --list
      List which patches would be applied to the database if --patch was run.

   -c or --create=[schema/data/phpscript/shscript]
      Create a new patch/script

   -m or --merge=[schema/data]
      Merge current patches into new single [schema/data] file

   -aVERSION or --add=VERSION
      Apply one or more patches to the database, specified by VERSION. See below
      for the syntax of VERSION.

   -rVERSION or --record=VERSION
      Mark one or more patches as already having been added to the database, but
      don't actually apply the patches. See below for the syntax of VERSION.
       
    --create-database-folders=[dbname]   
      Create new folder data for the database


VERSION syntax:

   VERSION is a comma separated list of SQL patch filenames. For example, to
   specify the patches 1_trunk_person.sql and 2_trunk_person.sql, replace
   'VERSION' with '1_trunk_person.sql,2_trunk_person.sql' (note that there is
   no space after the comma).


COMMANDOPTS (command-specific options):

   For --patch, the following COMMANDOPTS are available:

      -sVERSION or --skip=VERSION
         Ignore the patches specified by VERSION.

      -SVERSION or --skip-and-record=VERSION
         Ignore the patches specified by VERSION, but mark them as having been
         added to the database. This prevents them from being added by future
         --patch runs.

   No other commands accept any options.


OPTIONS:

   -v or --verbose
      Show debug information while running.

   -q or --quiet
      Don't show any output unless specifically requested (e.g., via --list).

   -d or --dryrun
      Don't make any changes to the database.

EOHELP;

    $proc = proc_open('less', array(
        0 => array('pipe', 'r'),
        2 => array('pipe', 'w')
            ), $pipes);
    if (is_resource($proc)) {
        fwrite($pipes[0], $help);
        fclose($pipes[0]);
        $return = proc_close($proc);
        if ($return != 0) {
            echo $help;
        }
    } else {
        echo $help;
    }
}

function createDatabaseFolders($db_name) {
    $sql_dir = DBPATCH_BASE_PATH . DIRECTORY_SEPARATOR . 'sql';
    
    if (!file_exists($sql_dir) && !mkdir($sql_dir)) {
        die("Could not create '{$sql_dir}' folder"); 
    }
    
    $base_dir = DBPATCH_BASE_PATH . DIRECTORY_SEPARATOR . 'sql' . DIRECTORY_SEPARATOR . 'base';
    
    if (!file_exists($base_dir) && !mkdir($base_dir)) {
        die("Could not create '{$base_dir}' folder"); 
    }
    
    $schema_dir = DBPATCH_BASE_PATH . DIRECTORY_SEPARATOR . 'sql' . DIRECTORY_SEPARATOR . 'schema';
    
    if (!file_exists($schema_dir) && !mkdir($schema_dir)) {
        die("Could not create '{$schema_dir}' folder"); 
    }
    
    $data_dir = DBPATCH_BASE_PATH . DIRECTORY_SEPARATOR . 'sql' . DIRECTORY_SEPARATOR . 'data';
    
    if (!file_exists($data_dir) && !mkdir($data_dir)) {
        die("Could not create '{$data_dir}' folder"); 
    }
    
    echo 'Database folders created';
}
?>
