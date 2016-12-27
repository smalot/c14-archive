<?php

namespace Carbon14\Source;

/**
 * Class Postgresql
 * @package Carbon14\Source
 */
class Postgresql extends SourceAbstract
{
    /**
     * Postgresql constructor.
     *
     * @param array $settings
     */
    public function __construct(array $settings)
    {
        parent::__construct('pgsql', $settings);
    }
}
