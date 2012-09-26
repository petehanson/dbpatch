<?php

/**
 * A tracker class to handle the version tracking mechanism using a SQL Store
 * 
 * Example:
 * $tracker->insert_new_version(array("item" => array("id" => "12309125", "version" => "ver1")));
 * 
 */
class DbVersionTracker implements trackerinterface {

    protected $db;

    public function __construct($db) {
        $this->db = $db;

        $this->create_version_tracking_table();
    }

    /**
     * Check that versioning table exists in DB. If not create it.
     * Every DB should have a separate table.
     */
    private function create_version_tracking_table() {
        $this->db->checkForDBVersion();
    }

    /*
     * Dispose any resources
     */
    public function dispose() {
         if ($this->db)
             $this->db->close();
    }

    public function get_applied_patches() {
        return $this->db->get_applied_patches();
    }

    public function has_error() {
        
    }

    /**
     *  assuming all is good, insert the version
     *  info into the version store.
     *
     */
    public function insert_new_version($tracking_item) {
        $this->db->insertVersion(
                $tracking_item["item"]["applied_patch"], 
                $tracking_item["item"]["date_patch_applied"]);
    }
}

?>
