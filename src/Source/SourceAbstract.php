<?php

namespace Carbon14\Source;

/**
 * Class SourceAbstract
 * @package Carbon14\Source
 */
abstract class SourceAbstract
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var array
     */
    protected $settings;

    /**
     * SourceAbstract constructor.
     *
     * @param string $name
     * @param array $settings
     */
    public function __construct($name, array $settings)
    {
        $this->name = $name;
        $this->settings = $settings;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return array
     */
    public function getSettings()
    {
        return $this->settings;
    }

    /**
     * @return array
     */
    public function export()
    {
        return array(
          'type' => $this->getName(),
          'settings' => $this->getSettings(),
        );
    }
}
