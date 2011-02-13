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
$version = "0.30";

/**
 * Set of files required to run the application
 *
 */
// sets the working directory to the folder the script runs from

require_once("Console/Getopt.php");

require_once("config.php");
require_once("app/dbversion.php");

require_once("database_drivers/" . config::$dbClassFile);
require_once("printers/cli.php");

/**
 * pathToScript is the execution directory
 *
 */
$pathToScript = dirname($_SERVER['SCRIPT_FILENAME']);

chdir($pathToScript);

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
	$opts = Console_Getopt::getOpt($argv, "hqvlpa::s::r::c::",
			array("help", "list", "verbose", "quiet", "add=",
			    "skip=", "record=", "create=", "patch"));

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

	if (is_array($opts[0])) {
		foreach ($opts[0] as $argParameter) {
			$key = $argParameter[0];
			$value = $argParameter[1];

			if ($key == "h" || $key == "--help") {
				$action = "help";
				$actions_issued++;
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

			if ($key == "p" || $key == "--patch") {
				$action = "patch";
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




	// set up the printer
	$printer = new printer($printLevel);


	// construct file paths
	//$basepath = dirname(__FILE__) . "/" . config::$basepath;
	//$schemapath = dirname(__FILE__) . "/" . config::$schemapath;
	//$datapath = dirname(__FILE__) . "/" . config::$datapath;
	$base_folder = dirname(__FILE__);


	$config = new config();
	$app = new dbversion($config, $printer,$base_folder);



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


	switch ($action) {
		case "patch":

			// lets process any skip values we get, as they only apply to patching
			if ($skip_value) {
				$skipList = explode(",", $skip_value);
				$app->skip_patches($skipList);
			}

			$app->apply_patches();

			break;

		case "create":
			$app->create_patch($action_value);
			break;

		case "list":
			break;

		case "add":
			break;

		case "record":
			break;

		case "help":
		default:
			displayHelp();
			break;
	}

	die();


	if ($addText) {
		$addList = explode(",", $addText);
		$app->addVersions($addList);
	}

	if ($skipText) {
		$skipList = explode(",", $skipText);
		$app->skipVersions($skipList);
	}

	if ($recordText) {
		$recordList = explode(",", $recordText);
		$app->recordVersions($recordList);
	}

	if ($runList === true) {
		$app->listVersions();
	} else {
		$app->execute();
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

	echo "Help on the database patching script. Version: {$version}\n";
	echo "Usage: patch_database.php [options] schema_patch_file.xml\n";
	echo "\n";
	echo "Available Options:\n";
	echo "This help screen:\t\t-h or --help\n";
	echo "Verbose Output:\t\t\t-v or --verbose\n";
	echo "No Output:\t\t\t-q or --quiet\n";
	echo "Do a dry run:\t\t\t-d or --dryrun\n";
	echo "List the patch versions:\t-l or --list\n";
	echo "Use certain version IDs:\t-a or --add=versionID\n";
	echo "Skip certain version IDs:\t-s or --skip=versionID\n";
	echo "Record certain version IDs:\t-r or --record=versionID\n";
	echo "\n";
	echo "You can use the --add parameter to only perform tasks ";
	echo "for the specified list of version IDs. You can specify ";
	echo "multiple version IDs by using a comma seperated list. ";
	echo "Just make sure there are no spaces between the commas ";
	echo "and values.\n";
	echo "Example: --add=1_trunk_person,2_trunk_person  or  ";
	echo "-a1_trunk_person,2_trunk_person\n";
	echo "\n";
	echo "You can use the --skip parameter to skip the specified ";
	echo "list of version IDs. You can specify multiple version IDs ";
	echo "by using a comma seperated list. Just make sure there ";
	echo "are no spaces between the commas and values.\n";
	echo "Example: --skip=1_trunk_person,2_trunk_person  or  ";
	echo "-s1_trunk_person,2_trunk_person\n";
	echo "\n";
	echo "You can use the --record parameter to record the ";
	echo "specified list of version IDs. This allows you to ";
	echo "mark a version as performed permanently in your db ";
	echo "instance. The IDs specified to be recorded can be ";
	echo "affected by the IDs specified in the --add and --skip ";
	echo "switches. You can specify multiple version IDs by using ";
	echo "a comma seperated list. Just make sure there are no ";
	echo "spaces between the commas and values.\n";
	echo "Example: --record=1_trunk_person,2_trunk_person  or  ";
	echo "-r1_trunk_person,2_trunk_person\n";
	echo "\n";
}
?>
