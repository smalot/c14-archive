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

use Carbon14\Command\Carbon14Command;
use Smalot\Online\Online;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class ListCommand
 * @package Carbon14\Command\Archive
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
          ->setName('archive:list')
          ->setDescription('List all archives')
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

        // Load settings.
        $settings = $this->getSettings();
        $token = $settings['token'];

        // Load basic identifiers.
        $safeUuid = $this->getSafeIdentifier($input);

        // Authenticate and list all safe.
        $this->online->setToken($token);
        $archiveList = $this->online->storageC14()->getArchiveList($safeUuid);

        $rows = array();
        foreach ($archiveList as $archive) {
            $archive = $this->online->storageC14()->getArchiveDetails($safeUuid, $archive['uuid_ref']);
            $created = new \DateTime($archive['creation_date']);

            $bucket = $archive['bucket'];
            $archival = !empty($archive['bucket']['archival_date']) ? new \DateTime(
              $archive['bucket']['archival_date']
            ) : null;

            $rows[] = array(
              $archive['uuid_ref'],
              $archive['name'],
              $archive['description'],
              $archive['parity'],
              $created->format('Y-m-d H:i:s'),
              $bucket['status'],
              ($archival ? $archival->format('Y-m-d H:i:s') : ''),
              $archive['status'],
              preg_match('/locked/mis', $archive['description']) ? 'yes' : 'no',
            );
        }

        // Render output.
        $io = new SymfonyStyle($input, $output);
        $io->table(
          array('uuid', 'label', 'description', 'parity', 'created', 'bucket', 'archival', 'status', 'locked'),
          $rows
        );
    }
}
