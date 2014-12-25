ABOUT
-----

dbpatch is a management tool for tracking and applying database migration/schema patches.

It currently supports MySQL (via PDO).

INSTALLING/CONFIGURATION
------------------------

You can install dbpatch as a git submodule. More ideal (and coming soon) will be via composer.

Once you have installed the application, then you want to create a new configuration folder set.
A sample configuration folder is at test/.  It consists of a config.php that lets you set up a config object and a
sql/ folder that contains four sub folders that store different types of patches.

schema/  - used to store patches that adjust table/function references.
data/ - used to store patches that apply data updates.
script/ - used to store script files, like bash or PHP scripts that can be applied to modify database data

To run the initialization:

./dbpatch init relative_path_folder_name

Then edit folder/config.php and modify the instantiation line for the config class.

The parameters are in the following order:

$id => An ID you define to identify the configuration
$driver => For now, this should remain "mysql"
$host => This is the IP of the database server
$databaseName => The name of the database in question
$user => The username to connect as
$pass => The password of the user
$port => The port the database is listening at

The config.php is set up like it is, so if you want to import the database connection parameters from somewhere else
in your project, you can do so, then populate the parameters of the config class.


USAGE
_____

ToDo

