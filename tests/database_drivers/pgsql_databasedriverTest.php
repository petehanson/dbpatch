<?php

require_once '../database_drivers/pgsql_database.php';
require_once "../printers/printerbase.php" ;
require_once "../printers//cli.php" ;

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
    
    public function testCalculate()
    {
        $this->assertEquals(2, 1 + 1);
    }
    
    public static function tearDownAfterClass()
    { }
}

?>
