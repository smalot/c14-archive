<?php

namespace Carbon14\Model;

/**
 * Class File
 * @package Carbon14\Model
 */
class File
{
    /**
     * @var string
     */
    protected $file;

    /**
     * @var int
     */
    protected $size;

    /**
     * @var string
     */
    protected $md5;

    /**
     * File constructor.
     * @param string $file
     */
    public function __construct($file)
    {
        $this->file = realpath($file);
        $this->size = filesize($this->file);
        $this->md5 = md5_file($this->file);
    }

    /**
     * @return string
     */
    public function getFilename()
    {
        return basename($this->file);
    }

    /**
     * @return string
     */
    public function getFilenamePath()
    {
        return $this->file;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return dirname($this->file);
    }

    /**
     * @return string
     */
    public function getExtension()
    {
        return pathinfo($this->file, PATHINFO_EXTENSION);
    }

    /**
     * @return int
     */
    public function getFilesize()
    {
        return $this->size;
    }

    /**
     * @return string
     */
    public function getMd5()
    {
        return $this->md5;
    }
}
