<?php

namespace Carbon14\Manager;

use Carbon14\Protocol\ProtocolAbstract;

/**
 * Class ProtocolManager
 * @package Carbon14\Manager
 */
class ProtocolManager
{
    /**
     * @var array
     */
    protected $protocols;

    /**
     * ProtocolManager constructor.
     */
    public function __construct()
    {
        $this->protocols = [];
    }

    /**
     * @param string $type
     * @param ProtocolAbstract $protocol
     */
    public function register($type, ProtocolAbstract $protocol)
    {
        $this->protocols[$type] = $protocol;
    }

    /**
     * @param string $type
     * @return ProtocolAbstract
     * @throws \Exception
     */
    public function get($type)
    {
        if (isset($this->protocols[$type])) {
            return $this->protocols[$type];
        }

        throw new \Exception('Unknown protocol');
    }
}
