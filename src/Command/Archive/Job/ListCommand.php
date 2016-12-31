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

namespace Carbon14\Command\Archive\Job;

use Carbon14\Command\Carbon14Command;
use Smalot\Online\Online;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class ListCommand
 * @package Carbon14\Command\Archive\Job
 */
class ListCommand extends Carbon14Command
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
          ->setName('archive:job:list')
          ->setDescription('List all jobs of an archive')
          ->addArgument('archive', InputArgument::REQUIRED, 'Referring archive')
          ->addOption('safe', null, InputOption::VALUE_REQUIRED, 'Referring safe (fallback on .carbon14.yml file)')
          ->addOption('reverse', null, InputOption::VALUE_NONE, 'Reverse list')
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

        // Load settings.
        $settings = $this->getSettings();
        $token = $settings['token'];

        // Load basic identifiers.
        $safeUuid = $this->getSafeIdentifier($input);
        $archiveUuid = $input->getArgument('archive');

        $this->online->setToken($token);
        $jobs = $this->online->storageC14()->getJobList($safeUuid, $archiveUuid);

        // Reverse list to display a natural order.
        if ($input->getOption('reverse')) {
            $jobs = array_reverse($jobs);
        }

        // Prepare output.
        $rows = array();
        foreach ($jobs as $job) {
            $started = new \DateTime($job['start']);
            $duration = 0;
            if (!empty($job['end'])) {
                $ended = new \DateTime($job['end']);
                $duration = max(0, $ended->getTimestamp() - $started->getTimestamp());
            }

            $rows[] = array(
              $job['uuid_ref'],
              (isset($job['parent_job']) ? '- ' : '').str_replace('_', ' ', $job['type']),
              $started->format('Y-m-d H:i:s'),
              (!empty($job['end']) ? $ended->format('Y-m-d H:i:s') : ''),
              $job['progress'].'%',
              ($duration ? $duration.'s' : '-'),
              $job['status'],
            );
        }

        // Render output.
        $io = new SymfonyStyle($input, $output);
        $io->table(array('uuid', 'type', 'start', 'end', 'progress', 'duration', 'status'), $rows);
    }
}
