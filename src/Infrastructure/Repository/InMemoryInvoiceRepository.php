<?php

namespace App\Infrastructure\Repository;

use App\Application\Port\InvoiceRepositoryInterface;
use App\Domain\Entity\Invoice;

class InMemoryInvoiceRepository implements InvoiceRepositoryInterface
{
    private array $invoices = [];
    private int $nextId = 1;

    /**
     * @param Invoice $invoice
     * @return Invoice
     */
    public function save(Invoice $invoice): Invoice
    {
        if ($invoice->getId() === null) {
            $invoice->setId($this->nextId++);
        }
        $this->invoices[$invoice->getId()] = $invoice;
        return $invoice;
    }

    /**
     * @param int $id
     * @return Invoice|null
     */
    public function findById(int $id): ?Invoice
    {
        return $this->invoices[$id] ?? null;
    }

    /**
    * @return list<Invoice>
    */
    public function findPendingInvoices(): array
    {
        return array_values(
            array_filter(
                $this->invoices,
                static fn (Invoice $invoice): bool =>
                    $invoice->getStatus() === 'pending'
            )
        );
    }
}
