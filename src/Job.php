<?php

namespace Carbon14;

use Carbon14\Source\SourceInterface;

/**
 * Class Job
 * @package Carbon14
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
    protected $frequency;

    /**
     * @var \DateTime
     */
    protected $lastExecution;

    /**
     * @var SourceInterface
     */
    protected $source;

    /**
     * Job constructor.
     *
     * @param string $name
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $frequency
     */
    public function setFrequency($frequency)
    {
        $this->frequency = $frequency;
    }

    /**
     * @return string
     */
    public function getFrequency()
    {
        return $this->frequency;
    }

    /**
     * @param \DateTime $lastExecution
     */
    public function setLastExecution($lastExecution)
    {
        $this->lastExecution = $lastExecution;
    }

    /**
     * @return \DateTime
     */
    public function getLastExecution()
    {
        return $this->lastExecution;
    }

    /**
     * @param SourceInterface $source
     */
    public function setSource($source)
    {
        $this->source = $source;
    }

    /**
     * @return SourceInterface
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @return array
     */
    public function export()
    {
        return array(
          'name' => $this->name,
          'frequency' => $this->frequency,
          'last_execution' => $this->lastExecution,
          'source' => $this->source->export(),
        );
    }
}
