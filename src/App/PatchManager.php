<?php

namespace uarsoftware\dbpatch\App;

class PatchManager implements PatchManagerInterface {

    protected $config;
    protected $patches;
    protected $database;

    protected $filesystemSchemaPatches;
    protected $filesystemDataPatches;
    protected $filesystemScriptPatches;

    protected $filesystemPatches;
    protected $databasePatches;

    protected $specificPatchesToApply;

    public function __construct(Config $config,DatabaseInterface $database) {
        $this->config = $config;
        $this->database = $database;

        $this->patches = array();



        $this->filesystemSchemaPatches = array();
        $this->filesystemDataPatches = array();
        $this->filesystemScriptPatches = array();

        $this->filesystemPatches = array();

        $this->resetSpecificPatchesToApply();

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
                $fullPath = $path . DIRECTORY_SEPARATOR . $file;
                if (!is_file($fullPath)) {
                    continue;
                }

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

    public function createPatchList(Array $patches) {
        $patchList = array();
        foreach ($patches as $patchString) {
            $patch = new Patch($patchString);
            array_push($patchList,$patch);
        }

        return $patchList;
    }

    public function addSpecificPatchesToApply(Array $patches) {
        $this->specificPatchesToApply = array_merge($this->specificPatchesToApply,$patches);
    }

    public function addSpecificPatchToApply(PatchInterface $patch) {
        $this->specificPatchesToApply[] = $patch;
    }

    public function resetSpecificPatchesToApply() {
        $this->specificPatchesToApply = array();
    }

    public function getUnappliedPatches() {
        $unappliedPatches = array_diff($this->filesystemPatches,$this->databasePatches);

        if (count($this->specificPatchesToApply) > 0) {
            $unappliedPatches = $this->filterSpecifiedPatches($unappliedPatches);
        }

        return $unappliedPatches;
    }

    protected function filterSpecifiedPatches($unappliedPatches) {

        $newUnappliedPatches = array();

        foreach ($this->specificPatchesToApply as $specificPatch) {
            if (in_array($specificPatch,$unappliedPatches)) {
                array_push($newUnappliedPatches,$specificPatch);
            }
        }

        sort($newUnappliedPatches);

        return $newUnappliedPatches;

    }
}
