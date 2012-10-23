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
            'user'   => 'cake',
            'pass'   => 'letthemeat',
	    'track_patches_in_file'   => false,
        ), /* */
        'cms' => array(
            //'driver' => 'mysql_database.php',
            'databasetype' => 'mysql',
            'host'   => 'localhost',
            'name'   => 'cms',
            'user'   => 'cake',
            'pass'   => 'letthemeat',
	    'track_patches_in_file'   => false,
        ),
       /*  'test' => array(
            //'driver' => 'mysql_database.php',
            'databasetype' => 'mysql',
            'host'   => 'localhost',
            'name'   => 'peter_test',
            'user'   => 'user',
            'pass'   => 'secret',
        ),*/
    );
}
