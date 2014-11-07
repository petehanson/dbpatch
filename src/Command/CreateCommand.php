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




class CreateCommand extends Command {

    protected function configure() {
        $this->setName("create:schema");
        $this->setDescription("Creates a new schema patch file");

        $this->addOption(
            'config',
            null,
            InputOption::VALUE_REQUIRED,
            'The configuration to use for this operation.'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output) {

        $helper = $this->getHelper('question');
        $question = new Question('Please provide a description for the patch: ', '');
        $description = $helper->ask($input, $output, $question);

        $executableBasepath = getcwd();

        $cm = new ConfigManager();
        $config = $cm->determineConfig($input->getOption("config"),$executableBasepath);

        $output->writeln("<info>Using config: " . $config->getConfigFilePath() . "</info>");

        $db = new Database($config);

        $patchManager = new PatchManager($config,$db);

        $newPatchFile = $patchManager->createSchemaPatchFile($description);

        $output->writeln("Created Patch File: " . $newPatchFile);

    }
}
