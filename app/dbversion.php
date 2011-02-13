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


class dbversion
{
/**#@+      
 * @access private
 *
 */
	
	
    protected $patchFile;
    protected $printer;
	
    protected $xml;
    protected $db;

    protected $dryRun;

    protected $versionsToProcess;
    protected $versionsToSkip;
    protected $versionsToRecord;
/**#@-*/

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
    public function __construct($patchFile,config $config,printerbase $printer)

    {
		$this->patchFile = $patchFile;
		$this->printer = $printer;

		$this->dryRun = false;
		$this->versionsToProcess = null;
		$this->versionsToSkip = null;
		$this->versionsToRecord = null;

		$this->xml = simplexml_load_file($this->patchFile);
		if ($this->xml === false) 
		throw new exception("An error occured while reading the xml file: {$this->patchFile}");

		$this->db = new database(config::$dbHost,config::$dbName,
			config::$dbUsername,config::$dbPassword,$printer);
		$this->db->checkForDBVersion();
    }
	
	
/**        
 *  destruct function - closes DB connection
 *
 */

    public function __destruct()	
    {
		$this->db->close();
    }
	
/**        
 *   dryRun function - no parms, sets dryrun flag based on input
 *   from invocker.
 *
 */

    public function dryRun()

    {
		$this->printer->write("Doing a Dry Run",1);
		$this->dryRun = true;
    }
 


/**        
 *  liveRun function - no parms, explicitly turns off dryrun 
 *  as appropriate.
 *
 */

    public function liveRun()
	
    {
		$this->printer->write("Doing a Live Run",1);
		$this->dryRun = false;
    }


/**        
 *  listVersions function: determine whether or not a version needs
 *  insertion into the dbversion table.
 *
 */

    public function listVersions()
	
    {
		foreach ($this->xml->version as $version)
		{
			if ($this->performProcessOnVersion((string)$version->id) 
				=== false) continue;
			$this->printer->write((string)$version->id,1);
			$this->printer->write((string)$version->description,2);
			$this->printer->write("Date: " . 
					(string)$version->date . 
					",	Person: " . 
					(string)$version->initiating_person,2);
			$this->printer->write("",2);
		}
    }


/**        
 *   execute function:  Just invokes the processXML function.
 *
 */

    public function execute()
    {
		$this->processXML();
    }



/**        
 *  function recordVersion: tests for current version in dbversion 
 *  table, and if it is not there, invokes insert method, notifies 
 *  user.
 * 
 *  @todo Possibly update to pass message in an array in the object
 *  in order to separate interface from logic.
 *
 */

    public function recordVersion($versionIDs)
    {

		if (!is_array($versionIDs)) $versionIDs = array($versionIDs);
		foreach ($this->xml->version as $version)
		{
			if (!in_array((string)$version->id,$versionIDs)) continue;
			$this->insertVersion($version);
			$this->printer->write("Inserting Version ID: " . (string)$version->id,1);
		}

		if ($this->db->doesTransactions())
		{
			if ($this->dryRun === true) $this->db->failTransaction();
			return $this->db->completeTransaction();
		}
		else
		{
			return true;
		}
    }
/**        
 *  function addVersions:  Helps keep track of final disposition of
 *  versions to dbversion table.
 *
 */

    public function addVersions($versionIDs)
    {
		if (!is_array($versionIDs)) $versionIDs = array($versionIDs);
		if ($this->versionsToProcess === null) $this->versionsToProcess = array();
		$this->versionsToProcess = array_merge($this->versionsToProcess,$versionIDs);
    }
/**        
 *  function skipVersions:  Helps keep track of final disposition of
 *  versions to dbversion table.
 *
 */
    public function skipVersions($versionIDs)
    {
		if (!is_array($versionIDs)) $versionIDs = array($versionIDs);
		if ($this->versionsToSkip === null) $this->versionsToSkip = array();
		$this->versionsToSkip = array_merge($this->versionsToSkip,$versionIDs);
    }
/**        
 *  function recordVersions:  Helps keep track of final disposition of
 *  versions to dbversion table.
 *
 */
    public function recordVersions($versionIDs)
    {
		if (!is_array($versionIDs)) $versionIDs = array($versionIDs);
		if ($this->versionsToRecord === null) $this->versionsToRecord = array();
		$this->versionsToRecord = array_merge($this->versionsToRecord,$versionIDs);
    }
	
	
/**        
 *  function processXML: spin through the input (versions) and
 *  invoke performProcessOnVersion for each version.
 *  @return boolean whether or not the version processing completed.
 *
 */

    protected function processXML()
    {
		if ($this->db->doesTransactions()) $this->db->startTransaction();

		foreach ($this->xml->version as $version)
		{
			if ($this->performProcessOnVersion((string)$version->id) === false) continue;
			$processResults = $this->processVersion($version);
		}

		if ($this->db->doesTransactions())
		{
			if ($this->dryRun === true) $this->db->failTransaction();
			$processResults = $this->db->completeTransaction();
		}
		else
		{
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
    protected function performProcessOnVersion($versionID)

    {
		if (is_array($this->versionsToProcess) && is_array($this->versionsToSkip))
		{
			if (in_array($versionID,$this->versionsToProcess) && !in_array($versionID,$this->versionsToSkip))
			{
			return true;
			}
			else
			{
			return false;
			}
		}
		elseif (is_array($this->versionsToProcess) && $this->versionsToSkip === null)
		{
			if (in_array($versionID,$this->versionsToProcess))
			{
			return true;
			}
			else
			{
			return false;
			}
		}
		elseif ($this->versionsToProcess === null && is_array($this->versionsToSkip))
		{
			if (in_array($versionID,$this->versionsToSkip))
			{
			return false;
			}
			else
			{
			return true;
			}
		}
		else
		{
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


    protected function processVersion($version)

    {
		$isVersionProcessed = $this->db->checkVersion((string)$version->id);
		if ($isVersionProcessed == true)
		{
			// skipping version
			$this->printer->write("Skipping Version ID: " . (string)$version->id,1);
		}
		else if (is_array($this->versionsToRecord) && in_array((string)$version->id,$this->versionsToRecord)
					&& $dryrun === false)
		{
			$this->recordVersion((string)$version->id);	
		}
		else
		{
			// processing version
			$returnResults = array();
			
			foreach ($version->ddl as $ddl)
			{
				#echo "This is the ddl in: " . var_dump($ddl) . "<br>\n";
				$ddl_sql = (string)$ddl->do;
				$ddl_rollback_hold = (string)$ddl->undo;
				#echo "Here is the ddl: " . var_dump((string)$ddl->do) . "\n";
				#echo "Here is the undo ddl: " . var_dump((string)$ddl->undo) . "\n";
				if ($ddl_sql and $ddl_rollback_hold) 
				{
					$this->db->addRollBack($ddl_rollback_hold);
					$ok = $this->db->Execute($ddl_sql);
					if (strlen($this->db->getError()) > 0)
					{
						$this->printer->write($this->db->getError(),1);
					}
					$returnResults[] = $ok;
				} 
				else 
				{
					$this->printer->write("Missing or unmatched DDL do and undo", 1);
					$returnResults[] = false;
				}
			}	
			if (in_array(false,$returnResults)) 
			{
					
				continue;
			}
			else 
			{	
				foreach ($version->statement as $statement)
				{
					$sql = (string)$statement;
					$ok = $this->db->Execute($sql);
					if (strlen($this->db->getError()) > 0)
					{
						$this->printer->write($this->db->getError(),1);
					}
					$returnResults[] = $ok;
				}
			}

			$returnResults[] = $this->insertVersion($version);

			if (in_array(false,$returnResults))
			{
				$this->printer->write("Adding Version ID: " . (string)$version->id . ", Status: Failed",1);
			}
			else
			{
				$this->printer->write("Adding Version ID: " . (string)$version->id . ", Status: Success",1);
			}
		}
    }

/**        
 *  function insertVersion: assuming all is ok up to now, attempt to 
 *  actually insert the version info into dbversion table. 
 *  @return boolean yea or nay on insert of version.
 *
 */

    protected function insertVersion($version)
    {
		return $this->db->insertVersion((string)$version->id,(string)$version->description,(string)$version->date,(string)$version->initiating_person);
    }

}

?>
