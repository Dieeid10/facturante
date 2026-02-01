<?php

namespace App\Infrastructure\Queue;

use App\Application\Port\EventQueueInterface;

class InMemoryEventQueue implements EventQueueInterface
{
    private array $queue = [];

    /**
     * @param object $event
     * @return void
     */
    public function enqueue(object $event): void
    {
        $this->queue[] = $event;
    }

    /**
     * @return object|null
     */
    public function dequeue(): ?object
    {
        if (empty($this->queue)) {
            return null;
        }

        return array_shift($this->queue);
    }

    /**
     * @return bool
     */
    public function isEmpty(): bool
    {
        return empty($this->queue);
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return count($this->queue);
    }
}
