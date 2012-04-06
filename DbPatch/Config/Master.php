<?php

require_once 'DbPatch/Config/SingleDb.php';
require_once 'configdb.php';

class DbPatch_Config_Master extends configdb
{

    /**
     * @todo Should not be used anymore
     * @deprecated
     */
    public static $basefile = "base.sql";

    /**
     * @todo Should not be used anymore
     * @deprecated
     */
    public static $basepath = "sql/base/";

    /**
     * @todo Should not be used anymore
     * @deprecated
     */
    public static $schemapath = "sql/schema/";

    /**
     * @todo Should not be used anymore
     * @deprecated
     */
    public static $datapath = "sql/data/";

    public static $standardized_timezone = "UTC";

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
/*    public static $db = array(
        'blog' => array(
            'driver' => 'mysql_database.php',
            'host'   => 'localhost',
            'name'   => 'my_blog',
            'user'   => 'str',
            'pass'   => 'strpass',
        ),
        'cms' => array(
            'driver' => 'mysql_database.php',
            'host'   => 'localhost',
            'name'   => 'my_cms',
            'user'   => 'str',
            'pass'   => 'strpass',
        ),
        'test' => array(
            'driver' => 'mysql_database.php',
            'host'   => 'localhost',
            'name'   => 'my_test_db',
            'user'   => 'str',
            'pass'   => 'strpass',
        ),
    );*/

    public static function getSingleDbConfigs()
    {

         if (!count(self::$db)) throw new Exception('Database array not set or is empty in config file');
        $configs = array();
        foreach (self::$db as $name => $data) {
            $config = new DbPatch_Config_SingleDb();
            $config->dbClassFile = $data['driver'];
            $config->dbHost      = $data['host'];
            $config->dbName      = $data['name'];
            $config->dbUsername  = $data['user'];
            $config->dbPassword  = $data['pass'];

            $config->basepath    = $name.'/'.self::$basepath;
            $config->schemapath  = $name.'/'.self::$schemapath;
            $config->datapath    = $name.'/'.self::$datapath;

            $config->basefile                 = self::$basefile;
            $config->standardized_timezone    = self::$standardized_timezone;

            $configs[] = $config;
        }
        return $configs;
    }
}
