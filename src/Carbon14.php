<?php

namespace Carbon14;

use Carbon14\Source;
use Carbon14\Command;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArgvInput;

/**
 * Class Carbon14
 * @package Carbon14
 */
class Carbon14 extends Application
{
    use TraitConfig;

    /**
     * @var bool
     */
    protected $debug;

    /**
     * @var Job[]
     */
    protected $jobs;

    /**
     * @var Source\SourceAbstract[]
     */
    protected $sources = array();

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
     * Load default commands that should always be available.
     */
    protected function registerCommands()
    {
        $commands = array(
          new Command\InitCommand(),
          new Command\CronCommand(),
          new Command\UpdateCommand(),
          new Command\Job\ListCommand(),
          new Command\Archive\ListCommand(),
          new Command\Archive\JobListCommand(),
          new Command\Safe\CreateCommand(),
          new Command\Safe\DeleteCommand(),
          new Command\Safe\ListCommand(),
        );

        $this->addCommands($commands);
    }

    /**
     * Load default sources.
     */
    protected function registerSources()
    {
        $sources = array(
//          new Source\Direct(),
//          new Source\Mysql(),
//          new Source\Postgresql(),
//          new Source\Tarball(),
        );

        $this->addSources($sources);
    }

    /**
     * @param Source\SourceAbstract[] $sources
     */
    protected function addSources($sources)
    {
        /** @var Source\SourceAbstract $source */
        foreach ($sources as $source) {
            $this->addSource($source);
        }
    }

    /**
     * @param Source\SourceAbstract $source
     */
    protected function addSource(Source\SourceAbstract $source)
    {
        $this->sources[$source->getName()] = $source;
    }

    /**
     * @return Job[]
     */
    public function getJobs()
    {
        return $this->jobs;
    }

    /**
     * @return boolean
     */
    public function isDebug()
    {
        return $this->debug;
    }
}
