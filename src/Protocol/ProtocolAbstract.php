<?php

namespace Carbon14\Protocol;

use Carbon14\Model\FileCollection;

/**
 * Class ProtocolAbstract
 * @package Carbon14\Protocol
 */
abstract class ProtocolAbstract
{
    /**
     * @param array $credential
     * @return $this
     */
    abstract public function open(array $credential);

    /**
     * @param FileCollection $fileCollection
     * @param bool $override
     * @param boolean $resume
     * @return $this
     */
    abstract public function transferFiles(FileCollection $fileCollection, $override = true, $resume = true);

    /**
     * @return $this
     */
    abstract public function close();
}
