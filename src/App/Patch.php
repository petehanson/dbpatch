<?php

namespace uarsoftware\dbpatch\App;

class Patch {

    protected $patchName;

    public function __construct($patchName) {
        $this->patchName = $patchName;
    }

    public function getBaseName() {
        return basename($this->patchName);
    }

    public function __toString() {
        return $this->getBaseName();
    }
}