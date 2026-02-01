<?php

declare(strict_types=1);

namespace Tests\Unit\DTO;

use App\Application\DTO\CreateInvoiceRequest;
use PHPUnit\Framework\TestCase;

/**
 * Test Unitario: CreateInvoiceRequest DTO
 * 
 * Este test demuestra:
 * - Testing de DTOs (Data Transfer Objects)
 * - Testing de objetos readonly
 * - Testing de validación de estructura
 * - Testing de inmutabilidad
 */
class CreateInvoiceRequestTest extends TestCase
{
    /**
     * Test: Crear CreateInvoiceRequest con datos válidos
     * 
     * Verifica que se puede crear un DTO con todos los campos requeridos.
     */
    public function testConstructor_WithValidData_CreatesRequest(): void
    {
        // Arrange & Act
        $request = new CreateInvoiceRequest(
            pointOfSale: 1,
            voucherType: 1,
            concept: 2,
            documentType: 80,
            documentNumber: 20123456789,
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
            serviceFromDate: '2026-01-01',
            serviceToDate: '2026-01-31',
            paymentDueDate: '2026-02-10',
            receiverVatConditionId: 1
        );
        
        // Assert
        $this->assertEquals(1, $request->pointOfSale);
        $this->assertEquals(1, $request->voucherType);
        $this->assertEquals('ARS', $request->currency);
        $this->assertCount(1, $request->vatItems);
    }

    /**
     * Test: CreateInvoiceRequest con campos opcionales null
     * 
     * Verifica que los campos opcionales pueden ser null.
     */
    public function testConstructor_WithOptionalNullFields_CreatesRequest(): void
    {
        // Arrange & Act
        $request = new CreateInvoiceRequest(
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
            vatItems: [],
            serviceFromDate: null,
            serviceToDate: null,
            paymentDueDate: null,
            receiverVatConditionId: 1
        );
        
        // Assert
        $this->assertNull($request->serviceFromDate);
        $this->assertNull($request->serviceToDate);
        $this->assertNull($request->paymentDueDate);
    }

    /**
     * Test: Verificar estructura de vatItems
     * 
     * Verifica que vatItems tiene la estructura correcta.
     */
    public function testVatItems_HasCorrectStructure(): void
    {
        // Arrange
        $vatItems = [
            ['Id' => 5, 'BaseImp' => 100.0, 'Importe' => 21.0],
            ['Id' => 4, 'BaseImp' => 50.0, 'Importe' => 10.5],
        ];
        
        // Act
        $request = new CreateInvoiceRequest(
            pointOfSale: 1,
            voucherType: 1,
            concept: 1,
            documentType: 99,
            documentNumber: 0,
            voucherDate: '2026-01-15',
            totalAmount: 181.5,
            untaxedAmount: 0.0,
            netAmount: 150.0,
            exemptAmount: 0.0,
            vatAmount: 31.5,
            taxAmount: 0.0,
            currency: 'ARS',
            currencyRate: 1.0,
            vatItems: $vatItems
        );
        
        // Assert
        $this->assertCount(2, $request->vatItems);
        $this->assertEquals(5, $request->vatItems[0]['Id']);
        $this->assertEquals(100.0, $request->vatItems[0]['BaseImp']);
    }
}
