<?php

namespace Carbon14\Source;

use Carbon14\Model\File;
use Carbon14\Model\FileCollection;
use Symfony\Component\Finder\Finder;

/**
 * Class Direct
 * @package Carbon14\Source
 */
class Direct extends SourceAbstract
{
    /**
     * @inheritdoc
     */
    public function run(array $settings)
    {
        $fileCollection = new FileCollection();

        $finder = new Finder();
        $finder->files()->in('/data/isos')->name('deb*.iso');

        foreach ($finder as $file) {
            $fileCollection->attach(new File($file));
        }

        $this->setFileCollection($fileCollection);

        return $this;
    }
}
