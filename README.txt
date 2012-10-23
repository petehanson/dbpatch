ABOUT
-----

patch_database.php is a versioning tool for databases. It currently supports
MySQL and PostgreSQL.

As of October, 2012, it is now possible to add dbpatch as a git module and use it unmodified.
The basic procedure is in your project to:
  git submodule add username@dev.upandrunningsoftware.com:/data/git/dbpatch.git
  mkdir sql
  cd sql
  cp ../dbpatch/*.tpl .
  mv config.php.tpl config.php
  mv db.php.tpl db.php
  mv patch_wrapper.php.tpl patch_database.php
  mv reset_db_wrapper.php.tpl reset_db.php

  then set up as appropriate following comments in the wrappers and instructions below.
    db.php can usually be left untouched
  The rest of the instructions below still apply, just in the sql directory rather than the dbpatch module directory


INSTALLING/CONFIGURATION
------------------------

Dependencies:
 - PHP 5
 - For use with MySQL:
    * PHP MySQLi extension installed and enabled
 - For use with PostgreSQL:
    * PHP PostgreSQL extension installed and enabled

Modify config.php to suit your needs. A short description of the configuration 
options follows:

The config class should extend the DbPatch_Config_Master. For this to work,
the config class must have a static $db property that should be an array and you 
can specify 1+ databases this way. This is a static array with the key specifying 
database and value as an array of connection options.

   array(
        'blog' => array(
            'driver' => 'mysql_database.php',
            'host'   => 'localhost',
            'name'   => 'blog',
            'user'   => 'user',
            'pass'   => 'secret',
        ),
        'test' => array(
            'driver' => 'mysql_database.php',
            'host'   => 'localhost',
            'name'   => 'test',
            'user'   => 'user',
            'pass'   => 'secret',
        ),
    );
    
 - driver:
   Name of the database driver to use. For MySQL, use 'mysql_database.php'. For
   PostgreSQL, use 'pgsql_database.php'.

 - host:
   Host of the database server. If the database is running on the same machine
   as this script, use 'localhost'.

 - name:
   Name of the database to be versioned.

 - user:
   Username to connect to the database server with.

 - pass:
   Password associated with user.


The following options have been deprecated:

 - $dbClassFile:
   Name of the database driver to use. For MySQL, use 'mysql_database.php'. For
   PostgreSQL, use 'pgsql_database.php'.
 
 - $dbHost:
   Host of the database server. If the database is running on the same machine
   as this script, use 'localhost'.

 - $dbName:
   Name of the database to be versioned. Currently this script is only able to
   manage a single database at a time.

 - $dbUsername:
   Username to connect to the database server with.

 - $dbPassword:
   Password associated with $dbUsername.

 - $basefile:
   Filename of the base SQL schema. This is applied once directly after the
   database is created.

 - $basepath:
   Path to the directory containing $basefile.

 - $schemapath:
   Path to the directory containing the schema patch files.

 - $datapath:
   Path to the directory containing the data patch files.

 - $standardized_timezone:
   Timezone used when recording dates of changes to the database.
   


USAGE
-----

Usage: ./patch_database [COMMAND] [COMMANDOPTS] [OPTIONS]


Where COMMAND is one of:

   -h or --help
      Show script usage information.
   
   -p or --patch
      Apply any patches that haven't already been applied to the database.
   
   -l or --list
      List which patches would be applied to the database if --patch was run.
   
   -aVERSION or --add=VERSION
      Apply one or more patches to the database, specified by VERSION. See below
      for the syntax of VERSION.
   
   -rVERSION or --record=VERSION
      Mark one or more patches as already having been added to the database, but
      don't actually apply the patches. See below for the syntax of VERSION.


VERSION syntax:

   VERSION is a comma separated list of SQL patch filenames. For example, to
   specify the patches 1_trunk_person.sql and 2_trunk_person.sql, replace
   'VERSION' with '1_trunk_person.sql,2_trunk_person.sql' (note that there is
   no space after the comma).


COMMANDOPTS (command-specific options):

   For --patch, the following COMMANDOPTS are available:
   
      -sVERSION or --skip=VERSION
         Ignore the patches specified by VERSION.
      
      -SVERSION or --skip-and-record=VERSION
         Ignore the patches specified by VERSION, but mark them as having been
         added to the database. This prevents them from being added by future
         --patch runs.
   
   No other commands accept any options.


OPTIONS:

   -v or --verbose
      Show debug information while running.
   
   -q or --quiet
      Don't show any output unless specifically requested (e.g., via --list).
   
   -d or --dryrun
      Don't make any changes to the database.
