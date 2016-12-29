<?php

namespace Carbon14\Command;

use Carbon14\Carbon14;
use Smalot\Online\Online;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class InitCommand
 * @package Carbon14\Command
 */
class InitCommand extends Carbon14Command
{
    /**
     * @var Online
     */
    protected $online;

    /**
     * @var Carbon14
     */
    protected $application;

    /**
     * @var array
     */
    protected $safeList;

    /**
     * {@inheritdoc}
     */
    public function __construct($name = null)
    {
        parent::__construct($name);

        $this->online = new Online();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        parent::configure();

        $this
          ->setName('init')
          ->setDescription('Init Carbon14')
          ->addOption('token', null, InputOption::VALUE_REQUIRED)
          ->addOption('safe', null, InputOption::VALUE_REQUIRED, 'Default safe (uuid expected)')
          ->addOption('duration', null, InputOption::VALUE_REQUIRED, 'Default bucket window duration in days (2, 5 or 7)')
          ->setHelp('');
    }

    /**
     * {@inheritdoc}
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        parent::interact($input, $output);

        $settings = $this->getApplication()->getSettings();

        $helper = $this->getHelper('question');
        $io = new SymfonyStyle($input, $output);

        if (!$input->getOption('token')) {
            $default = isset($settings['token']) ? $settings['token'] : '';
            $question = new Question("<info>Please enter the security token</info> [<comment>$default</comment>]\n > ", $default);
            $question->setValidator(
              function ($answer) {
                  if (!trim($answer)) {
                      throw new \RuntimeException('The token is required');
                  }

                  $this->loadSafeList($answer, true);

                  return $answer;
              }
            );
            $question->setMaxAttempts(2);

            $token = $helper->ask($input, $output, $question);
            $input->setOption('token', $token);

            $io->newLine();
        }

        if (!$input->getOption('safe')) {
            $this->loadSafeList($input->getOption('token'));
            $default_safe = isset($settings['default']['safe']) ? $settings['default']['safe'] : '';
            $default = $position = 0;
            $rows = array();

            foreach ($this->safeList as $safe) {
                if ($safe['status'] == 'active') {
                    $rows[] = array(
                      $position,
                      $safe['uuid_ref'],
                      $safe['name'],
                      $safe['description'],
                    );

                    if ($safe['uuid_ref'] == $default_safe) {
                        $default = $position;
                    }

                    $position++;
                }
            }

            $io->table(array('#', 'uuid', 'label', 'description'), $rows);

            $question = new Question("<info>Select the default safe name</info> [<comment>$default</comment>]\n > ", $default);
            $safe = $helper->ask($input, $output, $question);
            $input->setOption('safe', $rows[$safe][1]);

            $io->newLine();
        }

        if (!$input->getOption('duration')) {
            $default = isset($settings['default']['duration']) ? $settings['default']['duration'] : 7;
            $question = new Question("<info>Select the default duration in days (2, 5 or 7)</info> [<comment>$default</comment>]\n > ", $default);
            $question->setValidator(
              function ($answer) {
                  if (!in_array($answer, array(2, 5, 7))) {
                      throw new \RuntimeException('Invalid value.');
                  }
                  return $answer;
              }
            );
            $question->setMaxAttempts(2);

            $duration = $helper->ask($input, $output, $question);
            $io->newLine();
            $input->setOption('duration', $duration);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);

        $token = $input->getOption('token');
        $safe = $input->getOption('safe');
        $duration = intval($input->getOption('duration'));

        // Check token if not already done.
        $this->loadSafeList($token);

        $settings = $this->getApplication()->getSettings();

        // Update settings.
        $settings['token'] = $token;
        $settings['default']['safe'] = $safe;
        $settings['default']['duration'] = $duration;

        $this->getApplication()->setSettings($settings);

        if ($this->getApplication()->saveConfigFile()) {
            $output->writeln('<info>Configuration saved.</info>');
        }
    }

    /**
     * @param string $token
     * @param bool $reload
     *
     * @return void
     */
    protected function loadSafeList($token, $reload = false)
    {
        if (is_null($this->safeList) || $reload) {
            $this->online->setToken($token);
            $this->safeList = $this->online->storageC14()->getSafeList();
        }
    }
}
