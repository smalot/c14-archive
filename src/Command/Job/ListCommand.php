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

namespace Carbon14\Command\Job;

use Carbon14\Command\Carbon14Command;
use Carbon14\Manager\JobManager;
use Carbon14\Model\FileCollection;
use Carbon14\Model\Job;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class ListCommand
 * @package Carbon14\Command\Job
 */
class ListCommand extends Carbon14Command
{
    /**
     * @inheritdoc
     */
    public function __construct($name = null)
    {
        parent::__construct($name);
    }

    /**
     *
     */
    protected function configure()
    {
        parent::configure();

        $this
          ->setName('job:list')
          ->setDescription('Get a list of jobs')
          ->addOption('directory', 'd', InputOption::VALUE_REQUIRED, 'Config directory - default to $HOME/carbon14')
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

        $directory = $input->getOption('directory');

        /** @var JobManager $jobManager */
        $jobManager = $this->get('job_manager');
        $jobList = $jobManager->loadFiles($directory);

        // Prepare output.
        $rows = [];
        /** @var Job $job */
        foreach ($jobList as $job) {
            $lastExecution = $job->getLastExecution();

            $rows[] = [
              $job->getCode(),
              $job->getName(),
              substr($job->getDescription(), 0, 25),
              $job->getSourceType(),
              $job->getStatus(),
              $lastExecution ? $lastExecution->format('Y-m-d H:i:s') : 'never',
            ];
        }

        // Render output.
        $io = new SymfonyStyle($input, $output);
        $io->table(
          ['code', 'name', 'description', 'type', 'status', 'last execution'],
          $rows
        );
    }
}
