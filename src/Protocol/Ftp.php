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
class Ftp extends ProtocolAbstract
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
     * @inheritdoc
     */
    public function open(array $credential)
    {
        if (!$this->connection = ftp_connect(
          parse_url($credential['uri'], PHP_URL_HOST),
          parse_url($credential['uri'], PHP_URL_PORT)
        )
        ) {
            throw new \Exception('Fail to connect');
        }

        if (!$loggued = ftp_login($this->connection, $credential['login'], $credential['password'])) {
            throw new \Exception('Fail to login');
        }

        ftp_pasv($this->connection, true);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function transferFiles(FileCollection $fileCollection, $override = true, $resume = true)
    {
        /** @var File $file */
        foreach ($fileCollection as $file) {
            $this->transferFile($file, $override, $resume);
        }

        return $this;
    }

    /**
     * @param File $file
     * @param bool $override
     * @param boolean $resume
     * @return $this
     * @throws \Exception
     */
    public function transferFile(File $file, $override = true, $resume = true)
    {
        // Move to remote folder and create if missing.
        if (!ftp_chdir($this->connection, DIRECTORY_SEPARATOR.$file->getRelativePath())) {
            ftp_chdir($this->connection, DIRECTORY_SEPARATOR);
            $this->createDirectories($file->getRelativePath());
        }

        // If already on remote server.
        if (!$override && ($size = ftp_size($this->connection, $file->getFilename())) > 0) {
            $event = new TransferEvent($file);
            $this->eventDispatcher->dispatch(Events::TRANSFER_SKIPPED, $event);

            return $this;
        }

        // Temporary file name, renamed when successfull.
        $tmpFilename = $file->getFilename().'.tmp';
        $handle = fopen($file->getRealPath(), 'r');

        // Resume transfer.
        if ($resume && ($offset = ftp_size($this->connection, $tmpFilename)) > 0) {
            if ($offset >= $file->getSize()) {
                $offset = 0;
            }

            fseek($handle, $offset);
            $ret = ftp_nb_fput($this->connection, $tmpFilename, $handle, FTP_BINARY, $offset);

            $event = new TransferProgressEvent($file, $offset);
            $this->eventDispatcher->dispatch(Events::TRANSFER_RESUME, $event);
        } else {
            $ret = ftp_nb_fput($this->connection, $tmpFilename, $handle, FTP_BINARY);

            $event = new TransferEvent($file);
            $this->eventDispatcher->dispatch(Events::TRANSFER_STARTED, $event);
        }

        // Loop to transfer content.
        while ($ret == FTP_MOREDATA) {
            $event = new TransferProgressEvent($file, ftell($handle));
            $this->eventDispatcher->dispatch(Events::TRANSFER_PROGRESS, $event);

            $ret = ftp_nb_continue($this->connection);
        }

        // On ended transfer, check result.
        if ($ret != FTP_FINISHED) {
            $event = new TransferEvent($file);
            $this->eventDispatcher->dispatch(Events::TRANSFER_ERROR, $event);

            throw new \Exception('Error on file transfer');
        } else {
            ftp_rename($this->connection, $tmpFilename, $file->getFilename());
        }

        $event = new TransferEvent($file);
        $this->eventDispatcher->dispatch(Events::TRANSFER_FINISHED, $event);

        return $this;
    }

    /**
     * @param string $path
     * @return bool
     */
    protected function createDirectories($path)
    {
        // Nothing to do.
        if (!$path) {
            return false;
        }

        $parts = explode(DIRECTORY_SEPARATOR, $path);

        // Check each part of the tree.
        foreach ($parts as $part) {
            if (!@ftp_chdir($this->connection, $part)) {
                ftp_mkdir($this->connection, $part);
                ftp_chdir($this->connection, $part);
            }
        }

        return true;
    }

    /**
     * @inheritdoc
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
