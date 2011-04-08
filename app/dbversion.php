<?php

/**
 *   @package dbversion.php 
 * 
 * is the guts of the db versioning app.
 *
 */

/**
 *  @package dbversion.php  
 * Class to expose generic database versioning
 *  logic, independent
 *  of any particular dbms.
 * 
 *  Will test for and create a table in the current database called
 *  dbversion, storing information on executed versions.
 *
 */
class dbversion {
	/*	 * #@+
	 * @access private
	 *
	 */

	protected $printer;
	protected $db;
	protected $base_folder;
	protected $basepath;
	protected $basefile;
	protected $schemapath;
	protected $datapath;
	//protected $dryRun;
	protected $versionsToProcess;
	protected $skip_patches;
	protected $versionsToRecord;
	protected $commentCharcters;
	/*	 * #@- */

	/**
	 *
	 * constructor function
	 * @param string $patchFile
	 * @param config $config
	 * @param printerbase $printer
	 * @todo Consider changing to take in the xml at this point to
	 * separate logic from interface, file processing to command line
	 * interface.
	 *
	 */
	public function __construct(config $config, printerbase $printer, $base_folder) {
		$this->printer = $printer;

		$this->base_folder = realpath($base_folder);
		$this->basepath = realpath($base_folder . "/" . config::$basepath);
		$this->basefile = config::$basefile;
		$this->schemapath = realpath($base_folder . "/" . config::$schemapath);
		$this->datapath = realpath($base_folder . "/" . config::$datapath);
		/*
		  $printer->write("Base Path: {$this->basepath}");
		  $printer->write("Schema Path: {$this->schemapath}");
		  $printer->write("Data Path: {$this->datapath}");
		 */

		$this->versionsToProcess = null;
		$this->skip_patches = array();
		$this->versionsToRecord = null;

		$this->db = new database(config::$dbHost, config::$dbName, config::$dbUsername, config::$dbPassword, $printer);
		$this->db->checkForDBVersion();

		date_default_timezone_set(config::$standardized_timezone);
		$this->commentCharcters = array('#', '-', '/');
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
	 *  list_patches function: determines what patches need to be applied to the current working copy database
	 *
	 */
	public function list_versions() {

	}

	/**
	 *   apply_patches function:  executes the patching process
	 *
	 */
	public function apply_patches() {
		if (!$this->applyBaseSchema())
			return false;
		
		$return_result = true;

		// get list of applied patches from db
		$applied_patches = $this->db->get_applied_patches();
		if (!empty($applied_patches)) {
			$this->printer->write("");
			$this->printer->write("Patches already applied and will be skipped:");
			print_r($applied_patches);
		}

		// get list of patches on the file system
		$schema_patches = $this->get_patch_files($this->schemapath);
		$data_patches = $this->get_patch_files($this->datapath);

		// determine outstanding patches
		$needed_schema_patches = array_diff($schema_patches, $applied_patches);
		$needed_data_patches = array_diff($data_patches, $applied_patches);

		// filter out any specified in skip
		$needed_schema_patches = array_diff($needed_schema_patches, $this->skip_patches);
		$needed_data_patches = array_diff($needed_data_patches, $this->skip_patches);

		if (count($this->skip_patches) > 0) {
			$this->printer->write("");
			$this->printer->write("Schema patches that will be skipped:");
			foreach ($this->skip_patches as $patch) {
				$this->printer->write("\t" . $patch);
			}
		}


		$this->printer->write("");
		if (!empty($needed_schema_patches)) {
			$this->printer->write("Schema patches that will be applied:");
			foreach ($needed_schema_patches as $patch) {
				$this->printer->write("\t" . $patch);
			}
		} else {
			$this->printer->write("No schema patches to be applied were found");
		}
		
			
		$this->printer->write("");
		if (!empty($needed_data_patches)) {
			$this->printer->write("Data patches that will be applied:");
			foreach ($needed_data_patches as $patch) {
				$this->printer->write("\t" . $patch);
			}
		} else {
			$this->printer->write("No data patches to be applied were found");
		}

		// sort patches into correct order by timestamp prefix (filename)
		sort($needed_schema_patches);
		sort($needed_data_patches);



		if (empty($needed_schema_patches) && empty($needed_data_patches)) {
			$this->printer->write("");
			$this->printer->write("No patches were applied.");
		} else {
			$this->printer->write("");
			$this->printer->write("Applying patches:");
			// on each patch, apply it to the DB
			foreach (array("schemapath" => $needed_schema_patches, "datapath" => $needed_data_patches) as $pathname => $needed_patches) {
	
				foreach ($needed_patches as $patch) {
					// record the patch data into dbversion
					$fullpath = realpath($this->$pathname . "/" . $patch);
	
					$queries = $this->getFileQueries($fullpath);
					foreach ($queries as $sql) {
						$sql = trim($sql);
						if (in_array($sql[0], $this->commentCharcters) || empty($sql))
							continue;
						$result = $this->db->execute($sql);
					}
					if ($this->db->has_error()) {
						$this->printer->write("Error: {$fullpath}");
						$return_result = false;
						break;
					} else {
						$this->printer->write("Success: {$fullpath}");
						// save patch to dbversion
						//$this->record_patches($patch);
					}
				}
			}
		}


		return $return_result;
	}
	
	/**
	 * function applyBaseSchema: Apllies the base schema to the database when necessary(Creates the table structure)
	 */
	public function applyBaseSchema () {
		if ($this->db->isNewDB()) {
			$fullpath = realpath($this->basepath . "/" . $this->basefile);
			$output = system("mysql -h " . $this->db->getHost() . " -u " . $this->db->getUser() . " -p" . $this->db->getPassword() . " " . $this->db->getDBName() . " < \"{$fullpath}\"");
			if ($output) {
				$this->printer->write("Error: Could not create tables structure using {$fullpath}");
				return false;
			} else {
				$this->printer->write("Success: Created tables structure using {$fullpath}");
				return true;
			} 
		}
		return true;
	}
	protected function get_patch_files($path) {
		$files = array();
		$dir = new DirectoryIterator($path);
		foreach ($dir as $item) {
			if (!$item->isDot() && preg_match("/^\d{8}_\d{6}/",$item->getFilename())) {
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

		if ($this->db->doesTransactions()) {
			if ($this->dryRun === true)
				$this->db->failTransaction();
			return $this->db->completeTransaction();
		}
		else {
			return true;
		}
	}

	/**
	 *  function add_patches:  Applies specific patches to the database
	 *
	 */
	public function add_patches($patches) {
		$paths = array();
		$applied_patches = $this->db->get_applied_patches();
		foreach($patches as $patch) {
			//echo $patch."\n";
			if (in_array($patch, $applied_patches)) {
				$this->printer->write("Patch {$patch} is already applied and will be skipped.\n");
				continue;
			}
			if (file_exists($this->schemapath . '/' . $patch)) {
				if (file_exists($this->datapath . '/' . $patch)) {
					// file exists in both folders. Abort operation
					$this->printer->write("Aborting process: File {$patch} exists in both {$this->schemapath} and {$this->datapath} folders.");
					die;
				}
				$paths[$patch] = $this->schemapath . '/' . $patch;
				//echo "\nsaving {$patch} in " . __LINE__."\n";
			} elseif (file_exists($this->datapath . '/' . $patch)) {
				$paths[$patch] = $this->datapath . '/' . $patch;
				//echo "\nsaving {$patch} in " . __LINE__."\n";
			} else {
				$this->printer->write("Aborting process: File {$patch} not found");
				die;
			}
		}
		if (!empty($paths)) {
			foreach ($paths as $file => $p) {
				$queries = $this->getFileQueries($p);
				
				foreach ($queries as $sql) {
					$sql = trim($sql);
					if (in_array($sql[0], $this->commentCharcters) || empty($sql))
						continue;
					//echo $sql;	
					$result = $this->db->execute($sql);
				}
				if ($this->db->has_error()) {
					$this->printer->write("Error: {$p}");
					$return_result = false;
					break;
				} else {
					$this->printer->write("Success: {$p}");
					// save patch to dbversion
					$this->record_patches($file);
				}
			}
		} else {
			$this->printer->write("No patch has been applied, all listed patches are either already applied or not found.");
		}

		//$this->versionsToProcess = array_merge($this->versionsToProcess, $patches);
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

		switch ($patch_type) {
			case "schema":
				$path = $this->schemapath;
				break;
			case "data":
				$path = $this->datapath;
				break;
			default:
				throw new exception("An invalid patch type was provided.");
		}

		$timestamp_prefix = date("Ymd_His");

		$this->printer->write("Setting patch prefix: {$timestamp_prefix}", 2);

		$answer = $this->printer->ask("Provide a description for the patch:");

		// normalize answer string, only using alphanum
		$normalized_answer = preg_replace("/\s/", "_", $answer);
		$normalized_answer = preg_replace("/[^\w]/", "", $normalized_answer);

		// set the file name
		$patch_file_name = "{$timestamp_prefix}_{$normalized_answer}.sql";

		$this->printer->write("Patch file name: {$patch_file_name}", 2);

		// lets create the file
		$fullpath = $path . "/" . $patch_file_name;
		if (!touch($fullpath)) {
			throw new exception("Unable to create the file {$fullpath}");
		}

		$this->printer->write("Patch file created; {$fullpath}");
	}

	/**
	 *  function recordVersions:  Helps keep track of final disposition of
	 *  versions to dbversion table.
	 *
	 */
	/*
	  public function recordVersions($versionIDs) {
	  if (!is_array($versionIDs))
	  $versionIDs = array($versionIDs);
	  if ($this->versionsToRecord === null)
	  $this->versionsToRecord = array();
	  $this->versionsToRecord = array_merge($this->versionsToRecord, $versionIDs);
	  }
	 *
	 */

	/**
	 *  function processXML: spin through the input (versions) and
	 *  invoke performProcessOnVersion for each version.
	 *  @return boolean whether or not the version processing completed.
	 *
	 */
	protected function processXML() {
		if ($this->db->doesTransactions())
			$this->db->startTransaction();

		foreach ($this->xml->version as $version) {
			if ($this->performProcessOnVersion((string) $version->id) === false)
				continue;
			$processResults = $this->processVersion($version);
		}

		if ($this->db->doesTransactions()) {
			if ($this->dryRun === true)
				$this->db->failTransaction();
			$processResults = $this->db->completeTransaction();
		}
		else {
			$processResults = true;
		}
		return $processResults;
	}

	/**
	 * function:  performProcessOnVersion validates a version against
	 * user input to determine status, process or not.
	 *
	 * @param string $versionID
	 * @return boolean
	 *
	 * protected function to perform logic
	 * associated with whether or not a particular version is
	 * supposed to be processed.
	 */
	protected function performProcessOnVersion($versionID) {
		if (is_array($this->versionsToProcess) && is_array($this->skip_patches)) {
			if (in_array($versionID, $this->versionsToProcess) && !in_array($versionID, $this->skip_patches)) {
				return true;
			} else {
				return false;
			}
		} elseif (is_array($this->versionsToProcess) && $this->skip_patches === null) {
			if (in_array($versionID, $this->versionsToProcess)) {
				return true;
			} else {
				return false;
			}
		} elseif ($this->versionsToProcess === null && is_array($this->skip_patches)) {
			if (in_array($versionID, $this->skip_patches)) {
				return false;
			} else {
				return true;
			}
		} else {
			// no rules on skipping or filtering, so we run it
			return true;
		}
	}

	/**
	 *  function: processVersion:  do a single version, if it has not
	 *  already been done.
	 *
	 *  There is the capability of processing DDL from it's own input
	 *  rather from the statement element, in order to a) remove DDL from
	 *  transaction processing and b) provide the ability to perform an
	 *  undo action on DDL.  This supports DBMS's that do not include
	 *  DDL (or data access language) in COMMIT/ROLLBACK processing.
	 *
	 */
	protected function processVersion($version) {
		$isVersionProcessed = $this->db->checkVersion((string) $version->id);
		if ($isVersionProcessed == true) {
			// skipping version
			$this->printer->write("Skipping Version ID: " . (string) $version->id, 1);
		} else if (is_array($this->versionsToRecord) && in_array((string) $version->id, $this->versionsToRecord)
			&& $dryrun === false) {
			$this->recordVersion((string) $version->id);
		} else {
			// processing version
			$returnResults = array();

			foreach ($version->ddl as $ddl) {
				#echo "This is the ddl in: " . var_dump($ddl) . "<br>\n";
				$ddl_sql = (string) $ddl->do;
				$ddl_rollback_hold = (string) $ddl->undo;
				#echo "Here is the ddl: " . var_dump((string)$ddl->do) . "\n";
				#echo "Here is the undo ddl: " . var_dump((string)$ddl->undo) . "\n";
				if ($ddl_sql and $ddl_rollback_hold) {
					$this->db->addRollBack($ddl_rollback_hold);
					$ok = $this->db->Execute($ddl_sql);
					if (strlen($this->db->getError()) > 0) {
						$this->printer->write($this->db->getError(), 1);
					}
					$returnResults[] = $ok;
				} else {
					$this->printer->write("Missing or unmatched DDL do and undo", 1);
					$returnResults[] = false;
				}
			}
			if (in_array(false, $returnResults)) {

				continue;
			} else {
				foreach ($version->statement as $statement) {
					$sql = (string) $statement;
					$ok = $this->db->Execute($sql);
					if (strlen($this->db->getError()) > 0) {
						$this->printer->write($this->db->getError(), 1);
					}
					$returnResults[] = $ok;
				}
			}

			$returnResults[] = $this->insertVersion($version);

			if (in_array(false, $returnResults)) {
				$this->printer->write("Adding Version ID: " . (string) $version->id . ", Status: Failed", 1);
			} else {
				$this->printer->write("Adding Version ID: " . (string) $version->id . ", Status: Success", 1);
			}
		}
	}

	/**
	 *  function insertVersion: assuming all is ok up to now, attempt to
	 *  actually insert the version info into dbversion table.
	 *  @return boolean yea or nay on insert of version.
	 *
	 */
	protected function insertVersion($version) {
		return $this->db->insertVersion($version, date('Y-m-d'));
	}
	
	/**
	 * Extracts the queries from a given patch file
	 * @param $filepath - The full path of the patch file
	 * @return Array
	 */
	protected function getFileQueries ($filepath) {
		$sql_lines = file_get_contents($filepath);
		$queries = preg_split("/;+(?=([^'|^\\\']*['|\\\'][^'|^\\\']*['|\\\'])*[^'|^\\\']*[^'|^\\\']$)/", $sql_lines);
		return $queries;
	}

}

?>
