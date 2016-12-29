<?php

namespace Carbon14\Command;

use GuzzleHttp\Client;
use Humbug\SelfUpdate\Updater;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateCommand extends Carbon14Command
{
    /**
     * @var \GuzzleHttp\Client
     */
    protected $httpClient;

    /**
     * @inheritDoc
     */
    public function __construct($name = null)
    {
        parent::__construct($name);

        $this->httpClient = new Client();
    }

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this
          ->setName('self-update')
          ->addOption('check-only', null, InputOption::VALUE_NONE, '')
          ->setDescription('Updates Carbon14 to the latest version');
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $currentVersion = $this->getApplication()->getVersion();

        $updater = new Updater(null, false);
        $updater->setStrategy(Updater::STRATEGY_GITHUB);
        $updater->getStrategy()->setPackageName('smalot/carbon14');
        $updater->getStrategy()->setPharName('carbon14.phar');
        $updater->getStrategy()->setCurrentLocalVersion($currentVersion);
        $updater->getStrategy()->setStability('stable');

        try {
            if ($input->getOption('check-only')) {
                $result = $updater->hasUpdate();

                if ($result) {
                    $output->writeln(
                      sprintf(
                        '<comment>The current stable build available remotely is %s.</comment>',
                        $updater->getNewVersion()
                      )
                    );
                } elseif (false === $updater->getNewVersion()) {
                    $output->writeln('<error>There are no stable builds available.</error>');
                } else {
                    $output->writeln(
                      sprintf(
                        '<info>You are already using composer version %s.</info>',
                        $updater->getNewVersion()
                      )
                    );
                }

                return;
            } else {
                $result = $updater->update();

                if ($result) {
                    $new = $updater->getNewVersion();
                    $old = $updater->getOldVersion();

                    $output->writeln(
                      sprintf(
                        '<info>Successfully updated from %s to %s.</info>',
                        $old,
                        $new
                      )
                    );

                    exit(0);
                } else {
                    $output->writeln(
                      sprintf('<info>You are already using composer version %s.</info>', $updater->getNewVersion())
                    );
                }
            }
        } catch (\Exception $e) {
            $output->writeln('<error>Oups, something happened!</error>');
            exit(1);
        }
    }
}
