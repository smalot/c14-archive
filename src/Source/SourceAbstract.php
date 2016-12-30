<?php

namespace Carbon14\Source;

use Carbon14\Model\File;
use Carbon14\Model\FileCollection;
use Symfony\Component\Finder\SplFileInfo;

/**
 * Class SourceAbstract
 * @package Carbon14\Source
 */
abstract class SourceAbstract
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var array
     */
    protected $settings;

    /**
     * @var FileCollection
     */
    protected $fileCollection;

    /**
     * SourceAbstract constructor.
     *
     * @param string $name
     * @param array $settings
     */
    public function __construct($name, array $settings)
    {
        $this->name = $name;
        $this->settings = $settings;
        $this->fileCollection = new FileCollection();
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return array
     */
    public function getSettings()
    {
        return $this->settings;
    }

    /**
     * @return array
     */
    public function export()
    {
        return array(
          'type' => $this->getName(),
          'settings' => $this->getSettings(),
        );
    }

    /**
     * @param \IteratorAggregate $files
     * @return $this
     */
    public function setFiles(\IteratorAggregate $files)
    {
        $this->fileCollection->removeAll($this->fileCollection);
        $this->addFiles($files);

        return $this;
    }

    /**
     * @param \IteratorAggregate $files
     * @return $this
     */
    public function addFiles(\IteratorAggregate $files)
    {
        foreach ($files as $file) {
            $this->addFile($file);
        }

        return $this;
    }

    /**
     * @param SplFileInfo $file
     * @return $this
     */
    public function addFile(SplFileInfo $file)
    {
        if (!$file instanceof File) {
            $file = new File($file);
        }

        $this->getFileCollection()->attach($file);

        return $this;
    }

    /**
     * @return FileCollection
     */
    public function getFileCollection()
    {
        return $this->fileCollection;
    }
}
