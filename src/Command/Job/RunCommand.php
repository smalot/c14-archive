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
use Carbon14\EventSubscriber\EventLoggerSubscriber;
use Carbon14\Manager\JobManager;
use Carbon14\Manager\ProtocolManager;
use Carbon14\Manager\SourceManager;
use Carbon14\Model\File;
use Carbon14\Model\Job;
use Carbon14\Protocol\ProtocolAbstract;
use Smalot\Online\Online;
use Smalot\Online\OnlineException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\EventDispatcher\EventDispatcher;

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
          ->addArgument('code', InputArgument::REQUIRED, 'Job code')
          ->addOption('directory', 'd', InputOption::VALUE_REQUIRED, 'Config directory - default to $HOME/carbon14')
          ->addOption('safe', null, InputOption::VALUE_REQUIRED, 'Referring safe (fallback on .carbon14.yml file)')
          ->addOption('override', null, InputOption::VALUE_NONE, 'Override remote file if already exists')
          ->addOption('no-resume', null, InputOption::VALUE_NONE, 'Disable auto-resume on aborted transfer')
          ->setHelp('');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     * @throws
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);

        // Load job settings.
        $directory = $input->getOption('directory');
        $code = $input->getArgument('code');

        /** @var JobManager $jobManager */
        $jobManager = $this->get('job_manager');
        $jobList = $jobManager->loadFiles($directory);

        if (!isset($jobList[$code])) {
            throw new \InvalidArgumentException('Unknown job');
        }

        $job = $jobList[$code];

        // Prepare execution environment.
        $settings = $this->getSettings();
        $this->online->setToken($settings['token']);

        $safeIdentifier = $this->getSafeIdentifier($input);
        $output->writeln('<info>Safe detected:</info> '.$safeIdentifier);

        $archive = $this->findArchive($safeIdentifier);

        if (!$archive) {
            $duration = isset($settings['default']['duration']) ? $settings['default']['duration'] : 7;

            $archiveUuid = $this->createArchive($safeIdentifier, $duration);
            $output->writeln('<info>Archive created:</info> '.$archiveUuid);

            $start = microtime(true);
            $archive = $this->waitForActiveArchive($safeIdentifier, $archiveUuid);
            $output->writeln('<info>Archive available after</info> '.round(microtime(true) - $start).' seconds');

            if (!$archive) {
                $output->writeln('<error>Unable to find or create an archive</error>');

                return;
            }

        } else {
            $output->writeln('<info>Archive found:</info> '.$archive['uuid_ref']);
        }

        $this->registerProgressbar($output);

        /** @var SourceManager $sourceManager */
        $sourceManager = $this->get('source_manager');
        $source = $sourceManager->get($job->getSourceType());
        $fileCollection = $source->run($job->getSourceSettings());

        $output->writeln('<info>Source:</info> '.$fileCollection->count().' file(s)');

        $bucket = $this->online->storageC14()->getBucketDetails($safeIdentifier, $archive['uuid_ref']);

        /** @var ProtocolManager $protocolManager */
        $protocolManager = $this->get('protocol_manager');

        $credentials = [];
        foreach ($bucket['credentials'] as $credential) {
            $credentials[$credential['protocol']] = $credential;
        }

        if (isset($credentials['ftp'])) {
            $protocolType = 'ftp';
        } else {
            throw new \Exception('Protocol not supported');
        }

        /** @var ProtocolAbstract $protocol */
        $protocol = $protocolManager->get($protocolType);
        $protocol->open($credentials[$protocolType]);
        $protocol->transferFiles(
          $fileCollection,
          $input->getOption('override'),
          !$input->getOption('no-resume')
        );
        $protocol->close();

        $output->writeln('<info>Job done</info>');
    }

    /**
     * @param OutputInterface $output
     */
    protected function registerProgressbar(OutputInterface $output)
    {
        /** @var EventDispatcher $eventDispatcher */
        $eventDispatcher = $this->get('event_dispatcher');
        $eventLogger = new EventLoggerSubscriber($output);
        $eventDispatcher->addSubscriber($eventLogger);
    }

    /**
     * @param string $safeIdentifier
     * @return array|false
     * @throws \Exception
     */
    protected function findArchive($safeIdentifier)
    {
        $archives = $this->online->storageC14()->getArchiveList($safeIdentifier);

        // If found, return its ID.
        foreach ($archives as $archive) {
            if (preg_match(
                '/^([0-9]{4}-[0-9]{2}-[0-9]{2})/',
                $archive['name'],
                $match
              ) && $archive['status'] == 'active'
            ) {
                $created = strtotime($match[1]);

                // In the last 7 days.
                if (time() - $created < (7 * 86400)) {
                    $archive = $this->online->storageC14()->getArchiveDetails($safeIdentifier, $archive['uuid_ref']);

                    if (isset($archive['bucket']['status']) && $archive['bucket']['status'] == 'active') {
                        return $archive;
                    }
                }
            }
        }

        return false;
    }

    /**
     * @param string $safeIdentifier
     * @param int $duration
     * @return string|false
     * @throws OnlineException
     */
    protected function createArchive($safeIdentifier, $duration)
    {
        // If not found, so create it.
        $date = (date('N') == 1 ? time() : strtotime('previous monday'));
        $name = date('Y-m-d', $date);
        $description = 'automatically locked';

        $platforms = $this->online->storageC14()->getPlatformList();
        $platform = reset($platforms);
        $archiveUuid = false;
        $tries = 0;

        do {
            try {
                $again = false;
                $tmpName = $tries ? $name.' ('.$tries.')' : $name;

                $archiveUuid = $this->online->storageC14()->createArchive(
                  $safeIdentifier,
                  $tmpName,
                  $description,
                  null,
                  ['FTP'],
                  null,
                  $duration,
                  [$platform['id']]
                );
            } catch (OnlineException $e) {
                if ($e->getCode() == 10 && $tries++ <= 10) {
                    $again = true;
                } else {
                    throw $e;
                }
            }
        } while ($again);

        return $archiveUuid;
    }

    /**
     * @param string $safeIdentifier
     * @param string $archiveUuid
     * @return array|false
     * @throws \Exception
     */
    protected function waitForActiveArchive($safeIdentifier, $archiveUuid)
    {
        // Wait for archive ready, up to 1 minute.
        $tries = 0;
        do {
            sleep(1);
            $ready = false;

            $archive = $this->online->storageC14()->getArchiveDetails($safeIdentifier, $archiveUuid);

            if ($archive['status'] == 'active') {
                // Available.
                $ready = true;
            } elseif ($tries++ > 60) {
                // Too many tries.
                throw new \Exception('Timeout');
            }

        } while (!$ready);

        return $archive;
    }
}
