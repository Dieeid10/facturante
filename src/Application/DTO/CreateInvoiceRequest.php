<?php

declare(strict_types=1);

namespace App\Application\DTO;

/**
 * DTO de entrada para crear una factura
 */
final readonly class CreateInvoiceRequest
{
    private const VALID_VOUCHER_TYPES = [1, 2, 3, 6, 7, 8, 9];
    private const VALID_CONCEPTS = [1, 2, 3];
    private const MIN_POINT_OF_SALE = 1;
    private const MAX_POINT_OF_SALE = 99999;
    
    /**
     * @param array<int, array{Id: int, BaseImp: float, Importe: float}> $vatItems
     */
    public function __construct(
        public int $pointOfSale,
        public int $voucherType,
        public int $concept,
        public int $documentType,
        public int $documentNumber,
        public string $voucherDate,
        public float $totalAmount,
        public float $untaxedAmount,
        public float $netAmount,
        public float $exemptAmount,
        public float $vatAmount,
        public float $taxAmount,
        public string $currency,
        public float $currencyRate,
        public array $vatItems,
        public ?string $serviceFromDate = null,
        public ?string $serviceToDate = null,
        public ?string $paymentDueDate = null,
        public int $receiverVatConditionId = 1
    ) {
    }
}
