<?php
namespace uarsoftware\dbpatch\App;

use Symfony\Component\Console\Output\OutputInterface;

class PatchEngine {

    protected $config;
    protected $output;

    public function __construct(Config $config, OutputInterface $output) {
        $this->config = $config;
        $this->output = $output;
    }

    public function applyPatches(PatchManager $patchManager) {
        $db = new Database($this->config);

        $patches = $patchManager->getPatches();

        foreach ($patches as $patch) {
            $this->config $patch->getBaseName();
        }

    }

}
