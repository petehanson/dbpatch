<?php

namespace uarsoftware\dbpatch\App;

interface PatchApplierInterface {
    public function getErrorCode();
    public function getErrorMessage();
    public function getStatementCount();
}
