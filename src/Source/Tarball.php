<?php

namespace Carbon14\Source;

/**
 * Class Tarball
 * @package Carbon14\Source
 */
class Tarball extends SourceAbstract
{
    /**
     * Tarball constructor.
     *
     * @param array $settings
     */
    public function __construct(array $settings)
    {
        parent::__construct('tarball', $settings);
    }
}
