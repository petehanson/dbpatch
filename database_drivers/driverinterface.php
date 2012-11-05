<?php
/**
 * interface driverinterface
 *
 * @package driverinterface.php
 * @todo Consider review of implementations for possible method and
 * variable inclusion in the parent class.  Define abstract methods
 * signatures?
 *
 */
/**
 * interface driverinterface - null at present.
 *
 * @package driverinterface
 *
 */
interface driverinterface
{
	public function checkForDBVersion();
	public function close();
	public function get_applied_patch_names();
        public function get_applied_patch_items();
        public function change_user($username, $password);
	public function executeFile($file);
	public function has_error();
    public function getConnection();
    public function clearError();
        public function ping_db();
	public function insertVersion($id,$date);
}

?>
