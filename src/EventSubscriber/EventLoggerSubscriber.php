<?php

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
        $this->output->writeln('Transfer skipped');
    }

    /**
     * @param TransferEvent $event
     */
    public function onTransferStarted(TransferEvent $event)
    {
        $this->output->writeln('Transferring: '.$event->getFile()->getFilename());
        $this->progress = new ProgressBar($this->output, $event->getFile()->getFilesize());
        $this->progress->start();
    }

    /**
     * @param TransferProgressEvent $event
     */
    public function onTransferResume(TransferProgressEvent $event)
    {
        $this->output->writeln('Transferring: '.$event->getFile()->getFilename());
        $this->progress = new ProgressBar($this->output, $event->getFile()->getFilesize());
        $this->progress->start();
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
