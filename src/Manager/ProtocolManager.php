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

use Carbon14\Protocol\ProtocolAbstract;

/**
 * Class ProtocolManager
 * @package Carbon14\Manager
 */
class ProtocolManager
{
    /**
     * @var array
     */
    protected $protocols;

    /**
     * ProtocolManager constructor.
     */
    public function __construct()
    {
        $this->protocols = [];
    }

    /**
     * @param string $type
     * @param ProtocolAbstract $protocol
     */
    public function register($type, ProtocolAbstract $protocol)
    {
        $this->protocols[$type] = $protocol;
    }

    /**
     * @param string $type
     * @return ProtocolAbstract
     * @throws \Exception
     */
    public function get($type)
    {
        if (isset($this->protocols[$type])) {
            return $this->protocols[$type];
        }

        throw new \Exception('Unknown protocol');
    }
}
