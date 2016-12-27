<?php

namespace Carbon14\Command\Archive\Key;

use Carbon14\Carbon14;
use Smalot\Online\Online;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class SetCommand
 * @package Carbon14\Command\Archive\Key
 */
class SetCommand extends Command
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
        $this
          ->setName('archive:key:set')
          ->setDescription('Set an archive\'s encryption key')
          ->addArgument('archive', InputArgument::REQUIRED, 'Referring archive')
          ->addArgument('key', InputArgument::REQUIRED, 'The content of the key')
          ->addOption('safe', null, InputOption::VALUE_REQUIRED, 'Referring safe (fallback on .carbon14.yml file)')
          ->setHelp('')
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
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
        $key = $input->getArgument('key');

        // Authenticate and list all safe.
        $this->online->setToken($token);
        $this->online->storageC14()->enterKey($safe_uuid, $archive_uuid, $key);

        $output->writeln('<info>Archive\'s encryption key successfully updated</info>');
    }
}
