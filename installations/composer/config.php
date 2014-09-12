<?php

class config
{
    /**
     * Property with all the database to connect to.
     *
     * <code>
     * $db = array(
     *   'database_name_directory' => array(
     *       'driver' => 'mysql_database.php',
     *       'host'   => 'localhost',
     *       'name'   => 'database_name',
     *       'user'   => 'user',
     *       'pass'   => 'password',
     *   ),
     *   ...
     *   ...
     * );
     * </code>
     *
     * @var array
     */
    public static $db = array(
        'test_db_1' => array(
            'databasetype' => 'mysql',
            'host'   => 'localhost',
            'name'   => 'test1',
            'user'   => 'root',
            'pass'   => 'root',
	        'track_patches_in_file'   => false,
            'use_cli_client_for_reset' => false // true if base needs mysql client
        ),
        'test_db_2' => array(
            'databasetype' => 'mysql',
            'host'   => 'localhost',
            'name'   => 'test2',
            'user'   => 'root',
            'pass'   => 'root',
            'track_patches_in_file'   => false,
            'use_cli_client_for_reset' => false
        ),
        // can add more here for additional databases
    );
}
