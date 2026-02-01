<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

use DateTimeImmutable;
use InvalidArgumentException;

final class InvoiceDate
{
    private DateTimeImmutable $date;

    private function __construct(DateTimeImmutable $date)
    {
        $this->date = $date;
    }

    /**
     * Crea la fecha desde una fecha ISO / input de usuario
     * Ej: 2026-01-15
     */
    public static function fromIsoString(string $date): self
    {
        try {
            return new self(new DateTimeImmutable($date));
        } catch (\Exception $e) {
            throw new InvalidArgumentException('Fecha inválida: ' . $date);
        }
    }

    /**
     * Convierte la fecha al formato que espera AFIP (Ymd)
     * Ej: 20260115
     */
    public function toAfipFormat(): string
    {
        return $this->date->format('Ymd');
    }

    /**
     * Convierte la fecha a formato ISO string
     * Ej: 2026-01-15
     */
    public function toIsoString(): string
    {
        return $this->date->format('Y-m-d');
    }

    /**
     * Obtiene el DateTimeImmutable interno
     */
    public function toDateTimeImmutable(): DateTimeImmutable
    {
        return $this->date;
    }
}
