<?php

/**
 * Tracker factory - generates different flavors of databse drivers (mysql, pg.. etc.)
 *
 * @author fxneo
 */
class Driver_Factory {

    public static function Create(Config $config, $printer, $baseSchema, $suppressDbCreation = false) {

        switch ($config->getDriver()) {

            case "mysql":
                return new mysql_database($config->getHost(),$config->getDatabaseName(),$config->getUser(),$config->getPassword(), $printer, $baseSchema, $suppressDbCreation);
                break;

            default:
                throw new Exception('unhandled database type: ' . $config->getDriver());
        }
        
    }
}
