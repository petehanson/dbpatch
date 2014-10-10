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

    public function determineConfig($dbPatchBasePath,$configOption) {

    }

    public function applyPatches(PatchManager $patchManager) {
        // get the unapplied patches

        $unappliedPatches = $patchManager->getUnappliedPatches();

        // apply each patch to the DB

        $appliedPatches = array();

        foreach ($unappliedPatches as $patch) {

            $this->output->writeln("Applying patch: " . $patch->getBasename());

            $patch = $patchManager->applyPatch($patch);



            if ($patch->isSuccessful()) {
                $this->db->recordPatch($patch);
                array_push($appliedPatches,$patch);

                $this->output->writeln("Patch " . $patch->getBaseName() . " successful");
            }

        }

        return count($appliedPatches);
    }

}
