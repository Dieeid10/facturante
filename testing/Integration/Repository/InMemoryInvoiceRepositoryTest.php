<?php

declare(strict_types=1);

namespace Tests\Integration\Repository;

use App\Domain\Entity\Invoice;
use App\Domain\ValueObject\InvoiceDate;
use App\Domain\ValueObject\MoneyAmount;
use App\Infrastructure\Repository\InMemoryInvoiceRepository;
use PHPUnit\Framework\TestCase;

/**
 * Test de Integración: InMemoryInvoiceRepository
 * 
 * Este test demuestra:
 * - Testing de repositorios
 * - Testing de persistencia en memoria
 * - Testing de búsquedas
 * - Testing de estado compartido entre operaciones
 */
class InMemoryInvoiceRepositoryTest extends TestCase
{
    private InMemoryInvoiceRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new InMemoryInvoiceRepository();
    }

    /**
     * Test: Guardar y recuperar factura
     * 
     * Verifica que se puede guardar una factura
     * y recuperarla por ID.
     */
    public function testSaveAndFindById_WithValidInvoice_ReturnsInvoice(): void
    {
        // Arrange
        $invoice = $this->createInvoice();
        $invoice->setId(1);
        
        // Act
        $saved = $this->repository->save($invoice);
        $found = $this->repository->findById(1);
        
        // Assert
        $this->assertNotNull($found);
        $this->assertEquals(1, $found->getId());
        $this->assertEquals($invoice->getPointOfSale(), $found->getPointOfSale());
    }

    /**
     * Test: Buscar factura inexistente retorna null
     */
    public function testFindById_WithNonExistentId_ReturnsNull(): void
    {
        // Act
        $result = $this->repository->findById(999);
        
        // Assert
        $this->assertNull($result);
    }

    /**
     * Test: Guardar múltiples facturas
     */
    public function testSave_MultipleInvoices_AllAreStored(): void
    {
        // Arrange
        $invoice1 = $this->createInvoice();
        $invoice1->setId(1);
        $invoice2 = $this->createInvoice();
        $invoice2->setId(2);
        
        // Act
        $this->repository->save($invoice1);
        $this->repository->save($invoice2);
        
        // Assert
        $this->assertNotNull($this->repository->findById(1));
        $this->assertNotNull($this->repository->findById(2));
    }

    /**
     * Test: Encontrar facturas pendientes
     */
    public function testFindPendingInvoices_ReturnsOnlyDraftInvoices(): void
    {
        // Arrange
        $draftInvoice = $this->createInvoice();
        $draftInvoice->setId(1);
        
        $createdInvoice = $this->createInvoice();
        $createdInvoice->setId(2);
        $createdInvoice->markAsCreated(25, 'CAE123', InvoiceDate::fromIsoString('2026-01-29'));
        
        $this->repository->save($draftInvoice);
        $this->repository->save($createdInvoice);
        
        // Act
        $pending = $this->repository->findPendingInvoices();
        
        // Assert
        $this->assertCount(1, $pending);
        $this->assertEquals(1, $pending[0]->getId());
        $this->assertEquals(Invoice::STATUS_DRAFT, $pending[0]->getStatus());
    }

    /**
     * Helper: Crear factura para tests
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
