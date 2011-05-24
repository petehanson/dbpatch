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
		if (!$this->applyBaseSchema()) {
			return false;
		}
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
	 * needed_patches function:
	 * Get a list of the patches that need to be applied to the database (taking into account skipped/added
	 * patches). Returns an array('data'=>array(...), 'schema'=>array(...)), where data contains data patches
	 * and schema contains schema patches.
	 */
	protected function needed_patches() {
		// get list of applied patches from db
		$applied_patches = $this->db->get_applied_patches();

		// get list of patches on the file system
		$schema_patches = $this->get_patch_files($this->schemapath);
		$data_patches = $this->get_patch_files($this->datapath);

		// determine outstanding patches
		$needed_schema_patches = array_diff($schema_patches, $applied_patches);
		$needed_data_patches = array_diff($data_patches, $applied_patches);

		// filter out any specified in skip
		$needed_schema_patches = array_diff($needed_schema_patches, $this->skip_patches);
		$needed_data_patches = array_diff($needed_data_patches, $this->skip_patches);

		return array(
		    'data' => $needed_data_patches,
		    'schema' => $needed_schema_patches
		);
	}

	/**
	 *  list_patches function: prints what patches need to be applied to the current working copy database.
	 *  Optionally takes a parameter $needed_patches, an array in the format output by $this->needed_patches()
	 *
	 */
	public function list_patches($needed_patches=null) {
		// Get a list of the patches that need to be applied
		if ($needed_patches === null) {
			$needed_patches = $this->needed_patches();
			if ($needed_patches === false) {
				return false;
			}
		}
		$needed_data_patches = $needed_patches['data'];
		$needed_schema_patches = $needed_patches['schema'];

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
			foreach ($needed_data_patches as $patch) {
				$this->printer->write("\t" . $patch);
			}
		} else {
			$this->printer->write("No data patches to be applied were found");
		}

		return true;
	}

	/**
	 *   apply_patches function:  executes the patching process
	 *
	 */
	public function apply_patches() {
		$return_result = true;

		// Get a list of the patches that need to be applied
		$needed_patches = $this->needed_patches();
		if ($needed_patches === false) {
			return false;
		}
		$needed_data_patches = $needed_patches['data'];
		$needed_schema_patches = $needed_patches['schema'];

		// sort patches into correct order by timestamp prefix (filename)
		sort($needed_schema_patches);
		sort($needed_data_patches);

		// Print out what patches will be applied/skipped
		$this->list_patches($needed_patches);


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
			}
		}


		return $return_result;
	}

	/**
	 * function applyBaseSchema: Applies the base schema to the database when necessary(Creates the table structure)
	 */
	public function applyBaseSchema() {
		if ($this->db->isNewDB() || !$this->db->dbExists()) {
			$fullpath = realpath($this->basepath . "/" . $this->basefile);
			$success = $this->db->executeBase(file_get_contents($fullpath));
			if (!$success) {
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
	public function add_patches($patches) {
		$paths = array();
		$applied_patches = $this->db->get_applied_patches();
		foreach ($patches as $patch) {
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
				$queries = $this->get_queries_from_file($p);

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
	protected function get_queries_from_file($filepath) {
		return file_get_contents($filepath);
	}

}

?>
