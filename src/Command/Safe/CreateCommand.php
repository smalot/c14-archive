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

namespace Carbon14\Command\Safe;

use Carbon14\Carbon14;
use Carbon14\Command\Carbon14Command;
use Carbon14\Config;
use Smalot\Online\Online;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class CreateCommand
 * @package Carbon14\Command\Safe
 */
class CreateCommand extends Carbon14Command
{
    /**
     * @var Online
     */
    protected $online;

    /**
     * {@inheritdoc}
     */
    public function __construct($name = null)
    {
        parent::__construct($name);

        $this->online = new Online();
    }

    /**
     *
     */
    protected function configure()
    {
        parent::configure();

        $this
          ->setName('safe:create')
          ->setDescription('Create a safe')
          ->addArgument('name', InputArgument::REQUIRED, 'Safe name to create')
          ->addOption('description', null, InputOption::VALUE_REQUIRED, 'Description')
          ->setHelp('');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);

        /** @var Carbon14 $application */
        $application = $this->getApplication();
        /** @var array $settings */
        $settings = $application->getSettings();
        $token = $settings['token'];

        // Authenticate and list all safe.
        $this->online->setToken($token);

        $name = $input->getArgument('name');
        $description = $input->getOption('description') ?: 'automatically locked';
        $result = $this->online->storageC14()->createSafe($name, $description);

        $output->writeln('<info>Safe created</info>');
        $output->writeln($result);
    }
}
