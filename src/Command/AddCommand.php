<?php

namespace uarsoftware\dbpatch\Command;

use uarsoftware\dbpatch\App\Config;
use uarsoftware\dbpatch\App\ConfigManager;
use uarsoftware\dbpatch\App\PatchEngine;
use uarsoftware\dbpatch\App\PatchManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class AddCommand extends Command {

    protected function configure() {
        $this->setName("add");

        $this->setDescription("Adds one or more patches to the database");

        $this->addArgument(
            'patches',
            InputArgument::IS_ARRAY | InputArgument::REQUIRED,
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
        $output->writeln("Add Patch");

        $patches = $input->getArgument("patches");

        $config = new Config("test","mysql","localhost","test","root","root");
        $config->setBasePath(dirname(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR . 'test');

        $patchEngine = new PatchEngine($config,$output);

        $patchEngine->applyPatches(new PatchManager($patches));


    }
}
