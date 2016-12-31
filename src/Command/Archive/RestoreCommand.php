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
 * Class RestoreCommand
 * @package Carbon14\Command\Archive
 */
class RestoreCommand extends Carbon14Command
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
          ->setName('archive:restore')
          ->setDescription('Unarchive files into temporary storage')
          ->addArgument('archive', InputArgument::REQUIRED, 'Referring archive')
          ->addOption('safe', null, InputOption::VALUE_REQUIRED, 'Referring safe (fallback on .carbon14.yml file)')
          ->addOption('key', null, InputOption::VALUE_REQUIRED, 'The content of the key')
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
        $key = $input->getOption('key');

        $this->online->setToken($token);

        $locations = $this->online->storageC14()->getLocationList($safe_uuid, $archive_uuid);
        $location = reset($locations);

        // Default protocol.
        $protocols = ['FTP'];

        $this->online->storageC14()->doUnarchive(
          $safe_uuid,
          $archive_uuid,
          $location['uuid_ref'],
          true,
          "$key",
          $protocols
        );

        $output->writeln('<info>Archive unarchive successfully launched</info>');
    }
}
