<?php

namespace Carbon14\Command\Safe;

use Carbon14\Carbon14;
use Carbon14\Command\Carbon14Command;
use Carbon14\Config;
use Smalot\Online\Online;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class CreateCommand
 * @package Carbon14\Command\Safe
 */
class CreateCommand extends Carbon14Command
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
          ->setName('safe:create')
          ->setDescription('Create a safe')
          ->addArgument('name', InputArgument::REQUIRED, 'Safe name to create')
          ->addOption('description', null, InputOption::VALUE_REQUIRED, 'Description')
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
        parent::execute($input, $output);

        /** @var Carbon14 $application */
        $application = $this->getApplication();
        /** @var array $settings */
        $settings = $application->getSettings();
        $token = $settings['token'];

        // Authenticate and list all safe.
        $this->online->setToken($token);

        $name = $input->getArgument('name');
        $description = $input->getOption('description') ?: 'automatically locked';
        $result = $this->online->storageC14()->createSafe($name, $description);

        $output->writeln('<info>Safe created</info>');
        $output->writeln($result);
    }
}
