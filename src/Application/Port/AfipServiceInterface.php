<?php

namespace App\Application\Port;

use App\Application\DTO\InvoiceCreatedResponse;
use App\Domain\Entity\Invoice;

interface AfipServiceInterface
{
    /**
     * Crea una factura electrónica en AFIP
     *
     * @param Invoice $invoice
     * @return InvoiceCreatedResponse
     * @throws \Exception
     */
    public function createInvoice(Invoice $invoice): InvoiceCreatedResponse;

    /**
     * Obtiene información de una factura
     *
     * @param int $voucherNumber
     * @param int $pointOfSale
     * @param int $voucherType
     * @return array{
     *   CAE: string,
     *   CAEFchVto: string,
     *   CbteDesde: int,
     *   Resultado: string
     * }
     * @throws \Exception
     */
    public function getInformationInvoice(int $voucherNumber, int $pointOfSale, int $voucherType): array;
}
