<?php

declare(strict_types=1);

namespace App\Domain\Event;

/**
 * Evento de dominio que indica que una factura está pendiente de procesamiento
 *
 * Los eventos de dominio deben ser inmutables y contener solo datos primitivos
 * para facilitar la serialización y evitar acoplamiento con entidades
 */
final readonly class InvoicePendingEvent
{
    public function __construct(
        public int $invoiceId,
        public \DateTimeImmutable $occurredOn = new \DateTimeImmutable()
    ) {
    }
}
