<?php

namespace uarsoftware\dbpatch\App;

abstract class PatchApplierAbstract implements PatchApplierInterface {

    protected $status = null;
    protected $errorCode = null;
    protected $errorMessage = null;
    protected $statementCount = 0;

    public function __construct() {
    }

    abstract public function apply(PatchInterface $patch,DatabaseInterface $db);

    public function getErrorCode() {
        return $this->errorCode;
    }

    public function getErrorMessage() {
        return $this->errorMessage;
    }

    public function getStatementCount() {
        return $this->statementCount;
    }
}
