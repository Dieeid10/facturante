<?php

declare(strict_types=1);

namespace App\Application\DTO;

/**
 * DTO de respuesta cuando se crea una factura en AFIP
 */
final readonly class InvoiceCreatedResponse
{
    public function __construct(
        public int $voucherNumber,
        public string $cae,
        public string $caeExpirationDate
    ) {
    }

    /**
     * Crea un InvoiceCreatedResponse desde un array de respuesta de AFIP
     *
     * @param array{
     *   CAEFchVto?: string,
     *   CbteDesde?: string|int,
     *   voucher_number?: string|int,
     *   CAE?: string
     * } $response
     */
    public static function fromAfipArray(array $response): self
    {
        $caeFchVto = isset($response['CAEFchVto'])
            ? (string) $response['CAEFchVto']
            : '';

        // Si viene en formato Ymd (20260115), convertir a Y-m-d (2026-01-15)
        if (!empty($caeFchVto) && strlen($caeFchVto) === 8 && ctype_digit($caeFchVto)) {
            $caeFchVto = substr($caeFchVto, 0, 4) . '-' . substr($caeFchVto, 4, 2) . '-' . substr($caeFchVto, 6, 2);
        }

        return new self(
            voucherNumber: (int) ($response['CbteDesde'] ?? $response['voucher_number'] ?? 0),
            cae: (string) ($response['CAE'] ?? ''),
            caeExpirationDate: $caeFchVto ?: date('Y-m-d')
        );
    }
}
