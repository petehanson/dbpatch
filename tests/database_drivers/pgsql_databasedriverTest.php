<?php

require_once '../database_drivers/pgsql_database.php';
require_once "../printers/printerbase.php" ;
require_once "../printers//cli.php" ;

/*
 * Setup a DB on localhost called postgretestdb and create 2 users
 * postgres - admin user
 * mick - non admin user
 */
class pgsql_driver_tests extends PHPUnit_Framework_TestCase {
    public static function setUpBeforeClass()
    { }
    
    public function testAdminHasPrivilege()
    {
         // this user should be a super user
        $db = new pg_database("localhost", "postgretestdb", "postgres", "postgres", new printer());
        $result = $db->userHasPrivilege("TRIGGER");
        $db->close();
        $this->assertEquals(true, $result);
    }
    
    public function testAdminHasDbCreatePrivilege()
    {
         // this user should be a super user
        $db = new pg_database("localhost", "postgretestdb", "postgres", "postgres", new printer());
        $result = $db->userHasDbCreatePrivilege();
        $db->close();
        $this->assertEquals(true, $result);
    }
    
    public function testNonAdminUserHasNotPrivilege()
    {
         // this user should be a regular user
        $db = new pg_database("localhost", "postgretestdb", "mick", "mick", new printer());
        $result = $db->userHasPrivilege("TRIGGER");
        $db->close();
        $this->assertEquals(false, $result);
    }
    
    public function testNonAdminUserHasNotDbCreatePrivilege()
    {
         // this user should be a regular user
        $db = new pg_database("localhost", "postgretestdb", "mick", "mick", new printer());
        $result = $db->userHasDbCreatePrivilege();
        $db->close();
        $this->assertEquals(false, $result);
    }
    
    /**
     * Test that the function returns an array that is valid 
     */
    public function testAppliedPatchesNamesArrayIsValid()
    {
         // this user should be a regular user
        $db = new pg_database("localhost", "postgretestdb", "postgres", "postgres", new printer());
        $result = $db->get_applied_patch_names();
        $db->close();
        $this->assertEquals(true, isset($result) && is_array($result));
    }
    
    public function testInsertPatchItem()
    {
         // this user should be a regular user
        $db = new pg_database("localhost", "postgretestdb", "postgres", "postgres", new printer());
        
        $id = "integration_test_1";
        
        $result = $db->insertVersion($id, date("Y-m-d"));
        $cleanupResult = $db->execute("delete from dbversion where applied_patch ='" . $id . "'");
        
        $db->close();
        $this->assertEquals(true, $result !== FALSE && $cleanupResult !== FALSE);
    }
    
    /**
     * Test that the function returns an array that is valid 
     */
    public function testAppliedPatchesItemsArrayIsValid()
    {
         // this user should be a regular user
        $db = new pg_database("localhost", "postgretestdb", "postgres", "postgres", new printer());
        $result = $db->get_applied_patch_items();
        $db->close();
        $this->assertEquals(true, isset($result) && is_array($result));
    }
    
    /**
     * Test changing a user of the current connection works 
     */
    public function testChangeUser()
    {
         // this user should be a super user
        $db = new pg_database("localhost", "postgretestdb", "postgres", "postgres", new printer());
        $db->change_user("mick", "mick");
        $result = $db->ping_db();
        $db->close();
        $this->assertEquals(true, $result && !$db->has_error());
    }
    
    /**
     * Test a SQL query can be executed via the driver
     */
    public function testQueryExecution()
    {
         // this user should be a super user
        $db = new pg_database("localhost", "postgretestdb", "postgres", "postgres", new printer());
        $result = $db->execute("select * from dbversion;");
        $db->close();
        $this->assertEquals(true, $result !== FALSE && !$db->has_error());
    }
    
    /**
     * Test check for DB succeeded without any issues 
     */
    public function testCheckForDbVersion()
    {
         // this user should be a super user
        $db = new pg_database("localhost", "postgretestdb", "postgres", "postgres", new printer());
        $result = $db->checkForDBVersion();
        $db->close();
        $this->assertEquals(true, $result && !$db->has_error());
    }
    
    /**
     * Test connection to DB works 
     */
    public function testConnectionToDb()
    {
         // this user should be a super user
        $db = new pg_database("localhost", "postgretestdb", "postgres", "postgres", new printer());
        $result = $db->ping_db();
        $db->close();
        $this->assertEquals(true, $result);
    }
    
    /**
     * Test closing connection to DB works 
     */
    public function testConnectionToDbClosing()
    {
         // this user should be a super user
        $db = new pg_database("localhost", "postgretestdb", "postgres", "postgres", new printer());
         $db->close();
        $result = $db->ping_db();
       
        $this->assertEquals(false, $result);
       
    }
    
    public static function tearDownAfterClass()
    { }
}

?>
