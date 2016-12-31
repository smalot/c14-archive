<?php

namespace Carbon14\Command\Archive;

use Carbon14\Carbon14;
use Carbon14\Command\Carbon14Command;
use Smalot\Online\Online;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
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

        /** @var Carbon14 $application */
        $application = $this->getApplication();
        /** @var array $settings */
        $settings = $application->getSettings();
        $token = $settings['token'];

        $safe_uuid = $input->getOption('safe');
        if (empty($safe_uuid)) {
            $safe_uuid = $settings['default']['safe'];
        }

        if (empty($safe_uuid)) {
            throw new \InvalidArgumentException('Missing safe uuid');
        }

        // Authenticate and list all safe.
        $this->online->setToken($token);
        $archiveList = $this->online->storageC14()->getArchiveList($safe_uuid);

        $rows = array();
        foreach ($archiveList as $archive) {
            $archive = $this->online->storageC14()->getArchiveDetails($safe_uuid, $archive['uuid_ref']);
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

        $io = new SymfonyStyle($input, $output);
        $io->table(
          array('uuid', 'label', 'description', 'parity', 'created', 'bucket', 'archival', 'status', 'locked'),
          $rows
        );
    }
}
