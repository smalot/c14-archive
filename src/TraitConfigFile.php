<?php

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
