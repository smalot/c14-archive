<?php

namespace Carbon14\Source;

/**
 * Class Mysql
 * @package Carbon14\Source
 */
class Mysql extends SourceAbstract
{
    /**
     * Mysql constructor.
     *
     * @param array $settings
     */
    public function __construct(array $settings)
    {
        parent::__construct('mysql', $settings);
    }
}
