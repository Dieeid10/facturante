<?php

declare(strict_types=1);

namespace Tests\Helpers;

use App\Domain\Entity\Invoice;
use App\Domain\ValueObject\InvoiceDate;
use App\Domain\ValueObject\MoneyAmount;

/**
 * Factory para crear facturas en tests
 * 
 * Este helper demuestra el patrón Factory para tests,
 * que ayuda a crear objetos de prueba de forma consistente
 * y reutilizable.
 */
class InvoiceFactory
{
    /**
     * Crear una factura básica válida
     */
    public static function create(): Invoice
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

    /**
     * Crear una factura con ID específico
     */
    public static function createWithId(int $id): Invoice
    {
        $invoice = self::create();
        $invoice->setId($id);
        return $invoice;
    }

    /**
     * Crear una factura ya procesada (con CAE)
     */
    public static function createProcessed(int $id = 1): Invoice
    {
        $invoice = self::createWithId($id);
        $invoice->markAsCreated(
            25,
            '86030001451314',
            InvoiceDate::fromIsoString('2026-01-29')
        );
        return $invoice;
    }

    /**
     * Crear una factura fallida
     */
    public static function createFailed(int $id = 1): Invoice
    {
        $invoice = self::createWithId($id);
        $invoice->markAsFailed('Error de conexión');
        return $invoice;
    }
}
