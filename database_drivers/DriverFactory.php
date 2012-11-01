<?php

/**
 * Tracker factory - generates different flavors of databse drivers (mysql, pg.. etc.)
 *
 * @author fxneo
 */
class Driver_Factory {

    public static function Create($dbType, $dbHost, $dbName, $dbUsername, $dbPassword, $printer, $baseSchema, $suppressDbCreation = false) {
        
        if ($dbType == "mysql")
        {
            return new mysql_database($dbHost, $dbName, 
                $dbUsername, $dbPassword, $printer, $baseSchema, $suppressDbCreation);

        } else if ($dbType == "pgsql") {
            return new pg_database($dbHost, $dbName, 
                $dbUsername, $dbPassword, $printer, $baseSchema, $suppressDbCreation);
        } else {
            throw new Exception('unhandled database type: ' . $dbType);
        }
    }

}

?>
