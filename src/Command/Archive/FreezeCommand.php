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

namespace Carbon14\Command\Archive;

use Carbon14\Carbon14;
use Carbon14\Command\Carbon14Command;
use Smalot\Online\Online;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class FreezeCommand
 * @package Carbon14\Command\Archive
 */
class FreezeCommand extends Carbon14Command
{
    /**
     * @var Online
     */
    protected $online;

    /**
     * @inheritdoc
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
          ->setName('archive:freeze')
          ->setDescription('Archive files from temporary storage')
          ->addArgument('archive', InputArgument::REQUIRED, 'Referring archive')
          ->addOption('safe', null, InputOption::VALUE_REQUIRED, 'Referring safe (fallback on .carbon14.yml file)')
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

        $safeUuid = $input->getOption('safe');
        if (empty($safeUuid)) {
            $safeUuid = $settings['default']['safe'];
        }

        if (empty($safeUuid)) {
            throw new \InvalidArgumentException('Missing safe uuid');
        }

        $archiveUuid = $input->getArgument('archive');

        $this->online->setToken($token);
        $this->online->storageC14()->doArchive($safeUuid, $archiveUuid);

        $output->writeln('<info>Archive freeze successfully launched</info>');
    }
}
