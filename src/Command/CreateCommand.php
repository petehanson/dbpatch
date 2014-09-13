<?php

namespace uarsoftware\dbpatch\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CreateCommand extends Command {

    protected function configure() {
        $this->setName("test");
        $this->setDescription("A test command");
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $output->writeln("Hello World");
    }
}
