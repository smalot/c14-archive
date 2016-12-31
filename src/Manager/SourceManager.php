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

namespace Carbon14\Manager;

use Carbon14\Source\SourceAbstract;

/**
 * Class SourceManager
 * @package Carbon14\Manager
 */
class SourceManager
{
    /**
     * @var array
     */
    protected $sources;

    /**
     * SourceManager constructor.
     */
    public function __construct()
    {
        $this->sources = [];
    }

    /**
     * @param string $type
     * @param SourceAbstract $source
     */
    public function register($type, SourceAbstract $source)
    {
        $this->sources[$type] = $source;
    }

    /**
     * @param string $type
     * @return SourceAbstract
     * @throws \Exception
     */
    public function get($type)
    {
        if (isset($this->sources[$type])) {
            return $this->sources[$type];
        }

        throw new \Exception('Unknown source');
    }
}
