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
