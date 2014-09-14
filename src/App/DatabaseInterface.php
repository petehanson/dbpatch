<?php

namespace uarsoftware\dbpatch\App;

interface DatabaseInterface {
    public function __construct(Config $config);
    public function getAppliedPatches();
}