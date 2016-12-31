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

namespace Carbon14\EventSubscriber;

use Carbon14\Event\TransferEvent;
use Carbon14\Event\TransferProgressEvent;
use Carbon14\Events;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class EventLoggerSubscriber
 * @package Carbon14\EventSubscriber
 */
class EventLoggerSubscriber implements EventSubscriberInterface
{
    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * @var ProgressBar
     */
    protected $progress;

    /**
     * EventLoggerSubscriber constructor.
     */
    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
        $this->progress = new ProgressBar($this->output);
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
          Events::TRANSFER_SKIPPED => 'onTransferSkipped',
          Events::TRANSFER_STARTED => 'onTransferStarted',
          Events::TRANSFER_RESUME => 'onTransferResume',
          Events::TRANSFER_PROGRESS => 'onTransferProgress',
          Events::TRANSFER_ERROR => 'onTransferError',
          Events::TRANSFER_FINISHED => 'onTransferFinished',
        ];
    }

    /**
     * @param TransferEvent $event
     */
    public function onTransferSkipped(TransferEvent $event)
    {
        $this->output->writeln('<comment>Transfer skipped: </comment>'.$event->getFile()->getFilename());
    }

    /**
     * @param TransferEvent $event
     */
    public function onTransferStarted(TransferEvent $event)
    {
        $this->output->writeln('<info>Transferring:</info> '.$event->getFile()->getFilename());
        $this->progress->start($event->getFile()->getSize());
        $this->progress->setRedrawFrequency($this->progress->getMaxSteps() / 1000);
    }

    /**
     * @param TransferProgressEvent $event
     */
    public function onTransferResume(TransferProgressEvent $event)
    {
        $this->output->writeln('<info>Transferring:</info> '.$event->getFile()->getFilename());
        $this->progress->start($event->getFile()->getSize());
        $this->progress->setRedrawFrequency($this->progress->getMaxSteps() / 1000);
    }

    /**
     * @param TransferProgressEvent $event
     */
    public function onTransferProgress(TransferProgressEvent $event)
    {
        $this->progress->setProgress($event->getProgress());
    }

    /**
     * @param TransferEvent $event
     */
    public function onTransferError(TransferEvent $event)
    {
        $this->progress->finish();

        $this->output->writeln('');
        $this->output->writeln('<error>Transfer in error</error>');
    }

    /**
     * @param TransferEvent $event
     */
    public function onTransferFinished(TransferEvent $event)
    {
        $this->progress->finish();

        $this->output->writeln('');
        $this->output->writeln('<info>Transfer finished</info>');
    }
}
