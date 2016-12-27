<?php

namespace Carbon14;

use Symfony\Component\Yaml\Yaml;

/**
 * trait Config
 * @package Carbon14
 */
trait TraitConfig
{
    /**
     * @var string
     */
    protected $configFilename;

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
    public function loadConfig($configFilename)
    {
        $this->configFilename = $configFilename;

        if (file_exists($this->configFilename)) {
            $content = file_get_contents($this->configFilename);
            $this->settings = Yaml::parse($content);

            return true;
        }

        return false;
    }

    /**
     * @return true
     */
    public function saveConfig()
    {
        $content = Yaml::dump($this->settings, 10, 2, Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK);
        file_put_contents($this->configFilename, $content);

        return true;
    }
}
