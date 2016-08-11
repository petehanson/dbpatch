<?php

namespace uarsoftware\dbpatch\App;

class Patch implements PatchInterface{

    protected $patchName;
    protected $isAppliedPatch;

    protected $patchSuccess;
    protected $errorCode;
    protected $errorMessage;


    protected $isSQL = false;
    protected $isPHP = false;

    protected $patchApplier = null;


    public function __construct($patchName) {
        $this->patchName = $patchName;
        $this->setAsUnpppliedPatch();

        $this->patchSuccess = false;
        $this->errorCode = "";
        $this->errorMessage = "";

        $this->determinePatchType();
    }

    public function getPatchName() {
        return $this->patchName;
    }

    public function getBaseName() {
        return basename($this->patchName);
    }

    public function __toString() {
        return $this->getBaseName();
    }

    public function setAsAppliedPatch() {
        $this->isAppliedPatch = true;
    }

    public function setAsUnpppliedPatch() {
        $this->isAppliedPatch = false;
    }

    public function hasBeenApplied() {
        return $this->isAppliedPatch;
    }

    public function isRealFile() {
        if (file_exists($this->patchName)) {
            return true;
        } else {
            return false;
        }
    }

    public function isSuccessful() {
        return $this->patchSuccess;
    }

    public function setSuccessful() {
        $this->patchSuccess = true;
    }

    public function setFailed($code,$message) {
        $this->patchSuccess = false;
        $this->errorCode = $code;
        $this->errorMessage = $message;
    }

    public function getErrorCode() {
        return $this->errorCode;
    }

    public function getErrorMessage() {
        return $this->errorMessage;
    }


    public function getPatchContents() {
        $fileContents = "";
        if ($this->isRealFile()) {
            $fileContents = file_get_contents($this->patchName);
        }

        return $fileContents;
    }

    public function getPatchApplier() {
        return $this->patchApplier;
    }

    protected function determinePatchType() {
        $parts = pathinfo($this->patchName);

        switch ($parts['extension']) {
            case "sql":
                $this->patchApplier = new PatchApplierSql();
                $this->patchApplier->setStatementParser(new StatementParser());
                $this->isSQL = true;
                break;

            case "php":
                $this->patchApplier = new PatchApplierPhp();
                $this->isPHP = true;
                break;
        }
    }

    public function apply(DatabaseInterface $db) {
        $status = $this->patchApplier->apply($this,$db);

        if ($status == true) {
            $this->setSuccessful();
        } else {
            $this->setFailed($this->patchApplier->getErrorCode(),$this->patchApplier->getErrorMessage());
            throw new \exception($this->getErrorCode() . ": " . $this->getErrorMessage());
        }
    }
}