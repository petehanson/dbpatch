<?php
require_once 'DbPatch/Config/Master.php';

class config extends DbPatch_Config_Master
{
    /**
     * Property with all the database to connect to.
     *
     * <code>
     * $db = array(
     *   'blog' => array(
     *       'driver' => 'mysql_database.php',
     *       'host'   => 'localhost',
     *       'name'   => 'my_blog',
     *       'user'   => 'user',
     *       'pass'   => 'secret',
     *   ),
     *   'cms' => array(
     *       'driver' => 'mysql_database.php',
     *       'host'   => 'localhost',
     *       'name'   => 'my_cms',
     *       'user'   => 'user',
     *       'pass'   => 'secret',
     *   ),
     *   'test' => array(
     *       'driver' => 'mysql_database.php',
     *       'host'   => 'localhost',
     *       'name'   => 'my_test_db',
     *       'user'   => 'user',
     *       'pass'   => 'secret',
     *   ),
     * );
     * </code>
     *
     * @var array
     */
    public static $db = array(
        'blog' => array(
            'driver' => 'mysql_database.php',
            'host'   => 'localhost',
            'name'   => 'peter_blog',
            'user'   => 'user',
            'pass'   => 'secret',
        ),
        'cms' => array(
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
        ),
    );
}
