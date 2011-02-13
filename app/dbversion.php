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
	protected $schemapath;
	protected $datapath;
	//protected $dryRun;
	protected $versionsToProcess;
	protected $versionsToSkip;
	protected $versionsToRecord;
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
		$this->schemapath = realpath($base_folder . "/" . config::$schemapath);
		$this->datapath = realpath($base_folder . "/" . config::$datapath);

		/*
		$printer->write("Base Path: {$this->basepath}");
		$printer->write("Schema Path: {$this->schemapath}");
		$printer->write("Data Path: {$this->datapath}");
		 */

		$this->versionsToProcess = null;
		$this->versionsToSkip = null;
		$this->versionsToRecord = null;

		$this->db = new database(config::$dbHost, config::$dbName,
				config::$dbUsername, config::$dbPassword, $printer);
		$this->db->checkForDBVersion();

		date_default_timezone_set(config::$standardized_timezone);
	}

	/**
	 *  destruct function - closes DB connection
	 *
	 */
	public function __destruct() {
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
	}

	/**
	 *  function record_patches: just records the patch data into the dbverions table
	 *
	 */
	public function record_patches($versionIDs) {

		if (!is_array($versionIDs))
			$versionIDs = array($versionIDs);
		foreach ($this->xml->version as $version) {
			if (!in_array((string) $version->id, $versionIDs))
				continue;
			$this->insertVersion($version);
			$this->printer->write("Inserting Version ID: " . (string) $version->id, 1);
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
	public function add_patches($versionIDs) {
		if (!is_array($versionIDs))
			$versionIDs = array($versionIDs);
		if ($this->versionsToProcess === null)
			$this->versionsToProcess = array();
		$this->versionsToProcess = array_merge($this->versionsToProcess, $versionIDs);
	}

	/**
	 *  function skip_patches:  patches that should be skipped when patching
	 *
	 */
	public function skip_patches($versionIDs) {
		if (!is_array($versionIDs))
			$versionIDs = array($versionIDs);
		if ($this->versionsToSkip === null)
			$this->versionsToSkip = array();
		$this->versionsToSkip = array_merge($this->versionsToSkip, $versionIDs);
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

		$this->printer->write("Setting patch prefix: {$timestamp_prefix}",2);

		$answer = $this->printer->ask("Provide a description for the patch:");

		// normalize answer string, only using alphanum
		$normalized_answer = preg_replace("/\s/","_",$answer);
		$normalized_answer = preg_replace("/[^\w]/","",$normalized_answer);

		// set the file name
		$patch_file_name = "{$timestamp_prefix}_{$normalized_answer}.sql";

		$this->printer->write("Patch file name: {$patch_file_name}",2);

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
		if (is_array($this->versionsToProcess) && is_array($this->versionsToSkip)) {
			if (in_array($versionID, $this->versionsToProcess) && !in_array($versionID, $this->versionsToSkip)) {
				return true;
			} else {
				return false;
			}
		} elseif (is_array($this->versionsToProcess) && $this->versionsToSkip === null) {
			if (in_array($versionID, $this->versionsToProcess)) {
				return true;
			} else {
				return false;
			}
		} elseif ($this->versionsToProcess === null && is_array($this->versionsToSkip)) {
			if (in_array($versionID, $this->versionsToSkip)) {
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
		return $this->db->insertVersion((string) $version->id, (string) $version->description, (string) $version->date, (string) $version->initiating_person);
	}

}

?>
