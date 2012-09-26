<?php

/**
 * A tracker class to handle the version tracking mechanism using the disk file system
 * 
 * Example:
 * $tracker->insert_new_version(array("item" => array("id" => "12309125", "version" => "ver1")));
 * 
 */
class File_Version_Tracker implements trackerinterface {

    protected $dbName;
    protected $baseFolder;
    protected $versioningFilePath;
    protected $hasError;

    public function __construct($db, $baseFolder) {
        $this->dbName = $db;
        $this->baseFolder = $baseFolder;

        $this->create_version_tracking_file();
    }

    /**
     * Check that versioning file exists on file system. If not create it.
     * Every DB should have a separate tracking XML file.
     */
    private function create_version_tracking_file() {

        // This is the default path/name
        $this->versioningFilePath = $this->baseFolder . "/" . $this->dbName . "_trackingFile.xml";

        if (!file_exists($this->versioningFilePath)) {
            $createdFile = fopen($this->versioningFilePath, "c");

            if (!$createdFile)
                die("critical: cannot create versioning file on path:  $this->versioningFilePath!");
            else {
                fwrite($createdFile, '<?xml version="1.0" encoding="UTF-8" ?>');
                fwrite($createdFile, '<items />');
                fclose($createdFile);
            }
        }
    }

    /**
     * function get_applied_patches:  returns a 2 dimensional array of of patch data back to the client code
     * Returns a list of applied patches.
     * @return array
     */
    public function get_applied_patches() {
        $xml = file_get_contents($this->versioningFilePath);

        if ($xml) {
            $element = new SimpleXMLElement($xml);

            return $element->xpath("/items/item/applied_patch");
        }
        else
            echo "Could not get versioning file contents!";
    }

    public function has_error() {
        return $this->hasError;
    }

    /**
     *  assuming all is good, insert the version
     *  info into the versioning xml.
     *
     */
    public function insert_new_version($tracking_item) {
        $versioningXml = file_get_contents($this->versioningFilePath);
        $xmlDoc = null;

        if ($versioningXml) {
            $xmlDoc = new SimpleXMLElement($versioningXml);
        }
        
        if (isset($xmlDoc)) {
            $item = $xmlDoc->addChild('item');
            $item->addChild('applied_patch', $tracking_item["item"]["applied_patch"]);
            $item->addChild('date_patch_applied', $tracking_item["item"]["date_patch_applied"]);
            
            $xmlDoc->saveXML($this->versioningFilePath);
        }
        else
            die("critical: cannot store data to XML!");
    }

    public function dispose() {
        // Do nothing
    }

}

?>
