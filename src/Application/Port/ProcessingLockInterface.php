<?php

namespace App\Application\Port;

interface ProcessingLockInterface
{
    public function acquire(): bool;
    public function release(): void;
}
