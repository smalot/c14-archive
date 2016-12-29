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
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class DeleteCommand
 * @package Carbon14\Command\Safe
 */
class DeleteCommand extends Carbon14Command
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
          ->setName('safe:delete')
          ->setDescription('Delete a safe (archives included)')
          ->addArgument('uuid', InputArgument::REQUIRED, 'The identifier of the safe')
          ->addOption('confirm', null, InputOption::VALUE_REQUIRED, 'Confirm (y/n)')
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

        // Confirm required.
        if (strtolower($input->getOption('confirm')) != 'y') {
            $output->writeln('<comment>Operation aborted</comment>');

            return;
        }

        /** @var Carbon14 $application */
        $application = $this->getApplication();
        /** @var array $settings */
        $settings = $application->getSettings();
        $token = $settings['token'];
        $uuid = $input->getArgument('uuid');

        // Authenticate and list all safe.
        $this->online->setToken($token);

        $safe = $this->online->storageC14()->getSafeDetails($uuid);
        if (preg_match('/locked/mis', $safe['description'])) {
            $output->writeln("<error>Safe locked, can't be deleted</error>");
            return;
        }

        $this->online->storageC14()->deleteSafe($uuid);

        $output->writeln('<info>Safe deleted</info>');
    }

    /**
     * {@inheritdoc}
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        parent::interact($input, $output);

        $helper = $this->getHelper('question');
        $io = new SymfonyStyle($input, $output);

        if (!$input->getOption('confirm')) {
            $question = new Question(
              "<info>Please confirm</info> [y/N]\n > ",
              'n'
            );
            $question->setValidator(
              function ($answer) {
                  $answer = strtolower(trim($answer));
                  if (!$answer) {
                      throw new \RuntimeException('The answer is required');
                  }
                  if (!in_array($answer, ['y', 'n'])) {
                      throw new \RuntimeException('Please answer Y or N');
                  }

                  return $answer;
              }
            );
            $question->setMaxAttempts(2);

            $answer = $helper->ask($input, $output, $question);
            $input->setOption('confirm', $answer);

            $io->newLine();
        }
    }
}
