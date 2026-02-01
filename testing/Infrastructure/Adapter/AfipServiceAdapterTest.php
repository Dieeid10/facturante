<?php

declare(strict_types=1);

namespace Tests\Infrastructure\Adapter;

use App\Application\DTO\InvoiceCreatedResponse;
use App\Domain\Entity\Invoice;
use App\Domain\ValueObject\InvoiceDate;
use App\Domain\ValueObject\MoneyAmount;
use App\Infrastructure\Adapter\AfipServiceAdapter;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * Test de Infraestructura: AfipServiceAdapter
 * 
 * Este test demuestra:
 * - Testing de adaptadores con dependencias externas
 * - Uso de mocks para SDK externo
 * - Testing de conversión de formatos
 * - Testing de manejo de errores
 */
class AfipServiceAdapterTest extends TestCase
{
    /**
     * Test: Convertir Invoice a array de AFIP
     * 
     * Verifica que toAfipArray() convierte correctamente
     * la entidad Invoice al formato que espera AFIP.
     */
    public function testToAfipArray_ConvertsInvoice_ToAfipFormat(): void
    {
        // Arrange
        $invoice = $this->createInvoice();
        $adapter = new AfipServiceAdapter($this->createMockAfip());
        
        // Act
        $afipArray = $adapter->toAfipArray($invoice);
        
        // Assert
        $this->assertIsArray($afipArray);
        $this->assertEquals(1, $afipArray['PtoVta']);
        $this->assertEquals(1, $afipArray['CbteTipo']);
        $this->assertEquals('PES', $afipArray['MonId']); // ARS convertido a PES
        $this->assertEquals(100.0, $afipArray['ImpNeto']);
        $this->assertEquals(21.0, $afipArray['ImpIVA']);
        $this->assertArrayHasKey('Iva', $afipArray);
    }

    /**
     * Test: Crear factura exitosamente
     * 
     * Verifica que createInvoice() llama al SDK de AFIP
     * y convierte la respuesta a InvoiceCreatedResponse.
     */
    public function testCreateInvoice_WithValidInvoice_ReturnsResponse(): void
    {
        // Arrange
        $invoice = $this->createInvoice();
        $afipMock = $this->createMockAfip();
        
        // Configurar respuesta del SDK
        $afipMock->ElectronicBilling
            ->expects($this->once())
            ->method('CreateNextVoucher')
            ->willReturn([
                'CbteDesde' => 25,
                'CAE' => '86030001451314',
                'CAEFchVto' => '20260129'
            ]);
        
        $adapter = new AfipServiceAdapter($afipMock);
        
        // Act
        $response = $adapter->createInvoice($invoice);
        
        // Assert
        $this->assertInstanceOf(InvoiceCreatedResponse::class, $response);
        $this->assertEquals(25, $response->voucherNumber);
        $this->assertEquals('86030001451314', $response->cae);
    }

    /**
     * Test: Manejo de errores del SDK
     * 
     * Verifica que si el SDK lanza una excepción,
     * se convierte en RuntimeException.
     */
    public function testCreateInvoice_WithSdkError_ThrowsRuntimeException(): void
    {
        // Arrange
        $invoice = $this->createInvoice();
        $afipMock = $this->createMockAfip();
        
        $afipMock->ElectronicBilling
            ->expects($this->once())
            ->method('CreateNextVoucher')
            ->willThrowException(new \Exception('Error de AFIP'));
        
        $adapter = new AfipServiceAdapter($afipMock);
        
        // Assert
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Error comunicándose con AFIP');
        
        // Act
        $adapter->createInvoice($invoice);
    }

    /**
     * Test: Conversión de moneda ARS a PES
     */
    public function testToAfipArray_ConvertsCurrency_ArsToPes(): void
    {
        // Arrange
        $invoice = $this->createInvoice();
        $adapter = new AfipServiceAdapter($this->createMockAfip());
        
        // Act
        $afipArray = $adapter->toAfipArray($invoice);
        
        // Assert
        $this->assertEquals('PES', $afipArray['MonId']);
    }

    /**
     * Helper: Crear mock del SDK de AFIP
     */
    private function createMockAfip(): object
    {
        $electronicBilling = $this->createMock(\stdClass::class);
        
        $afip = new \stdClass();
        $afip->ElectronicBilling = $electronicBilling;
        
        return $afip;
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
