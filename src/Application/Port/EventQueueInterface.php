<?php

namespace App\Application\Port;

interface EventQueueInterface
{
    /**
     * Encola un evento para procesamiento posterior
     *
     * @param object $event
     * @return void
     */
    public function enqueue(object $event): void;

    /**
     * Desencola el siguiente evento
     *
     * @return object|null
     */
    public function dequeue(): ?object;

    /**
     * Verifica si hay eventos pendientes
     *
     * @return bool
     */
    public function isEmpty(): bool;

    /**
     * Obtiene la cantidad de eventos pendientes
     *
     * @return int
     */
    public function count(): int;
}
