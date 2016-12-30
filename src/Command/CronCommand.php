<?php

namespace Carbon14\Command;

use Carbon14\EventSubscriber\EventLoggerSubscriber;
use Carbon14\Model\File;
use Carbon14\Model\FileCollection;
use Carbon14\Protocol\Ftp;
use Carbon14\Source\Direct;
use GuzzleHttp\Exception\ClientException;
use Smalot\Online\Online;
use Smalot\Online\OnlineException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Finder\Finder;

/**
 * Class CronCommand
 * @package Carbon14\Command
 */
class CronCommand extends Carbon14Command
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
          ->setName('cron')
          ->addOption('safe', null, InputOption::VALUE_REQUIRED, 'Referring safe (fallback on .carbon14.yml file)')
          ->addOption('no-resume', null, InputOption::VALUE_NONE, 'Disable auto-resume')
          ->setDescription('Cron process')
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

        $settings = $this->getApplication()->getSettings();
        $this->online->setToken($settings['token']);

        $safe_uuid = $input->getOption('safe');
        if (empty($safe_uuid)) {
            $safe_uuid = $settings['default']['safe'];
        }

        if (empty($safe_uuid)) {
            throw new \InvalidArgumentException('Missing safe uuid');
        }

        $archive = $this->findArchive($safe_uuid);

        if (!$archive) {
            $duration = isset($settings['default']['duration']) ? $settings['default']['duration'] : 7;

            $archive_uuid = $this->createArchive($safe_uuid, $duration);
            $output->writeln('Archive created: ' . $archive_uuid);

            $start = microtime(true);
            $archive = $this->waitForActiveArchive($safe_uuid, $archive_uuid);
            $output->writeln('Archive available after '.round(microtime(true) - $start).' seconds');

            if (!$archive) {
                $output->writeln('<error>Unable to find or create an archive</error>');
                return;
            }

        } else {
            $output->writeln('Archive found: ' . $archive['uuid_ref']);
        }

        $bucket = $this->online->storageC14()->getBucketDetails($safe_uuid, $archive['uuid_ref']);

        $source = new Direct([]);

        $finder = new Finder();
        $finder->files()->in('/data/www/carbon14/src');

        $source->addFiles($finder);

//        $source->findFiles();
//        $file = new File('/data/isos/debian-8.3.0-amd64-netinst (1).iso');
//        $source->addFile($file);
//        $file = new File('/data/solr/LICENSE.txt');
//        $source->addFile($file);

        /** @var EventDispatcher $eventDispatcher */
        $eventDispatcher = $this->getApplication()->getEventDispatcher();
        $eventLogger = new EventLoggerSubscriber($output);
        $eventDispatcher->addSubscriber($eventLogger);

        foreach ($bucket['credentials'] as $credential) {
            if ($credential['protocol'] == 'ftp') {
                $protocol = new Ftp($eventDispatcher);
                $protocol->connect($credential);
                $protocol->transfertFiles($source->getFileCollection(), !$input->getOption('no-resume'));
                $protocol->close();
            }
        }

        $output->writeln('done');
    }

    /**
     * @param string $safe_uuid
     * @return array|false
     * @throws \Exception
     */
    protected function findArchive($safe_uuid)
    {
        $archives = $this->online->storageC14()->getArchiveList($safe_uuid);

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
                    $archive = $this->online->storageC14()->getArchiveDetails($safe_uuid, $archive['uuid_ref']);

                    if (isset($archive['bucket']['status']) && $archive['bucket']['status'] == 'active') {
                        return $archive;
                    }
                }
            }
        }

        return false;
    }

    /**
     * @param string $safe_uuid
     * @param int $duration
     * @return string|false
     * @throws OnlineException
     */
    protected function createArchive($safe_uuid, $duration)
    {
        // If not found, so create it.
        $date = (date('N') == 1 ? time() : strtotime('previous monday'));
        $name = date('Y-m-d', $date);
        $description = 'locked';

        $platforms = $this->online->storageC14()->getPlatformList();
        $platform = reset($platforms);
        $archive_uuid = false;

        $tries = 0;
        do {
            try {
                $again = false;
                $tmp_name = $tries ? $name.' ('.$tries.')' : $name;

                $archive_uuid = $this->online->storageC14()->createArchive(
                  $safe_uuid,
                  $tmp_name,
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

        return $archive_uuid;
    }

    /**
     * @param string $safe_uuid
     * @param string $archive_uuid
     * @return array|false
     * @throws \Exception
     */
    protected function waitForActiveArchive($safe_uuid, $archive_uuid)
    {
        // Wait for archive ready, up to 1 minute.
        $tries = 0;
        do {
            sleep(1);
            $ready = false;

            $archive = $this->online->storageC14()->getArchiveDetails($safe_uuid, $archive_uuid);

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
