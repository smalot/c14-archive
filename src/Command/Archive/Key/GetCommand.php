<?php

namespace Carbon14\Command\Archive\Key;

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
 * Class GetCommand
 * @package Carbon14\Command\Archive\Key
 */
class GetCommand extends Carbon14Command
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
          ->setName('archive:key:get')
          ->setDescription('Get an archive\'s encryption key')
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

        $safe_uuid = $input->getOption('safe');
        if (empty($safe_uuid)) {
            $safe_uuid = $settings['default']['safe'];
        }

        if (empty($safe_uuid)) {
            throw new \InvalidArgumentException('Missing safe uuid');
        }

        $archive_uuid = $input->getArgument('archive');

        // Authenticate and list all safe.
        $this->online->setToken($token);
        $key = $this->online->storageC14()->getKey($safe_uuid, $archive_uuid);

        $output->writeln($key);
    }
}
