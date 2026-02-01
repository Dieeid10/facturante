<?php

namespace App\Application\UseCase;

use App\Application\DTO\InvoiceCreatedResponse;
use App\Application\Port\AfipServiceInterface;
use App\Application\Port\EventQueueInterface;
use App\Application\Port\InvoiceRepositoryInterface;
use App\Application\Port\ProcessingLockInterface;
use App\Domain\Entity\Invoice;
use App\Domain\Event\InvoicePendingEvent;
use App\Domain\ValueObject\InvoiceDate;
use Psr\Log\LoggerInterface;

class ProcessInvoiceUseCase
{
    private AfipServiceInterface $afipService;
    private InvoiceRepositoryInterface $invoiceRepository;
    private EventQueueInterface $eventQueue;
    private ProcessingLockInterface $processingLock;
    private LoggerInterface $logger;

    public function __construct(
        AfipServiceInterface $afipService,
        InvoiceRepositoryInterface $invoiceRepository,
        EventQueueInterface $eventQueue,
        ProcessingLockInterface $processingLock,
        LoggerInterface $logger
    ) {
        $this->afipService = $afipService;
        $this->invoiceRepository = $invoiceRepository;
        $this->eventQueue = $eventQueue;
        $this->processingLock = $processingLock;
        $this->logger = $logger;
    }

    /**
     * Procesa una factura con el servicio AFIP y actualiza su estado
     *
     * @param Invoice $invoice
     * @return Invoice
     * @throws \Exception
     */
    public function execute(Invoice $invoice): Invoice
    {
        try {
            $this->logger->info('Iniciando procesamiento de factura con AFIP', [
                'invoice_id' => $invoice->getId(),
                'point_of_sale' => $invoice->getPointOfSale(),
                'voucher_type' => $invoice->getVoucherType(),
            ]);

            $afipResponse = $this->afipService->createInvoice($invoice);

            // Convertir la fecha de expiración del CAE al formato InvoiceDate
            $caeExpirationDate = InvoiceDate::fromIsoString($afipResponse->caeExpirationDate);

            $invoice->markAsCreated(
                $afipResponse->voucherNumber,
                $afipResponse->cae,
                $caeExpirationDate
            );

            $this->invoiceRepository->save($invoice);

            $this->logger->info('Factura creada exitosamente en AFIP', [
                'invoice_id' => $invoice->getId(),
                'voucher_number' => $afipResponse->voucherNumber,
                'cae' => $afipResponse->cae,
            ]);

            return $invoice;
        } catch (\Exception $e) {
            $this->logger->error('Error al procesar factura con AFIP', [
                'invoice_id' => $invoice->getId(),
                'error' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'trace' => $e->getTraceAsString(),
            ]);

            $invoice->markAsFailed($e->getMessage());
            $this->invoiceRepository->save($invoice);

            throw $e;
        }
    }

    /**
     * Procesa todas las facturas pendientes en la cola
     *
     * @return int Número de facturas procesadas
     */
    public function processPendingInvoices(): int
    {
        if (!$this->processingLock->acquire()) {
            // Ya se está procesando
            echo "⚠️ La cola ya está siendo procesada\n";
            return 0;
        }

        try {
            $processedCount = 0;

            while (!$this->eventQueue->isEmpty()) {
                echo "   ➤ Desencolando evento...\n";
                $event = $this->eventQueue->dequeue();

                if ($event instanceof InvoicePendingEvent) {
                    try {
                        $invoice = $this->invoiceRepository->findById($event->invoiceId);
                        if ($invoice === null) {
                            $this->logger->warning('Factura no encontrada', [
                                'invoice_id' => $event->invoiceId
                            ]);
                            continue;
                        }

                        echo "   ✓ Procesando factura ID: " . $event->invoiceId . "\n";
                        $this->logger->info('Procesando factura', [
                            'invoice_id' => $event->invoiceId
                        ]);

                        $this->execute($invoice);
                        $processedCount++;

                        $this->logger->info('Factura procesada exitosamente', [
                            'invoice_id' => $event->invoiceId
                        ]);
                    } catch (\Exception $e) {
                        $this->logger->error('Error procesando factura', [
                            'invoice_id' => $event->invoiceId,
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString()
                        ]);
                    }
                }
            }

            return $processedCount;
        } finally {
            $this->processingLock->release();
        }
    }
}
