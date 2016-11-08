<?php

namespace Carbon14\Command\Job;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ListCommand
 * @package Carbon14\Command\Job
 */
class ListCommand extends Command
{
    /**
     *
     */
    protected function configure()
    {
        $this
          ->setName('job:list')
          ->setDescription('List all jobs')
          ->setHelp('')
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // ...
    }
}
