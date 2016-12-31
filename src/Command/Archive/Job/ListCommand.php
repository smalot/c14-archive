<?php

namespace Carbon14\Command\Archive\Job;

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
 * @package Carbon14\Command\Archive\Job
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

        $archive_uuid = $input->getArgument('archive');

        $this->online->setToken($token);
        $jobs = $this->online->storageC14()->getJobList($safe_uuid, $archive_uuid);

        if ($input->getOption('reverse')) {
            $jobs = array_reverse($jobs);
        }

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

        $io = new SymfonyStyle($input, $output);
        $io->table(array('uuid', 'type', 'start', 'end', 'progress', 'duration', 'status'), $rows);
    }
}
