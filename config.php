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
        /* 'blog' => array(
            //'driver' => 'mysql_database.php',
            'databasetype' => 'mysql',
            'host'   => 'localhost',
            'name'   => 'my_blog',
            'user'   => 'root',
            'pass'   => 'root',
	    'track_patches_in_file'   => false,
            'use_cli_client_for_reset' => false // true if base needs mysql client
        ), /* */
        'test' => array(
            //'driver' => 'mysql_database.php',
            'databasetype' => 'mysql',
            'host'   => 'localhost',
            'name'   => 'test',
            'user'   => 'root',
            'pass'   => 'root',
			'track_patches_in_file'   => false,
            'use_cli_client_for_reset' => false
        ),
       /*  'test' => array(
            //'driver' => 'mysql_database.php',
            'databasetype' => 'mysql',
            'host'   => 'localhost',
            'name'   => 'peter_test',
            'user'   => 'user',
            'pass'   => 'secret',
            'use_cli_client_for_reset' => false // true if base needs mysql client
        ),*/
    );
}
