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
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class Carbon14Command
 * @package Carbon14\Command
 */
abstract class Carbon14Command extends Command
{
    /**
     *
     */
    protected function configure()
    {
        $this->addOption('config-file', 'f', InputOption::VALUE_REQUIRED, 'Config file');
    }

    /**
     * @return Carbon14
     */
    public function getApplication()
    {
        return parent::getApplication();
    }

    /**
     * @return array
     */
    protected function getSettings()
    {
        return $this->getApplication()->getSettings();
    }

    /**
     * @param InputInterface $input
     * @param bool $throwsException
     * @return string
     * @throws
     */
    protected function getSafeIdentifier(InputInterface $input, $throwsException = true)
    {
        $safe = $input->hasOption('safe') ? $input->getOption('safe') : '';

        if (empty($safe)) {
            $settings = $this->getSettings();
            $safe = $settings['default']['safe'];
        }

        if ($throwsException && empty($safe)) {
            throw new \InvalidArgumentException('Missing safe uuid');
        }

        return $safe;
    }

    /**
     * @inheritDoc
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        if ($input->hasOption('config-file')) {
            $this->getApplication()->loadConfigFile($input->getOption('config-file'));
        }
    }

    /**
     * @inheritDoc
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        parent::interact($input, $output);
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Template.
    }

    /**
     * @param string $id
     * @param int $invalidBehavior
     * @return object
     */
    public function get($id, $invalidBehavior = ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE)
    {
        return $this->getApplication()->getContainer()->get($id, $invalidBehavior);
    }
}
