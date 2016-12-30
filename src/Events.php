<?php

namespace Carbon14;

/**
 * Class Events
 * @package Carbon14
 */
final class Events
{
    /**
     * Transfer skipped because file already present.
     */
    const TRANSFER_SKIPPED = 'carbon14.transfer.skipped';

    /**
     * Transfer just started.
     */
    const TRANSFER_STARTED = 'carbon14.transfer.started';

    /**
     * Transfer just resumed.
     */
    const TRANSFER_RESUME = 'carbon14.transfer.resume';

    /**
     * Transfer in progress.
     */
    const TRANSFER_PROGRESS = 'carbon14.transfer.progress';

    /**
     * Transfer stopped with error.
     */
    const TRANSFER_ERROR = 'carbon14.transfer.error';

    /**
     * Transfer stopped successfully.
     */
    const TRANSFER_FINISHED = 'carbon14.transfer.finished';
}
