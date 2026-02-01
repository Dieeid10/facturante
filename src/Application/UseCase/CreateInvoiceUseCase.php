<?php

namespace App\Application\UseCase;

use App\Application\DTO\CreateInvoiceRequest;
use App\Application\Port\AfipServiceInterface;
use App\Application\Port\EventQueueInterface;
use App\Application\Port\InvoiceRepositoryInterface;
use App\Domain\Entity\Invoice;
use App\Domain\Event\InvoicePendingEvent;
use App\Domain\ValueObject\InvoiceDate;
use App\Domain\ValueObject\MoneyAmount;

class CreateInvoiceUseCase
{
    private AfipServiceInterface $afipService;
    private InvoiceRepositoryInterface $invoiceRepository;
    private EventQueueInterface $eventQueue;

    public function __construct(
        AfipServiceInterface $afipService,
        InvoiceRepositoryInterface $invoiceRepository,
        EventQueueInterface $eventQueue
    ) {
        $this->afipService = $afipService;
        $this->invoiceRepository = $invoiceRepository;
        $this->eventQueue = $eventQueue;
    }

    /**
     * Crea una factura y la encola para procesamiento
     * 
     * @param CreateInvoiceRequest $command
     * @return Invoice
     */
    public function execute(CreateInvoiceRequest $command): Invoice
    {
        $currency = $command->currency;
        $invoice = new Invoice(
            pointOfSale: $command->pointOfSale,
            voucherType: $command->voucherType,
            concept: $command->concept,
            documentType: $command->documentType,
            documentNumber: $command->documentNumber,
            voucherDate: InvoiceDate::fromIsoString($command->voucherDate),

            totalAmount:   MoneyAmount::fromFloat($command->totalAmount, $currency),
            untaxedAmount: MoneyAmount::fromFloat($command->untaxedAmount, $currency),
            netAmount:     MoneyAmount::fromFloat($command->netAmount, $currency),
            exemptAmount:  MoneyAmount::fromFloat($command->exemptAmount, $currency),
            vatAmount:     MoneyAmount::fromFloat($command->vatAmount, $currency),
            taxAmount:     MoneyAmount::fromFloat($command->taxAmount, $currency),

            currencyRate: $command->currencyRate,
            vatItems:     $command->vatItems,

            serviceFromDate: $command->serviceFromDate
                ? InvoiceDate::fromIsoString($command->serviceFromDate)
                : null,
            serviceToDate: $command->serviceToDate
                ? InvoiceDate::fromIsoString($command->serviceToDate)
                : null,
            paymentDueDate: $command->paymentDueDate
                ? InvoiceDate::fromIsoString($command->paymentDueDate)
                : null,

            receiverVatConditionId: $command->receiverVatConditionId
        );

        // Guardar la factura
        $invoice = $this->invoiceRepository->save($invoice);

        // Encolar evento para procesamiento (solo con el ID)
        $event = new InvoicePendingEvent($invoice->getId());
        $this->eventQueue->enqueue($event);

        return $invoice;
    }
}