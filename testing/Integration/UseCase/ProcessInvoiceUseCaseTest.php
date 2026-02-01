<?php

declare(strict_types=1);

namespace Tests\Integration\UseCase;

use App\Application\DTO\InvoiceCreatedResponse;
use App\Application\Port\AfipServiceInterface;
use App\Application\Port\EventQueueInterface;
use App\Application\Port\InvoiceRepositoryInterface;
use App\Application\Port\ProcessingLockInterface;
use App\Application\UseCase\ProcessInvoiceUseCase;
use App\Domain\Entity\Invoice;
use App\Domain\Event\InvoicePendingEvent;
use App\Domain\ValueObject\InvoiceDate;
use App\Domain\ValueObject\MoneyAmount;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Test de Integración: ProcessInvoiceUseCase
 * 
 * Este test demuestra:
 * - Testing de casos de uso complejos
 * - Testing con múltiples mocks
 * - Testing de flujos de error
 * - Testing de logging
 */
class ProcessInvoiceUseCaseTest extends TestCase
{
    private ProcessInvoiceUseCase $useCase;
    private MockObject&AfipServiceInterface $afipServiceMock;
    private MockObject&InvoiceRepositoryInterface $repositoryMock;
    private MockObject&EventQueueInterface $eventQueueMock;
    private MockObject&ProcessingLockInterface $lockMock;
    private MockObject&LoggerInterface $loggerMock;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->afipServiceMock = $this->createMock(AfipServiceInterface::class);
        $this->repositoryMock = $this->createMock(InvoiceRepositoryInterface::class);
        $this->eventQueueMock = $this->createMock(EventQueueInterface::class);
        $this->lockMock = $this->createMock(ProcessingLockInterface::class);
        $this->loggerMock = $this->createMock(LoggerInterface::class);
        
        $this->useCase = new ProcessInvoiceUseCase(
            $this->afipServiceMock,
            $this->repositoryMock,
            $this->eventQueueMock,
            $this->lockMock,
            $this->loggerMock
        );
    }

    /**
     * Test: Procesar factura exitosamente
     */
    public function testExecute_WithValidInvoice_ProcessesSuccessfully(): void
    {
        // Arrange
        $invoice = $this->createInvoice();
        $invoice->setId(1);
        
        $response = new InvoiceCreatedResponse(
            voucherNumber: 25,
            cae: '86030001451314',
            caeExpirationDate: '2026-01-29'
        );
        
        $this->afipServiceMock
            ->expects($this->once())
            ->method('createInvoice')
            ->with($invoice)
            ->willReturn($response);
        
        $this->repositoryMock
            ->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(Invoice::class));
        
        $this->loggerMock
            ->expects($this->atLeastOnce())
            ->method('info');
        
        // Act
        $result = $this->useCase->execute($invoice);
        
        // Assert
        $this->assertEquals(Invoice::STATUS_CREATED, $result->getStatus());
        $this->assertEquals(25, $result->getVoucherNumber());
        $this->assertEquals('86030001451314', $result->getCae());
    }

    /**
     * Test: Procesar factura con error de AFIP
     */
    public function testExecute_WithAfipError_MarksInvoiceAsFailed(): void
    {
        // Arrange
        $invoice = $this->createInvoice();
        $invoice->setId(1);
        
        $this->afipServiceMock
            ->expects($this->once())
            ->method('createInvoice')
            ->willThrowException(new \RuntimeException('Error de AFIP'));
        
        $this->repositoryMock
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(function (Invoice $inv) {
                return $inv->getStatus() === Invoice::STATUS_ERROR;
            }));
        
        $this->loggerMock
            ->expects($this->once())
            ->method('error');
        
        // Assert
        $this->expectException(\RuntimeException::class);
        
        // Act
        $this->useCase->execute($invoice);
    }

    /**
     * Test: Procesar cola de facturas pendientes
     */
    public function testProcessPendingInvoices_WithEvents_ProcessesAll(): void
    {
        // Arrange
        $invoice1 = $this->createInvoice();
        $invoice1->setId(1);
        $invoice2 = $this->createInvoice();
        $invoice2->setId(2);
        
        $event1 = new InvoicePendingEvent(1);
        $event2 = new InvoicePendingEvent(2);
        
        $this->lockMock
            ->expects($this->once())
            ->method('acquire')
            ->willReturn(true);
        
        $this->lockMock
            ->expects($this->once())
            ->method('release');
        
        $this->eventQueueMock
            ->expects($this->exactly(2))
            ->method('isEmpty')
            ->willReturnOnConsecutiveCalls(false, false, true);
        
        $this->eventQueueMock
            ->expects($this->exactly(2))
            ->method('dequeue')
            ->willReturnOnConsecutiveCalls($event1, $event2);
        
        $this->repositoryMock
            ->expects($this->exactly(2))
            ->method('findById')
            ->willReturnOnConsecutiveCalls($invoice1, $invoice2);
        
        $this->afipServiceMock
            ->expects($this->exactly(2))
            ->method('createInvoice')
            ->willReturn(new InvoiceCreatedResponse(25, 'CAE1', '2026-01-29'));
        
        // Act
        $processed = $this->useCase->processPendingInvoices();
        
        // Assert
        $this->assertEquals(2, $processed);
    }

    /**
     * Helper: Crear Invoice para tests
     */
    private function createInvoice(): Invoice
    {
        $currency = 'ARS';
        $netAmount = MoneyAmount::fromFloat(100.0, $currency);
        $vatAmount = MoneyAmount::fromFloat(21.0, $currency);
        $totalAmount = $netAmount->add($vatAmount);
        
        return new Invoice(
            pointOfSale: 1,
            voucherType: 1,
            concept: 1,
            documentType: 99,
            documentNumber: 0,
            voucherDate: InvoiceDate::fromIsoString('2026-01-15'),
            totalAmount: $totalAmount,
            untaxedAmount: MoneyAmount::zero($currency),
            netAmount: $netAmount,
            exemptAmount: MoneyAmount::zero($currency),
            vatAmount: $vatAmount,
            taxAmount: MoneyAmount::zero($currency),
            currencyRate: 1.0,
            vatItems: [
                ['Id' => 5, 'BaseImp' => 100.0, 'Importe' => 21.0]
            ],
            receiverVatConditionId: 1
        );
    }
}
