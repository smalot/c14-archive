<?php

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
