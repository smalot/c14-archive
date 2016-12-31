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

use Carbon14\Config;

/**
 * Class ConfigManager
 * @package Carbon14\Manager
 */
class ConfigManager
{
    /**
     * @var self
     */
    protected static $instance;

    /**
     * ConfigManager constructor.
     */
    protected function __construct()
    {
        // Nothing to do.
    }

    /**
     * @param string $file
     *
     * @return Config
     */
    public function loadFromFile($file)
    {
        $content = file_get_contents($file);

        return $this->loadFromContent($content);
    }

    /**
     * @param string $content
     *
     * @return Config
     */
    public function loadFromContent($content)
    {
        $json = json_decode($content, true);
        $config = new Config($json);

        return $config;
    }

    /**
     * @param Config $config
     * @param string $file
     *
     * @return bool
     */
    public function save(Config $config, $file)
    {
        $content = $config->export();
        $json = json_encode($content);

        if (file_put_contents($file, $json)) {
            return true;
        }

        return false;
    }

    /**
     * @return self
     */
    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }
}
