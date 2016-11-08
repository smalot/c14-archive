<?php

namespace Carbon14;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArgvInput;

/**
 * Class Carbon14
 * @package Carbon14
 */
class Carbon14 extends Application
{
    /**
     * @var bool
     */
    protected $debug;

    /**
     * @inheritDoc
     */
    public function __construct($name = 'UNKNOWN', $version = 'UNKNOWN')
    {
        $input = new ArgvInput();
        $env = $input->getParameterOption(array('--env', '-e'), getenv('SYMFONY_ENV') ?: 'prod');
        $this->debug = getenv('SYMFONY_DEBUG') !== '0' && !$input->hasParameterOption(
            array('--no-debug', '')
          ) && $env !== 'prod';

        parent::__construct($name, $version);

        $this->registerCommands();
    }

    /**
     * Gets the default commands that should always be available.
     *
     * @return Command[] An array of default Command instances
     */
    protected function registerCommands()
    {
        $commands = array(
          new \Carbon14\Command\InitCommand(),
          new \Carbon14\Command\CronCommand(),
          new \Carbon14\Command\UpdateCommand(),
          new \Carbon14\Command\Job\ListCommand(),
        );

        $this->addCommands($commands);
    }

    /**
     * @return boolean
     */
    public function isDebug()
    {
        return $this->debug;
    }
}
