<?php

namespace uarsoftware\dbpatch\App;

interface PatchInterface {
    public function __construct($patchName);
    public function getBaseName();
}