<?php

namespace Carbon14\Protocol;

use Carbon14\Event\TransferEvent;
use Carbon14\Event\TransferProgressEvent;
use Carbon14\Events;
use Carbon14\Model\File;
use Carbon14\Model\FileCollection;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * Class Ftp
 * @package Carbon14\Protocol
 */
class Ftp
{
    /**
     * @var EventDispatcher
     */
    protected $eventDispatcher;

    /**
     * @var
     */
    protected $connection;

    /**
     * Ftp constructor.
     */
    public function __construct(EventDispatcher $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->connection = null;
    }

    /**
     * @inheritdoc
     */
    public function __destruct()
    {
        $this->close();
    }

    /**
     * @param array $credential
     * @return $this
     * @throws \Exception
     */
    public function connect(array $credential)
    {
        if (!$this->connection = ftp_connect(parse_url($credential['uri'], PHP_URL_HOST), parse_url($credential['uri'], PHP_URL_PORT))) {
            throw new \Exception('Fail to connect');
        }

        if (!$loggued = ftp_login($this->connection, $credential['login'], $credential['password'])) {
            throw new \Exception('Fail to login');
        }

        ftp_pasv($this->connection, true);

        return $this;
    }

    /**
     * @param FileCollection $fileCollection
     * @param bool $resume
     * @return $this
     * @throws \Exception
     */
    public function transfertFiles(FileCollection $fileCollection, $resume = true)
    {
        /** @var File $file */
        foreach ($fileCollection as $file) {
            $this->transferFile($file, $resume);
        }

        return $this;
    }

    /**
     * @param File $file
     * @param boolean $resume
     * @return $this
     * @throws \Exception
     */
    public function transferFile(File $file, $resume = true)
    {
        $handle = fopen($file->getFilenamePath(), 'r');

        // Resume transfer.
        if (($offset = ftp_size($this->connection, $file->getFilename())) > 0 && $resume) {
            if ($offset == $file->getFilesize()) {
                $event = new TransferEvent($file);
                $this->eventDispatcher->dispatch(Events::TRANSFER_SKIPPED, $event);

                return $this;
            }

            fseek($handle, $offset);
            $ret = ftp_nb_fput($this->connection, $file->getFilename(), $handle, FTP_BINARY, $offset);

            $event = new TransferProgressEvent($file, $offset);
            $this->eventDispatcher->dispatch(Events::TRANSFER_RESUME, $event);
        } else {
            $ret = ftp_nb_fput($this->connection, $file->getFilename(), $handle, FTP_BINARY);

            $event = new TransferEvent($file);
            $this->eventDispatcher->dispatch(Events::TRANSFER_STARTED, $event);
        }

        while ($ret == FTP_MOREDATA) {
            $event = new TransferProgressEvent($file, ftell($handle));
            $this->eventDispatcher->dispatch(Events::TRANSFER_PROGRESS, $event);

            $ret = ftp_nb_continue($this->connection);
        }

        if ($ret != FTP_FINISHED) {
            $event = new TransferEvent($file);
            $this->eventDispatcher->dispatch(Events::TRANSFER_ERROR, $event);

            throw new \Exception('Error on file transfer');
        }

        $event = new TransferEvent($file);
        $this->eventDispatcher->dispatch(Events::TRANSFER_FINISHED, $event);

        return $this;
    }

    /**
     * @return $this
     */
    public function close()
    {
        if (is_resource($this->connection)) {
            ftp_close($this->connection);
            $this->connection = null;
        }

        return $this;
    }
}
