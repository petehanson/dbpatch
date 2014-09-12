<?php
require_once(dirname(__FILE__) . '/../database_drivers/DriverFactory.php');

/**
 *   @package PatchEngine.php
 *
 * is the guts of the versioning app.
 *
 */

/**
 *  @package PatchEngine.php
 * Class to expose generic versioning
 *  logic, independent
 *  of any particular store (dbms - file)
 *
 */
class Patch_Engine {
    /*     * #@+
     * @access private
     *
     */

    protected $printer;
    protected $db;
    protected $dbName;
    protected $fileVersionTracker;
    protected $base_folder;
    protected $basepath;
    protected $basefile;
    protected $schemapath;
    protected $datapath;
    protected $scriptpath;
    protected $dbType;
    //protected $dryRun;
    protected $versionsToProcess;
    protected $skip_patches;
    protected $versionsToRecord;
    protected $commentCharacters;
    protected $dbTrackPatchesInFile;
    protected $dbStorePatchesInFile;
    protected $bundler;
    protected $root_level_commands;
    protected $prompt_for_root_user;

    /*     * #@- */

    /**
     *
     * constructor function
     * @param string $patchFile
     * @param DbPatch_Config_SingleDb $config
     * @param printerbase $printer
     * @todo Consider changing to take in the xml at this point to
     * separate logic from interface, file processing to command line
     * interface.
     *
     */
    public function __construct(configuration $configuration, printerbase $printer, $base_folder, $suppressDbCreation = false) {
        $this->printer = $printer;

        $this->base_folder = realpath($base_folder);
        $this->basepath = realpath($base_folder . "/" . $config->basepath);
        $this->basefile = $config->basefile;
        $this->dbTrackPatchesInFile = $config->dbTrackPatchesInFile;
        $this->schemapath = realpath($base_folder . "/" . $config->schemapath);
        $this->datapath = realpath($base_folder . "/" . $config->datapath);
        $this->scriptpath = realpath($base_folder . "/" . $config->scriptpath);

        $this->versionsToProcess = null;
        $this->skip_patches = array();
        $this->versionsToRecord = null;
        $this->dbName = $config->dbName;
        $this->root_level_commands = $config->root_level_commands;
        $this->prompt_for_root_user = $config->prompt_for_root_user;
        $this->dbType = $config->dbType;

        $this->bundler = new Patch_File_Bundler($this->dbName, $this->base_folder);

        $baseschema = realpath($this->basepath . "/" . $this->basefile);

        $this->db = Driver_Factory::Create($this->dbType, $config->dbHost, $config->dbName, $config->dbUsername,
                $config->dbPassword, $printer, $baseschema, $suppressDbCreation);

        $this->db->checkForDBVersion();

        $appliedDbPatchItems = $this->db->get_applied_patch_items();

        $this->fileVersionTracker = File_Tracker_Factory::Create(
                        $config->dbName, $printer, $this->base_folder, $this->dbTrackPatchesInFile ? $appliedDbPatchItems : array());

        if (!$this->dbTrackPatchesInFile && count($appliedDbPatchItems) == 0) {
            $appliedPatchesFromFile = $this->fileVersionTracker->get_applied_patches();

            foreach ($appliedPatchesFromFile as $itemFromFile) {
                $this->db->insertTrackingItem($itemFromFile);
            }
        }
        date_default_timezone_set($config->standardized_timezone);
        $this->commentCharacters = array('#', '-', '/');
    }

    /**
     *  destruct function - closes DB connection
     *
     */
    public function __destruct() {
        if ($this->db)
            $this->db->close();
    }

    /**
     * needed_patches function:
     * Get a list of the patches that need to be applied to the database (taking into account skipped/added
     * patches). Returns an array('data'=>array(...), 'schema'=>array(...)), where data contains data patches
     * and schema contains schema patches.
     */
    protected function needed_patches() {
        // get list of applied patches from appropriate store
        if ($this->dbTrackPatchesInFile) {
            $applied_patches = $this->fileVersionTracker->get_applied_patch_names();
        } else {
            $applied_patches = $this->db->get_applied_patch_names();
        }

        // get list of patches on the file system
        $schema_patches = $this->get_patch_files($this->schemapath);
        $data_patches = $this->get_patch_files($this->datapath);
        $script_patches = $this->get_patch_files($this->scriptpath);

        // determine outstanding patches
        $needed_schema_patches = array_diff($schema_patches, $applied_patches);
        $needed_data_patches = array_diff($data_patches, $applied_patches);
        $needed_script_patches = array_diff($script_patches, $applied_patches);

        // filter out any specified in skip
        $needed_schema_patches = array_diff($needed_schema_patches, $this->skip_patches);
        $needed_data_patches = array_diff($needed_data_patches, $this->skip_patches);
        $needed_script_patches = array_diff($needed_script_patches, $this->skip_patches);

        return array(
            'data' => $needed_data_patches,
            'schema' => $needed_schema_patches,
            'script' => $needed_script_patches
        );
    }

    /**
     *  list_patches function: prints what patches need to be applied to the current working copy database.
     *  Optionally takes a parameter $needed_patches, an array in the format output by $this->needed_patches()
     *
     */
    public function list_patches($needed_patches = null) {
        // Get a list of the patches that need to be applied
        if ($needed_patches === null) {
            $needed_patches = $this->needed_patches();
            if ($needed_patches === false) {
                return false;
            }
        }
        $needed_data_patches = $needed_patches['data'];
        $needed_schema_patches = $needed_patches['schema'];
        $needed_script_patches = $needed_patches['script'];

        // Say which patches have been marked to be skipped
        if (count($this->skip_patches) > 0) {
            $this->printer->write("");
            $this->printer->write("Schema patches that will be skipped:");
            foreach ($this->skip_patches as $patch) {
                $this->printer->write("\t" . $patch);
            }
        }

        // Say which schema patches will be applied
        $this->printer->write("");
        if (!empty($needed_schema_patches)) {
            $this->printer->write("Schema patches that will be applied:");
            sort($needed_schema_patches);
            foreach ($needed_schema_patches as $patch) {
                $this->printer->write("\t" . $patch);
            }
        } else {
            $this->printer->write("No schema patches to be applied were found");
        }


        // Say which data patches will be applied
        $this->printer->write("");
        if (!empty($needed_data_patches)) {
            $this->printer->write("Data patches that will be applied:");
            sort($needed_data_patches);
            foreach ($needed_data_patches as $patch) {
                $this->printer->write("\t" . $patch);
            }
        } else {
            $this->printer->write("No data patches to be applied were found");
        }

        // Say which script patches will be applied
        $this->printer->write("");
        if (!empty($needed_script_patches)) {
            $this->printer->write("Script patches that will be applied:");
            foreach ($needed_script_patches as $patch) {
                $this->printer->write("\t" . $patch);
            }
        } else {
            $this->printer->write("No script patches to be applied were found");
        }

        return true;
    }

    /**
     *   apply_patches function:  executes the patching process
     *
     */
    public function apply_patches($bundleInFile = false) {
        $return_result = true;

        // Get a list of the patches that need to be applied
        $needed_patches = $this->needed_patches();
        if ($needed_patches === false) {
            return false;
        }
        $needed_data_patches = $needed_patches['data'];
        $needed_schema_patches = $needed_patches['schema'];
        $needed_script_patches = $needed_patches['script'];

        // sort patches into correct order by timestamp prefix (filename)
        sort($needed_schema_patches);
        sort($needed_data_patches);
        sort($needed_script_patches);

        // Print out what patches will be applied/skipped
        $this->list_patches($needed_patches);


        if (empty($needed_schema_patches) && empty($needed_data_patches) && empty($needed_script_patches)) {
            $this->printer->write("");
            $this->printer->write("No patches were applied.");
        } else {
            $this->printer->write("");
            $inorder = array_merge($needed_schema_patches, $needed_data_patches, $needed_script_patches);
            sort($inorder);
            $this->add_patches($inorder);
            //$this->add_patches(array_merge($needed_schema_patches, $needed_data_patches), $bundleInFile);

            /* $this->printer->write("Applying patches:");
              // on each patch, apply it to the DB
              foreach (array("schemapath" => $needed_schema_patches, "datapath" => $needed_data_patches) as $pathname => $needed_patches) {

              foreach ($needed_patches as $patch) {
              // record the patch data into dbversion
              $fullpath = realpath($this->$pathname . "/" . $patch);

              $sql = $this->get_queries_from_file($fullpath);
              $this->db->execute($sql);
              if ($this->db->has_error()) {
              $this->printer->write("Error: {$fullpath}");
              $return_result = false;
              break;
              } else {
              $this->printer->write("Success: {$fullpath}");
              // save patch to dbversion
              $this->record_patches($patch);
              }
              }
              } */
        }


        return $return_result;
    }

    public function merge_patches($patch_type, $unlinkAfter = true)
    {
        $this->printer->write('');
        $this->printer->write('Starting merge in `' . $this->dbName . '`');

        switch ($patch_type) {
            case "schema":
                $path = $this->schemapath;
                break;
            case "data":
                $path = $this->datapath;
                break;
            case "script":
                throw new exception("Cannot merge scripts.");
                break;
            default:
                throw new exception("An invalid patch type was provided.");
        }

        // get list of patches on the file system
        // before we do anything
        $schema_patches = $this->get_patch_files($this->schemapath);
        $data_patches = $this->get_patch_files($this->datapath);

        // now sort patches into correct order by timestamp prefix (filename)
        sort($schema_patches);
        sort($data_patches);

        // merge into full chronological order
        $inorder = array_merge($schema_patches, $data_patches);
        sort($inorder);

        if (empty($inorder)) {
            $this->printer->write('No patches to merge in `' . $this->dbName . '`, skipping.');
            return;
        }


        $timestamp_prefix = date("Ymd_His");
        $this->printer->write("Setting patch prefix: {$timestamp_prefix}", 2);
        $answer = $this->printer->ask("Provide a description for merged patch to `" . $this->dbName . "`");

        // normalize answer string, only using alphanum
        $normalized_answer = preg_replace("/\s/", "_", $answer);
        $normalized_answer = preg_replace("/[^\w]/", "", $normalized_answer);
        if (empty($answer)) {
        	$this->printer->write("  Skipping.");
            return;
        }
        // set the file name
        $patch_file_name = "{$timestamp_prefix}_{$normalized_answer}.sql";

        $this->printer->write("Patch file name: {$patch_file_name}", 2);

        $fullpath = $path . "/" . $patch_file_name;

        if (file_exists($fullpath)) {
            throw new exception("destination file {$fullpath} already exists");
        }

        // lets create the file
        if (!touch($fullpath)) {
            throw new exception("Unable to create the file {$fullpath}");
        }

        $this->printer->write("Patch file created; {$fullpath}");


        $paths = array();
        $types = array();
        foreach($inorder as $patch_file)
        {
            if (file_exists($this->schemapath . '/' . $patch_file)) {
                if (file_exists($this->datapath . '/' . $patch_file)) {
                    // file exists in both folders. Abort operation
                    $this->printer->write("Aborting process: File {$patch_file} exists in both {$this->schemapath} and {$this->datapath} folders.");
                    die;
                }
                $paths[$patch_file] = $this->schemapath . '/' . $patch_file;
                $types[$patch_file] = 'schema';
            } elseif (file_exists($this->datapath . '/' . $patch_file)) {
                $paths[$patch_file] = $this->datapath . '/' . $patch_file;
                $types[$patch_file] = 'data';
            } else {
                $this->printer->write("Aborting process: File {$patch_file} not found");
                die;
            }

            $this->printer->write('Merging patch: ' . $patch_file);

            // append each patch into dest file
            $contents = file_get_contents($paths[$patch_file]);
            if ($contents === false) {
                throw new exception('Unable to read patch file: ' . $paths[$patch_file]);
            }

            // add a comment header identifying the source patch
            $header = PHP_EOL;
            $header .= '-- ' . PHP_EOL;
            $header .= '-- Originally from ' . $types[$patch_file] . ': ' . $patch_file . PHP_EOL;
            $header .= '-- ' . PHP_EOL;
            $header .= PHP_EOL;
            // make sure we're starting with appropriate delimiter in case previous file had a different one
            $header .= 'DELIMITER ;' . PHP_EOL;
            $header .= PHP_EOL;

            // write the header
            $written = file_put_contents($fullpath, $header, FILE_APPEND);
            // if successful add the contents
            if ($written) $written = file_put_contents($fullpath, $contents, FILE_APPEND);
            // free mem
            unset($contents);

            // stop on error
            if (!$written) {
                throw new exception('Unable to append patch file: ' . $patch_file );
            }
        }

        // delete if requested
        if ($unlinkAfter) {
            foreach($inorder as $patch_file)
            {
                $this->printer->write('Deleting patch: ' . $patch_file);
                @unlink($paths[$patch_file]);
            }
        }

        $this->printer->write('Done merging in `' . $this->dbName . '`');
        return true;
    }

    protected function get_patch_files($path) {
        $files = array();
        if (!is_string($path)) return array();
        else if (!is_dir($path)) die($path . ' does not exist');
        $dir = new DirectoryIterator($path);
        foreach ($dir as $item) {
            if (!$item->isDot() && preg_match("/^\d{8}_\d{6}/", $item->getFilename())) {
                $files[] = $item->getFilename();
            }
        }

        return $files;
    }

    /**
     *  function record_patches: just records the patch data into the dbverions table
     *
     */
    public function record_patches($versionIDs) {
        if (!is_array($versionIDs))
            $versionIDs = array($versionIDs);
        foreach ($versionIDs as $version) {
            $this->insertVersion($version);
            $this->printer->write("Inserting Version ID: " . (string) $version, 1);
        }

        /*
          if ($this->db->doesTransactions()) {
          if ($this->dryRun === true)
          $this->db->failTransaction();
          return $this->db->completeTransaction();
          }
          else {
          return true;
          }
         *
         */

        return true;
    }

    /**
     *  function add_patches:  Applies specific patches to the database
     *
     */
    public function add_patches($patches, $bundleInFile = false) {
        $paths = array();
        // get list of applied patches from appropriate store
        if ($this->dbTrackPatchesInFile) {
            $applied_patches = $this->fileVersionTracker->get_applied_patch_names();
        } else {
            $applied_patches = $this->db->get_applied_patch_names();
        }
        foreach ($patches as $patch) {
            //echo $patch."\n";
            if (in_array($patch, $applied_patches)) {
                $this->printer->write("Patch {$patch} is already applied and will be skipped.\n");
                continue;
            }
            $schemaExists = file_exists($this->schemapath . '/' . $patch);
            $dataExists = file_exists($this->datapath . '/' . $patch);
            $scriptExists = file_exists($this->scriptpath . '/' . $patch);
            $patchCount = ($schemaExists ? 1 : 0) + ($dataExists ? 1 : 0) + ($scriptExists ? 1 : 0);
            if ($patchCount > 1) {
                // file exists in more than one folder. Abort operation
                $msg = "Aborting process: File {$patch} exists in ";
                $conj = false;
                if ($schemaExists) {
                    $msg .= ' ' . $this->schemapath;
                    $conj = true;
                }
                if ($dataExists) {
                    if ($conj) $msg .= ' and ';
                    $msg .= ' ' . $this->datapath;
                    $conj = true;
                }
                if ($scriptExists) {
                    if ($conj) $msg .= ' and ';
                    $msg .= ' ' . $this->scriptpath;
                    $conj = true;
                }
                $msg .= ' folders.';
                $this->printer->write( $msg );
                die;
            } else if ($schemaExists) {
                $paths[$patch] = $this->schemapath . '/' . $patch;
                //echo "\nsaving {$patch} in " . __LINE__."\n";
            } elseif ($dataExists) {
                $paths[$patch] = $this->datapath . '/' . $patch;
                //echo "\nsaving {$patch} in " . __LINE__."\n";
            } elseif ($scriptExists) {
                $paths[$patch] = $this->scriptpath . '/' . $patch;
                //echo "\nsaving {$patch} in " . __LINE__."\n";
            } else {
                $this->printer->write("Aborting process: File {$patch} not found");
                die;
            }
        }
        if (!empty($paths)) {
            if ($bundleInFile) {
                $succeeded = $this->bundler->bundleFilesToDefaultPatchFile($paths);
                if ($succeeded) {
                    foreach ($paths as $file => $p) {
                        $this->record_patches($file);
                    }

                    $this->printer->write("\nPatches were bundled successfully to .sql files\n");
                }
            }
            else
                $this->executeFiles($paths);
        } else {
            $this->printer->write("No patch has been applied, all listed patches are either already applied or not found.");
        }
    }


    public function create_patch_folders($db_config_name) {

    }


    /*
     * Execute files using appropriate DB Driver
     */

    private function executeFiles($filePaths) {
        foreach ($filePaths as $file => $path) {
            if ($this->prompt_for_root_user) {
                $this->check_file_for_root_statements($path);
            }

            $retval = null;
            $scpathlen = strlen($this->scriptpath . '/');
            if (substr($path, 0, $scpathlen) == ($this->scriptpath . '/' ) && strtolower(substr($path, -4)) != '.sql') {
                // script
                $retval = $this->executeScript($path);
            } else {
                $this->db->executeFile($path);
                $retval = !$this->db->has_error();
            }

            if (!$retval) {
                $this->printer->write("Error: {$path}");
                break;
            } else {
                $this->printer->write("Success: {$path}");
                // save patch to dbversion
                $this->record_patches($file);
            }
        }
    }

    /**
     * Check .SQL file for root required statements and ask for mysql root credentials
     * if needed.
     */
    private function check_file_for_root_statements($filePath) {
        // skip sh & php
        $pInfo = pathinfo($filePath);
        if (!is_array($pInfo)) return null;
        if ((strtolower($pInfo['extension']) == 'php') || (strtolower($pInfo['extension']) == 'sh'))  return false;

        $fileContents = file_get_contents($filePath);

        if ($fileContents) {
            // verify if any of the root level commands (e.g. GRANT) is contained in the file
            foreach ($this->root_level_commands as $root_level_command) {
                if (preg_match("/" . $root_level_command . "/i", $fileContents)) {

                    $hasPrivilege = $this->db->userHasPrivilege($root_level_command);

                    // If current user doesn't have the privilege required ask for a root user
                    if (isset($hasPrivilege) && !$hasPrivilege) {
                        $this->printer->write("\nDetected root level statements in patch files. Please enter a MySQL root user credentials:\n");
                        $username = $this->printer->askWithRetriesIfEmpty("Username: ", 2);
                        $password = $this->printer->askWithRetriesIfEmpty("Password: ", 2);

                        $this->db->change_user($username, $password);

                        break;
                    }
                }
            }
        }
    }

    /**
     *  function skip_patches:  patches that should be skipped when patching
     *
     */
    public function skip_patches($patches) {

        // set up patches as an array if it isn't one
        if (!is_array($patches))
            $patches = array($patches);

        // loop through each skip patch
        $temp_list = array();
        foreach ($patches as $patch) {
            // determine if it has a path in the patch specification
            $slash_position = strrpos($patch, "/");
            // if we do, we process out the path, getting just the filename
            if ($slash_position !== false) {
                $length = strlen($patch) - $slash_position;
                $temp_list[] = substr($patch, $slash_position + 1, $length);
            } else {
                // otherwise we treat it just as a patch name
                $temp_list[] = $patch;
            }
        }

        // get rid of duplicates
        $patches = array_unique($temp_list);

        $this->skip_patches = array_merge($this->skip_patches, $patches);
    }

    /**
     * Sets up a new patch file in the appropriate directory, based on patch type
     *
     * @param string $patch_type
     */
    public function create_patch($patch_type) {

        $extension = 'sql';
        switch ($patch_type) {
            case "schema":
                $path = $this->schemapath;
                break;
            case "data":
                $path = $this->datapath;
                break;
            case "phpscript":
                $path = $this->scriptpath;
                $extension = 'php';
                break;
            case "shscript":
                $path = $this->scriptpath;
                $extension = 'sh';
                break;
            default:
                throw new exception("An invalid patch type was provided.");
        }

        $timestamp_prefix = date("Ymd_His");

        $this->printer->write("Setting patch prefix: {$timestamp_prefix}", 2);

        $answer = $this->printer->ask("Provide a description for new patch to `" . $this->dbName . "`");

        // normalize answer string, only using alphanum
        $normalized_answer = preg_replace("/\s/", "_", $answer);
        $normalized_answer = preg_replace("/[^\w]/", "", $normalized_answer);
	if (empty($answer)) {
        	$this->printer->write("  Skipping.");
		return;
	}

        // set the file name
        $patch_file_name = "{$timestamp_prefix}_{$normalized_answer}." . $extension;

        $this->printer->write("Patch file name: {$patch_file_name}", 2);

        // lets create the file
        $fullpath = $path . "/" . $patch_file_name;
        if (!touch($fullpath)) {
            throw new exception("Unable to create the file {$fullpath}");
        }

        $this->printer->write("Patch file created; {$fullpath}");
    }

    /**
     *  function insertVersion: assuming all is ok up to now, attempt to
     *  actually insert the version info into dbversion store (file or sql).
     *  @return boolean yea or nay on insert of version.
     *
     */
    protected function insertVersion($version) {
        // Item to be stored in the version store (file or SQL)
        $trackingItem = array("item" =>
            array("applied_patch" => $version,
                "date_patch_applied" => date('Y-m-d')));

        if ($this->dbTrackPatchesInFile)
            return $this->fileVersionTracker->insert_new_version($trackingItem);
        else
            return $this->db->insertTrackingItem($trackingItem);
    }

    /**
     * Extracts the queries from a given patch file
     * @param $filepath - The full path of the patch file
     * @return Array
     */
    protected function get_queries_from_file($filepath) {
        return file_get_contents($filepath);
    }

    public function getDb()
	{
		return $this->db;
	}

    public function executeScript($filePath)
    {
        $retval = null;

        // skip sh & php
        $pInfo = pathinfo($filePath);
        if (!is_array($pInfo)) return null;
        if (strtolower($pInfo['extension']) == 'php') {
            $this->printer->write('Executing PHP script: ' . $filePath);
            $start = microtime(true);
            $php = (defined('PHP_BINARY') ? constant('PHP_BINARY') : (PHP_BINDIR . DIRECTORY_SEPARATOR . 'php'));
            $tmpfile = tempnam('', basename($filePath));
            $rvar = null;
            system($php . ' ' . $filePath . ' > "' . $tmpfile . '" 2>&1', $rvar);
            $retval = ($rvar == 0);
            $end = microtime(true);
            $this->printer->write('Execution completed in ' . sprintf('%.5f', $end - $start) . ' seconds');
            $this->printer->write('Output in: ' . $tmpfile);

        } else if (strtolower($pInfo['extension']) == 'sh') {
            $this->printer->write('Executing SH script: ' . $filePath);
            $start = microtime(true);
            $rvar = null;
            $tmpfile = tempnam('', basename($filePath));
            system('/bin/bash ' . $filePath . ' > "' . $tmpfile . '" 2>&1', $rvar);
            $retval = ($rvar == 0);
            $end = microtime(true);
            $this->printer->write('Execution completed in ' . sprintf('%.5f', $end - $start) . ' seconds');
            $this->printer->write('Output in: ' . $tmpfile);

        } else {
            $retval = false;

        }

        return $retval;
    }
}
