<?php

namespace Carbon14\Command;

use GuzzleHttp\Client;
use Humbug\SelfUpdate\Updater;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateCommand extends Command
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
          ->setDescription('Updates carbon14.phar to the latest version');
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $updater = new Updater(null, false);
        $updater->setStrategy(Updater::STRATEGY_GITHUB);
        $updater->getStrategy()->setPackageName('smalot/carbon14');
        $updater->getStrategy()->setPharName('carbon14.phar');
        $updater->getStrategy()->setCurrentLocalVersion($this->getApplication()->getVersion());
        $updater->getStrategy()->setStability('any');

        try {
            if ($input->getOption('check-only')) {
                $result = $updater->hasUpdate();

                if ($result) {
                    echo(sprintf(
                      'The current stable build available remotely is: %s',
                      $updater->getNewVersion()
                    ));
                } elseif (false === $updater->getNewVersion()) {
                    echo('There are no stable builds available.');
                } else {
                    echo('You have the current stable build installed.');
                }

                return;
            } else {
                $result = $updater->update();
                $result ? exit('Updated!') : exit('No update needed!');
            }
        } catch (\Exception $e) {
            exit('Well, something happened! Either an oopsie or something involving hackers.');
        }
    }
}
