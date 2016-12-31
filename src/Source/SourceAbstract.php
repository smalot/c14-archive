<?php

namespace Carbon14\Source;

use Carbon14\Model\FileCollection;

/**
 * Class SourceAbstract
 * @package Carbon14\Source
 */
abstract class SourceAbstract
{
    /**
     * @var FileCollection
     */
    protected $fileCollection;

    /**
     * @return \Carbon14\Model\FileCollection
     */
    public function getFileCollection()
    {
        return $this->fileCollection;
    }

    /**
     * @param \Carbon14\Model\FileCollection $fileCollection
     */
    public function setFileCollection($fileCollection)
    {
        $this->fileCollection = $fileCollection;
    }

    /**
     * @param array $settings
     * @return $this
     */
    abstract public function run(array $settings);
}
