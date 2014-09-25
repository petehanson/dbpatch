<?php
namespace uarsoftware\dbpatch\App;

use Symfony\Component\Console\Output\OutputInterface;

class PatchEngine {

    protected $db;
    protected $config;
    protected $output;

    public function __construct(Config $config, DatabaseInterface $db,OutputInterface $output) {
        $this->db = $db;
        $this->config = $config;
        $this->output = $output;
    }

    public function applyPatches(PatchManager $patchManager) {
        $patches = $patchManager->getPatches();
    }

}
