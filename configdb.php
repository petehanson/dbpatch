<?php

class configdb
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
        'blog' => array(
            'driver' => 'mysql_database.php',
            'host'   => 'localhost',
            'name'   => 'my_blog',
            'user'   => 'str',
            'pass'   => 'strpass',
        ),
/*        'cms' => array(
            'driver' => 'mysql_database.php',
            'host'   => 'localhost',
            'name'   => 'peter_cms',
            'user'   => 'user',
            'pass'   => 'secret',
        ),
        'test' => array(
            'driver' => 'mysql_database.php',
            'host'   => 'localhost',
            'name'   => 'peter_test',
            'user'   => 'user',
            'pass'   => 'secret',
        ),*/
    );
}
