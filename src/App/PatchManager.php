<?php

namespace uarsoftware\dbpatch\App;

class PatchManager {

    protected $config;
    protected $patches;
    protected $database;

    protected $filesystemSchemaPatches;
    protected $filesystemDataPatches;
    protected $filesystemScriptPatches;

    protected $filesystemPatches;
    protected $databasePatches;

    public function __construct(Config $config,DatabaseInterface $database) {
        $this->config = $config;
        $this->database = $database;

        $this->patches = array();



        $this->filesystemSchemaPatches = array();
        $this->filesystemDataPatches = array();
        $this->filesystemScriptPatches = array();

        $this->filesystemPatches = array();

        // get all the file system listings

        $this->listPatchesFromFilesystem();

        $this->databasePatches = $database->getAppliedPatches();
        //$this->createPatches($patchList);

    }

    protected function listPatchesFromFilesystem() {

        $processType = function($path) {
            $patches = array();
            $d = dir($path);
            while (false !== ($file = $d->read())) {
                echo $file;
                $patch = new Patch($file);
                $patches[] = $patch;
            }

            return $patches;
        };

        $this->filesystemSchemaPatches = $processType($this->config->getSchemaPath());
        $this->filesystemDataPatches = $processType($this->config->getDataPath());
        $this->filesystemScriptPatches = $processType($this->config->getScriptPath());

        $this->filesystemPatches = array_merge($this->filesystemSchemaPatches,$this->filesystemDataPatches,$this->filesystemScriptPatches);
        sort($this->filesystemPatches);

    }

    protected function createPatches(Array $patchList) {
        foreach ($patchList as $patchString) {
            $patch = new Patch($patchString);
            $this->patches[] = $patch;
        }
    }

    public function getUnappliedPatches() {
        $unappliedPatches = array_diff($this->filesystemPatches,$this->databasePatches);

        return $unappliedPatches;
    }


    public function getPatches() {
        return $this->patches;
    }

}
