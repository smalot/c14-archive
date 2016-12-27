<?php

namespace Carbon14\Manager;

use Carbon14\Carbon14;

/**
 * Class JobManager
 * @package Carbon14\Manager
 */
class JobManager
{
    /**
     * @var Carbon14
     */
    protected $application;

    /**
     * @var self
     */
    protected static $instance;

    /**
     * ConfigManager constructor.
     */
    protected function __construct(Carbon14 $application)
    {
        $this->application = $application;
    }

    /**
     *
     */
    public function loadFromConfig()
    {

    }


}
