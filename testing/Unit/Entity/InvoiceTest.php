<?php

declare(strict_types=1);

namespace Tests\Unit\Entity;

use App\Domain\Entity\Invoice;
use App\Domain\Exception\InvalidInvoiceAmountException;
use App\Domain\ValueObject\InvoiceDate;
use App\Domain\ValueObject\MoneyAmount;
use PHPUnit\Framework\TestCase;

/**
 * Test Unitario: Invoice Entity
 * 
 * Este test demuestra:
 * - Testing de entidades de dominio
 * - Testing de invariantes de negocio
 * - Testing de excepciones de dominio
 * - Testing de cambios de estado
 * - Testing de validación de reglas de negocio
 */
class InvoiceTest extends TestCase
{
    /**
     * Helper: Crear una factura válida para tests
     */
    private function createValidInvoice(): Invoice
    {
        $currency = 'ARS';
        $netAmount = MoneyAmount::fromFloat(1000.0, $currency);
        $vatAmount = MoneyAmount::fromFloat(210.0, $currency);
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
                ['Id' => 5, 'BaseImp' => 1000.0, 'Importe' => 210.0]
            ],
            receiverVatConditionId: 1
        );
    }

    /**
     * Test: Crear factura válida
     * 
     * Verifica que se puede crear una factura con datos válidos.
     */
    public function testConstructor_WithValidData_CreatesInvoice(): void
    {
        // Arrange & Act
        $invoice = $this->createValidInvoice();
        
        // Assert
        $this->assertInstanceOf(Invoice::class, $invoice);
        $this->assertEquals(Invoice::STATUS_DRAFT, $invoice->getStatus());
        $this->assertNull($invoice->getId());
    }

    /**
     * Test: Invariante de montos - suma correcta
     * 
     * Verifica que el invariante de negocio se cumple:
     * ImpTotal = ImpTotConc + ImpNeto + ImpOpEx + ImpTrib + ImpIVA
     */
    public function testConstructor_WithValidAmounts_CreatesInvoice(): void
    {
        // Arrange & Act
        $invoice = $this->createValidInvoice();
        
        // Assert: La factura se creó sin excepción
        $this->assertInstanceOf(Invoice::class, $invoice);
    }

    /**
     * Test: Invariante de montos - suma incorrecta lanza excepción
     * 
     * Verifica que si los montos no suman correctamente,
     * se lanza InvalidInvoiceAmountException.
     */
    public function testConstructor_WithInvalidAmounts_ThrowsException(): void
    {
        // Arrange
        $currency = 'ARS';
        $netAmount = MoneyAmount::fromFloat(1000.0, $currency);
        $vatAmount = MoneyAmount::fromFloat(210.0, $currency);
        // Total incorrecto: debería ser 1210.0, pero ponemos 1200.0
        $totalAmount = MoneyAmount::fromFloat(1200.0, $currency);
        
        $this->expectException(InvalidInvoiceAmountException::class);
        $this->expectExceptionMessage('Importe total inválido');
        
        // Act
        new Invoice(
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
            vatItems: []
        );
    }

    /**
     * Test: Marcar factura como creada
     * 
     * Verifica que markAsCreated() actualiza el estado correctamente.
     */
    public function testMarkAsCreated_UpdatesStatusAndData(): void
    {
        // Arrange
        $invoice = $this->createValidInvoice();
        $voucherNumber = 25;
        $cae = '86030001451314';
        $caeExpirationDate = InvoiceDate::fromIsoString('2026-01-29');
        
        // Act
        $invoice->markAsCreated($voucherNumber, $cae, $caeExpirationDate);
        
        // Assert
        $this->assertEquals(Invoice::STATUS_CREATED, $invoice->getStatus());
        $this->assertEquals($voucherNumber, $invoice->getVoucherNumber());
        $this->assertEquals($cae, $invoice->getCae());
        $this->assertEquals($caeExpirationDate, $invoice->getCaeExpirationDate());
    }

    /**
     * Test: Marcar factura como fallida
     * 
     * Verifica que markAsFailed() actualiza el estado a error.
     */
    public function testMarkAsFailed_UpdatesStatusToError(): void
    {
        // Arrange
        $invoice = $this->createValidInvoice();
        $reason = 'Error de conexión con AFIP';
        
        // Act
        $invoice->markAsFailed($reason);
        
        // Assert
        $this->assertEquals(Invoice::STATUS_ERROR, $invoice->getStatus());
    }

    /**
     * Test: Asignar ID a factura
     */
    public function testSetId_SetsInvoiceId(): void
    {
        // Arrange
        $invoice = $this->createValidInvoice();
        
        // Act
        $invoice->setId(123);
        
        // Assert
        $this->assertEquals(123, $invoice->getId());
    }

    /**
     * Test: Obtener currency ID desde totalAmount
     */
    public function testGetCurrencyId_ReturnsCurrencyFromTotalAmount(): void
    {
        // Arrange
        $invoice = $this->createValidInvoice();
        
        // Act
        $currencyId = $invoice->getCurrencyId();
        
        // Assert
        $this->assertEquals('ARS', $currencyId);
    }
}
