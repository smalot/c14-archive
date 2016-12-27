<?php

namespace Carbon14\Command\Safe;

use Carbon14\Carbon14;
use Smalot\Online\Online;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class ListCommand
 * @package Carbon14\Command\Safe
 */
class ListCommand extends Command
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
          ->setName('safe:list')
          ->setDescription('List all safes')
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

        // Authenticate and list all safe.
        $this->online->setToken($token);
        $safeList = $this->online->storageC14()->getSafeList();

        $rows = array();
        foreach ($safeList as $safe) {
            $safe = $this->online->storageC14()->getSafeDetails($safe['uuid_ref']);

            $rows[] = array(
              $safe['uuid_ref'],
              $safe['name'],
              $safe['description'],
              $safe['status'],
              preg_match('/locked/mis', $safe['description']) ? 'yes' : 'no',
            );
        }

        $io = new SymfonyStyle($input, $output);
        $io->table(array('uuid', 'label', 'description', 'status', 'locked'), $rows);
    }
}
