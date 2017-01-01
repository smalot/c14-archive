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
use Smalot\Online\Online;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class RunCommand
 * @package Carbon14\Command\Job
 */
class RunCommand extends Carbon14Command
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
          ->setName('job:run')
          ->setDescription('Run a job')
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
        $selectedSafe = !empty($settings['default']['safe']) ? $settings['default']['safe'] : '';

        // Authenticate and list all safe.
        $this->online->setToken($token);
        $safeList = $this->online->storageC14()->getSafeList();

        // Reverse list to display a natural order.
        if ($input->getOption('reverse')) {
            $safeList = array_reverse($safeList);
        }

        // Prepare output.
        $rows = [];
        foreach ($safeList as $safe) {
            $safe = $this->online->storageC14()->getSafeDetails($safe['uuid_ref']);

            $rows[] = [
              $safe['uuid_ref'],
              $safe['name'],
              $safe['description'],
              $safe['status'],
              preg_match('/locked/mis', $safe['description']) ? 'yes' : 'no',
              $selectedSafe == $safe['uuid_ref'] ? '*' : '',
            ];
        }

        // Render output.
        $io = new SymfonyStyle($input, $output);
        $io->table(
          ['uuid', 'label', 'description', 'status', 'locked', 'selected'],
          $rows
        );
    }
}
