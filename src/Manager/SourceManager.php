<?php

namespace Carbon14\Manager;

use Carbon14\Source\SourceAbstract;

/**
 * Class SourceManager
 * @package Carbon14\Manager
 */
class SourceManager
{
    /**
     * @var array
     */
    protected $sources;

    /**
     * SourceManager constructor.
     */
    public function __construct()
    {
        $this->sources = [];
    }

    /**
     * @param string $type
     * @param SourceAbstract $source
     */
    public function register($type, SourceAbstract $source)
    {
        $this->sources[$type] = $source;
    }

    /**
     * @param string $type
     * @return SourceAbstract
     * @throws \Exception
     */
    public function get($type)
    {
        if (isset($this->sources[$type])) {
            return $this->sources[$type];
        }

        throw new \Exception('Unknown source');
    }
}
