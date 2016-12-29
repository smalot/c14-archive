<?php

namespace Carbon14\Command;

use Carbon14\Carbon14;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class Carbon14Command
 * @package Carbon14\Command
 */
abstract class Carbon14Command extends Command
{
    /**
     *
     */
    protected function configure()
    {
        $this->addOption('config-file', 'f', InputOption::VALUE_REQUIRED, 'Config file');
    }

    /**
     * @return Carbon14
     */
    public function getApplication()
    {
        return parent::getApplication();
    }

    /**
     * @inheritDoc
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->getApplication()->loadConfigFile($input->getOption('config-file'));
    }

    /**
     * @inheritDoc
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        parent::interact($input, $output);
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Template.
    }
}
