<?php

namespace App\Infrastructure\Lock;

use App\Application\Port\ProcessingLockInterface;

class FileProcessingLock implements ProcessingLockInterface
{
    private $handle;

    /**
     * @return bool
     */
    public function acquire(): bool
    {
        $this->handle = fopen(sys_get_temp_dir() . '/invoice_queue.lock', 'c');

        return flock($this->handle, LOCK_EX | LOCK_NB);
    }

    /**
     * @return void
     */
    public function release(): void
    {
        if ($this->handle) {
            flock($this->handle, LOCK_UN);
            fclose($this->handle);
        }
    }
}
