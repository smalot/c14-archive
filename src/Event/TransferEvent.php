<?php

namespace Carbon14\Event;

use Carbon14\Model\File;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class TransferEvent
 * @package Carbon14\Event
 */
class TransferEvent extends Event
{
    /**
     * @var File
     */
    protected $file;

    /**
     * TransferEvent constructor.
     * @param File $file
     */
    public function __construct(File $file)
    {
        $this->file = $file;
    }

    /**
     * @return File
     */
    public function getFile()
    {
        return $this->file;
    }
}
