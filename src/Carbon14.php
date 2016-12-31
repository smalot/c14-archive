<?php

namespace Carbon14;

use Carbon14\DependencyInjection\Compiler\ProtocolPass;
use Carbon14\DependencyInjection\Compiler\SourcePass;
use Carbon14\Source;
use Carbon14\Command;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class Carbon14
 * @package Carbon14
 */
class Carbon14 extends Application
{
    use TraitConfigFile;

    /**
     * @var bool
     */
    protected $debug;

    /**
     * @var ContainerInterface
     */
    protected $container;

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

        $this->loadContainer();
        $this->setDispatcher($this->container->get('event_dispatcher'));
        $this->registerCommands();
    }

    /**
     *
     */
    protected function loadContainer()
    {
        $container = new ContainerBuilder();

        // Load root config file.
        $dir = dirname(__DIR__).DIRECTORY_SEPARATOR.'config';
        $loader = new YamlFileLoader($container, new FileLocator($dir));
        $loader->load('config.yml');

        $container->addCompilerPass(new ProtocolPass());
        $container->addCompilerPass(new SourcePass());

        // Compile container.
        $container->compile();

        $this->setContainer($container);
    }

    /**
     * @param ContainerInterface $container
     * @return $this
     */
    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;

        return $this;
    }

    /**
     * @return ContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
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
          new Command\Archive\FreezeCommand(),
          new Command\Archive\ListCommand(),
          new Command\Archive\RestoreCommand(),
          new Command\Archive\Job\ListCommand(),
          new Command\Archive\Key\DeleteCommand(),
          new Command\Archive\Key\GetCommand(),
          new Command\Archive\Key\SetCommand(),
          new Command\Safe\CreateCommand(),
          new Command\Safe\DeleteCommand(),
          new Command\Safe\ListCommand(),
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
