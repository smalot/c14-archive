<?php

/**
 * MIT License
 *
 * Copyright (C) 2016 - Sebastien Malot <sebastien@malot.fr>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace Carbon14;

use Carbon14\DependencyInjection\Compiler\CommandPass;
use Carbon14\DependencyInjection\Compiler\ProtocolPass;
use Carbon14\DependencyInjection\Compiler\SourcePass;
use Carbon14\Command;
use Carbon14\Manager\CommandManager;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Debug\Debug;
use Symfony\Component\Debug\DebugClassLoader;
use Symfony\Component\Debug\ErrorHandler;
use Symfony\Component\Debug\ExceptionHandler;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

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
        $env = $input->getParameterOption(['--env', '-e'], getenv('SYMFONY_ENV') ?: 'prod');
        $this->debug = getenv('SYMFONY_DEBUG') !== '0' && !$input->hasParameterOption(
            ['--no-debug', '']
          ) && $env !== 'prod';

        parent::__construct($name, $version);

//        if ($this->debug) {
        error_reporting(E_ALL);
        Debug::enable();
        ErrorHandler::register();
        ExceptionHandler::register();
        DebugClassLoader::enable();
//        }

        $this->registerContainer();
        $this->setDispatcher($this->container->get('event_dispatcher'));
        $this->registerCommands();
    }

    /**
     * Register dependency injection container.
     */
    protected function registerContainer()
    {
        $container = new ContainerBuilder();

        // Load root config file.
        $dir = dirname(__DIR__).DIRECTORY_SEPARATOR.'config';
        $loader = new YamlFileLoader($container, new FileLocator($dir));
        $loader->load('config.yml');

        $container->addCompilerPass(new CommandPass());
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
        /** @var CommandManager $commandManager */
        $commandManager = $this->getContainer()->get('command_manager');

        $this->addCommands($commandManager->getCommands());
    }

    /**
     * @return boolean
     */
    public function isDebug()
    {
        return $this->debug;
    }
}
