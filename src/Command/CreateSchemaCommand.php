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
use Symfony\Component\Console\Question\Question;




class CreateSchemaCommand extends CreateCommand {

    protected function configure() {
        parent::configure();
        $this->setName("create:schema");
        $this->setDescription("Creates a new schema patch file");
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $this->generatePatch("createSchemaPatchFile",$input,$output);
    }
}
