<?php

declare(strict_types=1);

namespace Tests\Integration\UseCase;

use App\Application\DTO\CreateInvoiceRequest;
use App\Application\Port\AfipServiceInterface;
use App\Application\Port\EventQueueInterface;
use App\Application\Port\InvoiceRepositoryInterface;
use App\Application\UseCase\CreateInvoiceUseCase;
use App\Domain\Entity\Invoice;
use App\Domain\Event\InvoicePendingEvent;
use App\Domain\ValueObject\InvoiceDate;
use App\Domain\ValueObject\MoneyAmount;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test de Integración: CreateInvoiceUseCase
 * 
 * Este test demuestra:
 * - Testing de casos de uso (Use Cases)
 * - Uso de Mocks para dependencias
 * - Verificación de interacciones (métodos llamados)
 * - Testing de flujos completos
 * - Testing con objetos reales y mocks mezclados
 */
class CreateInvoiceUseCaseTest extends TestCase
{
    private CreateInvoiceUseCase $useCase;
    private MockObject&AfipServiceInterface $afipServiceMock;
    private MockObject&InvoiceRepositoryInterface $repositoryMock;
    private MockObject&EventQueueInterface $eventQueueMock;

    /**
     * Setup: Se ejecuta antes de cada test
     * 
     * Aquí creamos los mocks y el caso de uso.
     */
    protected function setUp(): void
    {
        parent::setUp();
        
        // Crear mocks de las dependencias
        $this->afipServiceMock = $this->createMock(AfipServiceInterface::class);
        $this->repositoryMock = $this->createMock(InvoiceRepositoryInterface::class);
        $this->eventQueueMock = $this->createMock(EventQueueInterface::class);
        
        // Crear el caso de uso con los mocks
        $this->useCase = new CreateInvoiceUseCase(
            $this->afipServiceMock,
            $this->repositoryMock,
            $this->eventQueueMock
        );
    }

    /**
     * Test: Crear factura exitosamente
     * 
     * Verifica el flujo completo:
     * 1. Se crea la factura desde el DTO
     * 2. Se guarda en el repositorio
     * 3. Se encola el evento
     */
    public function testExecute_WithValidRequest_CreatesAndEnqueuesInvoice(): void
    {
        // Arrange
        $request = $this->createValidRequest();
        $savedInvoice = $this->createInvoice();
        $savedInvoice->setId(1);
        
        // Configurar mock del repositorio
        // Stub: Devuelve la factura guardada
        $this->repositoryMock
            ->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(Invoice::class))
            ->willReturn($savedInvoice);
        
        // Configurar mock de la cola
        // Verificar que se encola el evento
        $this->eventQueueMock
            ->expects($this->once())
            ->method('enqueue')
            ->with($this->isInstanceOf(InvoicePendingEvent::class));
        
        // Act
        $result = $this->useCase->execute($request);
        
        // Assert
        $this->assertInstanceOf(Invoice::class, $result);
        $this->assertEquals(1, $result->getId());
        $this->assertEquals(Invoice::STATUS_DRAFT, $result->getStatus());
    }

    /**
     * Test: Verificar que el evento contiene el ID correcto
     * 
     * Este test verifica que el evento se crea con el ID
     * de la factura guardada.
     */
    public function testExecute_EnqueuesEvent_WithCorrectInvoiceId(): void
    {
        // Arrange
        $request = $this->createValidRequest();
        $savedInvoice = $this->createInvoice();
        $savedInvoice->setId(42);
        
        $this->repositoryMock
            ->method('save')
            ->willReturn($savedInvoice);
        
        // Verificar que el evento tiene el ID correcto
        $this->eventQueueMock
            ->expects($this->once())
            ->method('enqueue')
            ->with($this->callback(function (InvoicePendingEvent $event) {
                return $event->invoiceId === 42;
            }));
        
        // Act
        $this->useCase->execute($request);
    }

    /**
     * Helper: Crear un CreateInvoiceRequest válido
     */
    private function createValidRequest(): CreateInvoiceRequest
    {
        return new CreateInvoiceRequest(
            pointOfSale: 1,
            voucherType: 1,
            concept: 1,
            documentType: 99,
            documentNumber: 0,
            voucherDate: '2026-01-15',
            totalAmount: 121.0,
            untaxedAmount: 0.0,
            netAmount: 100.0,
            exemptAmount: 0.0,
            vatAmount: 21.0,
            taxAmount: 0.0,
            currency: 'ARS',
            currencyRate: 1.0,
            vatItems: [
                ['Id' => 5, 'BaseImp' => 100.0, 'Importe' => 21.0]
            ],
            receiverVatConditionId: 1
        );
    }

    /**
     * Helper: Crear una Invoice válida
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
