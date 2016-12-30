<?php

namespace Carbon14\Event;

use Carbon14\Model\File;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class TransferProgressEvent
 * @package Carbon14\Event
 */
class TransferProgressEvent extends Event
{
    /**
     * @var File
     */
    protected $file;

    /**
     * @var int
     */
    protected $progress;

    /**
     * TransferEvent constructor.
     * @param File $file
     * @param int $progress
     */
    public function __construct(File $file, $progress)
    {
        $this->file = $file;
        $this->progress = $progress;
    }

    /**
     * @return \Carbon14\Model\File
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @return int
     */
    public function getProgress()
    {
        return $this->progress;
    }
}
