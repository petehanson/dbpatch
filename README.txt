ABOUT
-----

patch_database.php is a versioning tool for databases. It currently supports
MySQL.


INSTALLING/CONFIGURATION
------------------------

Dependencies:
 - PHP 5
 - For use with MySQL:
    * PHP MySQLi extension installed and enabled
 - For use with PostgreSQL:
    * PHP PostgreSQL extension installed and enabled
 - Console/GetOpt.php needs to be available in the include path


There's two options for installation:
1. Composer (recommended)
2. Git Submodule

Composer method
In your composer.json file, add these lines to your project:

"repositories": [
	{
		"type": "vcs",
		"url":  "ssh://git@secure.upandrunningsoftware.com/dbpatch"
	}
],
"require": {
	"uarsoftware/dbpatch": "*"
}

Then run the install or update command with composer
The files will be installed at vendor/uarsoftware/dbpatch/


Git submodule method
    In your project, run:  git submodule add ssh://git@secure.upandrunningsoftware.com/dbpatch
    If you’ve cloned the project, then you’ll need to run:
        git submodule init
        git submodule update


Once you've installed the package with either composer or git, then you want to make a folder to reference the tool and store patches.

1. In the root of your project, make a folder like sql/ or database_patches/. This folder needs to be at the same level as the dbpatch or vendor folder where it was installed.
2. Copy the files from installation/composer/ or installation/git_submodule, depending on the approach that you used.
3. Modify the config.php file to fit the number of DBs you'll have. See the section below about the config options.
4. Init the folders for patch storage for each database in the config with this:  php patch_database.php --init


CONFIG.PHP
----------

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


//TODO:  add additional properties here.


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
