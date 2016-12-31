<?php

/**
 * MIT License
 *
 * Copyright (C) 2016 - Sebastien Malot <sebastien@malot.fr>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace Carbon14\Command\Safe;

use Carbon14\Command\Carbon14Command;
use Smalot\Online\Online;
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
     * @inheritdoc
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

        // Confirm required.
        if (strtolower($input->getOption('confirm')) != 'y') {
            $output->writeln('<comment>Operation aborted</comment>');

            return;
        }

        // Load settings.
        $settings = $this->getSettings();
        $token = $settings['token'];

        // Authenticate.
        $this->online->setToken($token);

        // Check if locked.
        $safeIdentifier = $input->getArgument('uuid');
        $safe = $this->online->storageC14()->getSafeDetails($safeIdentifier);
        if (preg_match('/locked/mis', $safe['description'])) {
            $output->writeln("<error>Safe locked, can't be deleted</error>");

            return;
        }

        $this->online->storageC14()->deleteSafe($safeIdentifier);

        // Render output.
        $output->writeln('<info>Safe deleted</info>');
    }

    /**
     * @inheritdoc
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
