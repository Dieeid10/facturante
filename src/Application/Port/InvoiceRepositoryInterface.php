<?php

namespace App\Application\Port;

use App\Domain\Entity\Invoice;

interface InvoiceRepositoryInterface
{
    /**
     * Guarda una factura
     *
     * @param Invoice $Invoice
     * @return Invoice
     */
    public function save(Invoice $Invoice): Invoice;

    /**
     * Busca una factura por ID
     *
     * @param int $id
     * @return Invoice|null
     */
    public function findById(int $id): ?Invoice;

    /**
     * Obtiene todas las facturas pendientes
     *
     * @return Invoice[]
     */
    public function findPendingInvoices(): array;
}
