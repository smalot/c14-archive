<?php

namespace Carbon14\Event;

use Carbon14\Model\File;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class TransferProgressEvent
 * @package Carbon14\Event
 */
class TransferProgressEvent extends TransferEvent
{
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
        parent::__construct($file);

        $this->progress = $progress;
    }

    /**
     * @return int
     */
    public function getProgress()
    {
        return $this->progress;
    }
}
