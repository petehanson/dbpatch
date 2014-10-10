<?php

namespace uarsoftware\dbpatch\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use uarsoftware\dbpatch\App\ConfigManager;
use uarsoftware\dbpatch\App\Config;

class InitCommand extends Command {

    protected function configure() {
        $this->setName("init");
        $this->setDescription("Creates the folder arrangement for a new configuration and a base config file");

        $this->addArgument(
            'configpath',
            InputArgument::REQUIRED,
            'Relative path from this script\'s location for the folder to create and where to place the sql folders and config file.'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output) {

        $executableBasepath = getcwd();

        $cm = new ConfigManager();
        $path = $cm->createConfigFolders($executableBasepath,$input->getArgument("configpath"));
        $output->writeln("Created configuration at {$path}");
    }
}
