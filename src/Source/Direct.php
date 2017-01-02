<?php

/**
 * MIT License
 *
 * Copyright (C) 2016 - Sebastien Malot <sebastien@malot.fr>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace Carbon14\Source;

use Carbon14\Model\File;
use Carbon14\Model\FileCollection;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

/**
 * Class Direct
 * @package Carbon14\Source
 */
class Direct implements SourceInterface
{
    /**
     * @inheritdoc
     */
    public function run(array $settings)
    {
        // Select files.
        $files = [];
        foreach ($this->buildFinder($settings['finder']) as $file) {
            $files[] = new File($file);
        }

        // Reverse order.
        if (!empty($settings['finder']['reverse'])) {
            $files = array_reverse($files);
        }

        // Limit file collection.
        if (!empty($settings['finder']['count'])) {
            $files = array_slice($files, 0, $settings['finder']['count']);
        }

        // Fill collection.
        $fileCollection = new FileCollection();
        foreach ($files as $file) {
            $fileCollection->push($file);
        }

        return $fileCollection;
    }

    /**
     * @param array $settings
     * @return Finder
     */
    protected function buildFinder(array $settings)
    {
        $finder = new Finder();
        $finder->files();

        $mapping = [
          'in' => 'in',
          'exclude' => 'exclude',
          'name' => 'name',
          'not_name' => 'notName',
          'depth' => 'depth',
          'size' => 'size',
          'date' => 'date',
        ];

        foreach ($mapping as $key => $method) {
            if (isset($settings[$key])) {
                call_user_func_array([$finder, $method], [$settings[$key]]);
            }
        }

        // By default, links are not followed.
        if (!empty($settings['follow_links'])) {
            $finder->followLinks();
        }

        if (!empty($settings['sort'])) {
            switch ($settings['sort']) {
                case 'accessed_time':
                    $finder->sortByAccessedTime();
                    break;
                case 'changed_time':
                    $finder->sortByChangedTime();
                    break;
                case 'modified_time':
                    $finder->sortByModifiedTime();
                    break;
                case 'name':
                    $finder->sortByName();
                    break;
                case 'type':
                    $finder->sortByType();
                    break;
                case 'size':
                    $finder->sort(function(SplFileInfo $a, SplFileInfo $b) {
                        return $a->getSize() > $b->getSize();
                    });
                    break;
            }
        }

        return $finder;
    }
}
