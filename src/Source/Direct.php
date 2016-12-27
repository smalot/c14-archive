<?php

namespace Carbon14\Source;

/**
 * Class Direct
 * @package Carbon14\Source
 */
class Direct extends SourceAbstract
{
    /**
     * @var array
     */
    protected $files;

    /**
     * Direct constructor.
     *
     * @param array $settings
     */
    public function __construct(array $settings)
    {
        parent::__construct('direct', $settings);

        $this->files = array();
    }

    /**
     * @param mixed $files
     */
    public function setFiles($files)
    {
        $this->files = $files;
    }

    /**
     * @return mixed
     */
    public function getFiles()
    {
        return $this->files;
    }
}
