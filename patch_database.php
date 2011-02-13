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
 *  @package patch_database.php 
 *  @author Pete Hanson  phanson@uarass.com
 *   @copyright Pete Hanson  2008
 *
 */

/**
 * variable $version documents the implementation level.
 *
 */

$version = "0.20";

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

try
{

    $opts = Console_Getopt::getOpt($argv,"hqvdla::s::r::",
	array("help","list","dryrun","verbose","quiet","add=",
	"skip=","record="));

    //print_r($opts);

    if (PEAR::isError($opts))
    {
	fwrite(STDERR,$opts->getMessage()."\n");
	fwrite(STDERR,"Run for help: {$argv[0]} --help\n");
	exit(INVALID_OPTION);
    } 

    $printLevel = 1;
    $dryRun = false;
    $runList = false;

    $addText = null;
    $skipText = null;
    $recordText = null;

    if (is_array($opts[0]))
    {
	foreach ($opts[0] as $argParameter)
	{
	    $key = $argParameter[0]; 
	    $value = $argParameter[1];

	    if ($key == "h" || $key == "--help")
	    {
		displayHelp();
		exit();
	    }
	    
	    if ($key == "v" || $key == "--verbose") $printLevel = 2;

	    if ($key == "q" || $key == "--quiet") $printLevel = 0;

	    if ($key == "d" || $key == "--dryrun") $dryRun = true;
	    
	    if ($key == "l" || $key == "--list") $runList = true;

	    if ($key == "a" || $key == "--add") $addText = $value;

	    if ($key == "s" || $key == "--skip") $skipText = $value;

	    if ($key == "r" || $key == "--record") $recordText = $value;
	    
	}	
    }

    if (!$opts[1][0])
    {
	echo "{$argv[0]} needs 1 argument\n";
	echo "Usage: {$argv[0]} <path to patch file>\n";
	echo "Help: {$argv[0]} --help\n";
	exit;
    }
    
    $patchFile = $opts[1][0];


    $printer = new printer($printLevel);
    $config = new config(); 
    $app = new dbversion($patchFile,$config,$printer);

    if ($dryRun)
    {
	$app->dryRun();
    }
    else
    {
	$app->liveRun();
    }

    if ($addText)
    {
	$addList = explode(",",$addText); 
	$app->addVersions($addList);
    }

    if ($skipText)
    {
	$skipList = explode(",",$skipText); 
	$app->skipVersions($skipList);
    }

	if ($recordText)
	{
		$recordList = explode(",",$recordText); 
		$app->recordVersions($recordList);
	}

    if ($runList === true)
    {
	$app->listVersions();
    }
    else
    {
	$app->execute();
    }

}
catch (Exception $e)
{
    echo "{$e->getMessage()}\n";
    echo "{$e->getTraceAsString()}\n";
    exit;
}


/**        
 *   function displayHelp:  no input parms, displays help text.
 *
 */

function displayHelp()

{
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
