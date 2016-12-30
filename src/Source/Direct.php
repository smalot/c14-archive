<?php

namespace Carbon14\Source;

/**
 * Class Direct
 * @package Carbon14\Source
 */
class Direct extends SourceAbstract
{
    /**
     * Direct constructor.
     *
     * @param array $settings
     */
    public function __construct(array $settings)
    {
        parent::__construct('direct', $settings);
    }


}
