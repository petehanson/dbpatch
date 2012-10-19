<?php

/**
 * Tracker factory - generates different flavors of databse drivers (mysql, pg.. etc.)
 *
 * @author fxneo
 */
class Driver_Factory {

    public static function Create($dbType, $dbHost, $dbName, $dbUsername, $dbPassword, $printer, $baseSchema) {
        
        if ($dbType == "mysql")
        {
            return new mysql_database($dbHost, $dbName, 
                $dbUsername, $dbPassword, $printer, $baseSchema);
        }
        if ($dbType == "pgsql")
        {
            return new pg_database($dbHost, $dbName, 
                $dbUsername, $dbPassword, $printer, $baseSchema);
        }
    }

}

?>
