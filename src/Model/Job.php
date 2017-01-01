<?php

/**
 * MIT License
 *
 * Copyright (C) 2017 - Sebastien Malot <sebastien@malot.fr>
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

/**
 * Class Job
 * @package Carbon14\Event
 */
class Job
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $description;

    /**
     * @var string
     */
    protected $status;

    /**
     * @var \DateTime
     */
    protected $lastExecution;

    /**
     * @var string
     */
    protected $sourceType;

    /**
     * @var array
     */
    protected $sourceSettings;

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return Job
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return Job
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     * @return Job
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getLastExecution()
    {
        return $this->lastExecution;
    }

    /**
     * @param \DateTime $lastExecution
     * @return Job
     */
    public function setLastExecution($lastExecution)
    {
        $this->lastExecution = $lastExecution;

        return $this;
    }

    /**
     * @return string
     */
    public function getSourceType()
    {
        return $this->sourceType;
    }

    /**
     * @param string $sourceType
     * @return Job
     */
    public function setSourceType($sourceType)
    {
        $this->sourceType = $sourceType;

        return $this;
    }

    /**
     * @return array
     */
    public function getSourceSettings()
    {
        return $this->sourceSettings;
    }

    /**
     * @param array $sourceSettings
     * @return Job
     */
    public function setSourceSettings($sourceSettings)
    {
        $this->sourceSettings = $sourceSettings;

        return $this;
    }
}
