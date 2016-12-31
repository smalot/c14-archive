<?php

namespace Carbon14\Model;

use Symfony\Component\Finder\SplFileInfo;

/**
 * Class File
 * @package Carbon14\Model
 */
class File extends SplFileInfo
{
    /**
     * @var string
     */
    protected $md5;

    /**
     * File constructor.
     * @param mixed $file
     */
    public function __construct($file)
    {
        if ($file instanceof SplFileInfo) {
            parent::__construct($file->getRealPath(), $file->getRelativePath(), $file->getRelativePathname());
        } elseif (is_string($file)) {
            parent::__construct($file, '', basename($file));
        } else {
            throw new \InvalidArgumentException('Not supported');
        }

        $this->md5 = md5_file($this->getRealPath());
    }

    /**
     * @return string
     */
    public function getMd5()
    {
        return $this->md5;
    }
}
