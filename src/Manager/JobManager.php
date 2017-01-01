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

namespace Carbon14\Manager;

use Carbon14\Model\Job;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;

/**
 * Class JobManager
 * @package Carbon14\Manager
 */
class JobManager
{
    /**
     * Default folder in the user's HOME.
     */
    const JOB_FOLDER = '.carbon14';

    /**
     * ConfigManager constructor.
     * @param SourceManager $sourceManager
     */
    public function __construct(SourceManager $sourceManager)
    {
        $this->sourceManager = $sourceManager;
    }

    /**
     * @param string $content
     * @return Job
     */
    public function parse($content)
    {
        $config = Yaml::parse($content);

        $config += [
          'name' => '',
          'description' => '',
          'status' => 'active',
          'last_execution' => '',
          'source' => [],
        ];

        $config['source'] += [
          'type' => '',
          'settings' => [],
        ];

        $lastExecution = $config['last_execution'] ? new \DateTime($config['last_execution']) : null;

        $job = new Job();
        $job->setName($config['name']);
        $job->setDescription($config['description']);
        $job->setStatus($config['status']);
        $job->setLastExecution($lastExecution);
        $job->setSourceType($config['source']['type']);
        $job->setSourceSettings($config['source']['settings']);

        return $job;
    }

    /**
     * @param Job $job
     * @return string
     */
    public function dump(Job $job)
    {
        $lastExecution = $job->getLastExecution();

        $config = [
          'name' => $job->getName(),
          'description' => $job->getDescription(),
          'status' => $job->getStatus(),
          'last_execution' => $lastExecution ? $lastExecution->format('Y-m-d H:i:s') : null,
          'source' => [
            'type' => $job->getSourceType(),
            'settings' => $job->getSourceSettings(),
          ],
        ];

        $content = Yaml::dump($config);

        return $content;
    }

    /**
     * @param string $directory
     * @return Job[]
     */
    public function findFiles($directory)
    {
        $fs = new Filesystem();

        // Default folder.
        if (empty($directory)) {
            $home = getenv('HOME');
            $directory = $home.DIRECTORY_SEPARATOR.self::JOB_FOLDER;

            if (!$fs->exists($directory)) {
                $fs->mkdir($directory);
            }
        }

        if (!$fs->exists($directory)) {
            return [];
        }

        $finder = new Finder();
        $finder->files()
          ->in($directory)
          ->depth('== 0')
          ->name('*.yml');

        $jobs = [];
        foreach ($finder as $file) {
            $job = $this->parse($file->getContents());
            if (!$job->getName()) {
                // Use the filename as default job name.
                $job->setName($file->getBasename('.yml'));
            }
            $jobs[] = $job;
        }

        return $jobs;
    }
}
