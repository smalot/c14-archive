<?php

namespace Carbon14\Command;

use Smalot\Online\Online;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;

/**
 * Class InitCommand
 * @package Carbon14\Command
 */
class InitCommand extends Command
{
    /**
     * @var Online
     */
    protected $online;

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
        $this
          ->setName('init')
          ->setDescription('Init backup C14')
          ->addOption('token', null, InputOption::VALUE_REQUIRED, '')
          ->addOption('safe', null, InputOption::VALUE_REQUIRED, '')
          ->addOption('duration', null, InputOption::VALUE_REQUIRED, 'Bucket window duration (2, 5 or 7), default: 7 days')
          ->setHelp('');
    }

    /**
     * {@inheritdoc}
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');

        if (!$input->getOption('token')) {
            $question = new Question('<info>Please enter the security token: </info>', '');
            $question->setValidator(
              function ($answer) {
                  if (!trim($answer)) {
                      throw new \RuntimeException('The token is required');
                  }

                  return $answer;
              }
            );
            $question->setMaxAttempts(2);

            $token = $helper->ask($input, $output, $question);
            $input->setOption('token', $token);
        }

        if (!$input->getOption('safe')) {
            $this->loadSafeList($input->getOption('token'));
            $choices = $safeList = array();
            foreach ($this->safeList as $safe) {
                if ($safe['status'] == 'active') {
                    $label = $safe['name'].' ('.$safe['uuid_ref'].')';
                    $choices[] = $label;
                    $safeList[$label] = $safe['uuid_ref'];
                }
            }
            $default = null;
            $question = new ChoiceQuestion('<info>Select the safe name: </info>', $choices, $default);
            $safe = $helper->ask($input, $output, $question);
            $input->setOption('safe', $safeList[$safe]);
        }


    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $token = $input->getOption('token');
        $output->writeln('token: ' . $token);

        $safe = $input->getOption('safe');
        $output->writeln('safe: ' . $safe);
    }

    /**
     * @param string $token
     *
     * @return void
     */
    protected function loadSafeList($token)
    {
        $this->online->setToken($token);
        $this->safeList = $this->online->storageC14()->getSafeList();
    }
}
