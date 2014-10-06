<?php

namespace uarsoftware\dbpatch\App;

interface PatchInterface {
    public function __construct($patchName);
    public function getBaseName();
    public function getPatchStatements();
    public function isRealFile();
    public function hasBeenApplied();
    public function setAsAppliedPatch();
    public function setAsUnpppliedPatch();
    public function setSuccessful();
    public function setFailed($code,$message);
    public function isSuccessful();
    public function getErrorCode();
    public function getErrorMessage();
}