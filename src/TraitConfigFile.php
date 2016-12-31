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

namespace Carbon14;

use Symfony\Component\Yaml\Yaml;

/**
 * trait TraitConfigFile
 * @package Carbon14
 */
trait TraitConfigFile
{
    /**
     * @var string
     */
    protected $configFile;

    /**
     * @var array
     */
    protected $settings;

    /**
     * @return array
     */
    public function getSettings()
    {
        return $this->settings;
    }

    /**
     * @param array $settings
     */
    public function setSettings($settings)
    {
        $this->settings = $settings;
    }

    /**
     * @return bool
     */
    public function loadConfigFile($configFile)
    {
        if (!$configFile) {
            $home = getenv('HOME');
            $this->configFile = $home.DIRECTORY_SEPARATOR.'.carbon14.yml';
        } elseif (file_exists(getcwd().DIRECTORY_SEPARATOR.$configFile)) {
            $this->configFile = getcwd().DIRECTORY_SEPARATOR.$configFile;
        } else {
            $this->configFile = $configFile;
        }

        if (file_exists($this->configFile)) {
            $content = file_get_contents($this->configFile);
            $this->settings = Yaml::parse($content);
        }

        return true;
    }

    /**
     * @return true
     */
    public function saveConfigFile()
    {
        $content = Yaml::dump($this->settings, 10, 2, Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK);
        file_put_contents($this->configFile, $content);

        return true;
    }
}
