<?php

namespace uarsoftware\dbpatch\Command;

use uarsoftware\dbpatch\App\Config;
use uarsoftware\dbpatch\App\ConfigManager;
use uarsoftware\dbpatch\App\Database;
use uarsoftware\dbpatch\App\DatabaseInterface;
use uarsoftware\dbpatch\App\PatchManager;
use uarsoftware\dbpatch\App\PatchManagerInterface;
use uarsoftware\dbpatch\App\Patch;
use uarsoftware\dbpatch\App\PatchInterface;
use uarsoftware\dbpatch\App\PatchEngine;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;



class PatchCommand extends Command {

    protected function configure() {
        $this->setName("patch");

        $this->setDescription("Adds patches to the database, either all available or specific ones.");

        $this->addArgument(
            'patches',
            InputArgument::IS_ARRAY | InputArgument::OPTIONAL,
            'List out each patch file name to apply (separate multiple patches with a space).'
        );

        $this->addOption(
            'config',
            null,
            InputOption::VALUE_REQUIRED,
            'The configuration to use for this operation.'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output) {

        $executableBasepath = getcwd();

        //$output->writeln("Executing patch");

        $cm = new ConfigManager();
        $config = $cm->determineConfig($input->getOption("config"),$executableBasepath);

        $output->writeln("<info>Using config: " . $config->getConfigFilePath() . "</info>");

        $db = new Database($config);

        $patchManager = new PatchManager($config,$db);

        // get any specific patches to apply
        // we'd filter on just these patches based on any that haven't been applied, determined by patch manager
        $patches = $input->getArgument("patches");
        $specificPatchesList = $patchManager->createPatchList($patches);
        $patchManager->addSpecificPatchesToApply($specificPatchesList);


        $patchEngine = new PatchEngine($config,$db,$output);
        $patchEngine->applyPatches($patchManager);
    }
}
