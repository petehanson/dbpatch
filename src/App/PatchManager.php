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

                $patch = new Patch($fullPath);
                $patches[] = $patch;
            }

            return $patches;
        };

        $this->filesystemSchemaPatches = $processType($this->config->getSchemaPath());
        $this->filesystemDataPatches = $processType($this->config->getDataPath());
        $this->filesystemScriptPatches = $processType($this->config->getScriptPath());

        $this->filesystemPatches = array_merge($this->filesystemSchemaPatches,$this->filesystemDataPatches,$this->filesystemScriptPatches);
        //sort($this->filesystemPatches);
        usort($this->filesystemPatches,function($a,$b) {
            return strcmp($a->getBaseName(),$b->getBaseName());
        });

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

    public function applyPatch(PatchInterface $patch) {

        /*
        $sqlStatements = $patch->getPatchStatements();

        foreach ($sqlStatements as $sql) {
            $result = $this->database->executeQuery($sql);

            if ($result->status == false) {
                $patch->setFailed($result->errorCode,$result->errorMessage);
                throw new \exception($patch->getErrorCode() . ": " . $patch->getErrorMessage() . ",  executed query: " . $sql);
            }
        }

        $patch->setSuccessful();
        return $patch;
        */

        $patch->apply($this->database);
        return $patch;
    }

    public function createDataPatchFile($description,$extension = "sql",$timestamp = null) {
        $path = $this->config->getDataPath();

        return $this->createPatchFile($path,$description,$extension,$timestamp);
    }

    public function createSchemaPatchFile($description,$extension = "sql",$timestamp = null) {
        $path = $this->config->getSchemaPath();

        return $this->createPatchFile($path,$description,$extension,$timestamp);
    }

    public function createScriptPatchFile($description,$extension = "php",$timestamp = null) {
        $path = $this->config->getScriptPath();

        $initContent = <<<EOF
<?php

return true;
EOF;


        return $this->createPatchFile($path,$description,$extension,$timestamp,$initContent);
    }

    protected function createPatchFile($path,$description,$extension,$timestamp,$initContent = null) {
        $returnFileName = "";

        $description = $this->normalizeDescription($description);
        $dateTime = $this->normalizeTimestampPrefix($timestamp);

        $returnFileName = $dateTime . '.' . $description . '.' . $extension;


        $fullPath = $path . DIRECTORY_SEPARATOR . $returnFileName;
        touch($fullPath);

        if ($initContent !== null) {
            file_put_contents($fullPath,$initContent);
        }

        return $fullPath;
    }

    protected function normalizeTimestampPrefix($timestamp = null) {
        if ($timestamp === null) {
            $timestamp = time();
        }

        date_default_timezone_set($this->config->getStandardizedTimezone());
        return date("Ymd_His",$timestamp);
    }

    protected function normalizeDescription($description) {
        return preg_replace("/[^\w]/","_",strtolower($description));
    }
}

