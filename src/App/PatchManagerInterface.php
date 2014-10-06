<?php

namespace uarsoftware\dbpatch\App;

interface PatchManagerInterface {
    public function __construct(Config $config,DatabaseInterface $db);
    public function createPatchList(Array $patches);
    public function addSpecificPatchesToApply(Array $patches);
    public function addSpecificPatchToApply(PatchInterface $patch);
    public function resetSpecificPatchesToApply();
    public function getUnappliedPatches();
    public function applyPatch(PatchInterface $patch);
}