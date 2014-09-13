<?php

class DbPatch_Config_SingleDb
{
    public $dbClassFile;
    public $dbHost;
    public $dbName;
    public $dbUsername;
    public $dbPassword;
    public $dbTrackPatchesInFile;
    public $dbType;
    public $use_cli_client_for_reset = false;

    public $basefile;
    public $basepath;
    public $schemapath;
    public $datapath;
    public $scriptpath;
    public $standardized_timezone;
    public $prompt_for_root_user;
    public $root_level_commands;
}
