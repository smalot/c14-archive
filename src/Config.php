<?php

namespace Carbon14;

/**
 * Class Config
 * @package Carbon14
 */
class Config
{
    /**
     * @var array
     */
    protected $settings;

    /**
     * Config constructor.
     * @param array $settings
     */
    public function __construct($settings = array())
    {
        $settings += array(
          'token' => null,
          'default' => array(),
          'jobs' => array(),
        );

        $this->settings = $settings;

        $this->init();
    }

    /**
     *
     */
    protected function init()
    {

    }

    /**
     * return array
     */
    public function export()
    {
        return array();
    }
}
