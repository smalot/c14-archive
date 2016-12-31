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

namespace Carbon14\Command;

use Carbon14\Carbon14;
use Smalot\Online\Online;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
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
     * @inheritdoc
     */
    public function __construct($name = null)
    {
        parent::__construct($name);

        $this->online = new Online();
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        parent::configure();

        $this
          ->setName('init')
          ->setDescription('Init Carbon14')
          ->addOption('token', null, InputOption::VALUE_REQUIRED)
          ->addOption('safe', null, InputOption::VALUE_REQUIRED, 'Default safe (uuid expected)')
          ->addOption(
            'duration',
            null,
            InputOption::VALUE_REQUIRED,
            'Default bucket window duration in days (2, 5 or 7)'
          )
          ->setHelp('');
    }

    /**
     * @inheritdoc
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        parent::interact($input, $output);

        $settings = $this->getSettings();

        $helper = $this->getHelper('question');
        $io = new SymfonyStyle($input, $output);

        if (!$input->getOption('token')) {
            $default = isset($settings['token']) ? $settings['token'] : '';
            $question = new Question(
              "<info>Please enter the security token</info> [<comment>$default</comment>]\n > ",
              $default
            );
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

            $question = new Question(
              "<info>Select the default safe name</info> [<comment>$default</comment>]\n > ",
              $default
            );
            $safe = $helper->ask($input, $output, $question);
            $input->setOption('safe', $rows[$safe][1]);

            $io->newLine();
        }

        if (!$input->getOption('duration')) {
            $default = isset($settings['default']['duration']) ? $settings['default']['duration'] : 7;
            $question = new Question(
              "<info>Select the default duration in days (2, 5 or 7)</info> [<comment>$default</comment>]\n > ",
              $default
            );
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
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);

        $token = $input->getOption('token');
        $safe = $input->getOption('safe');
        $duration = intval($input->getOption('duration'));
        $settings = $this->getSettings();

        // Check token if not already done.
        $this->loadSafeList($token);

        // Update settings.
        $settings['token'] = $token;
        $settings['default']['safe'] = $safe;
        $settings['default']['duration'] = $duration;

        // Store updated config.
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
