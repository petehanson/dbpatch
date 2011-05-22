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
	public function get_applied_patches();
	public function execute($sql);
	public function has_error();
	public function isNewDB();
	public function insertVersion($id,$date);
}

?>
